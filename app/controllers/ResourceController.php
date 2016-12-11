<?php
class ResourceController extends ControllerEntity {
	public $entityName = 'resource';
	public $tableName  = 'resource';
	
	public function initialize() {
		parent::initialize();
	}
	
	/* 
	* Заполняет (инициализирует) свойство fields
	* Переопределяемый метод.
	*/
	protected function initFields() {
		$this->fields = [
			'id' => array(
				'id' => 'id',
				'name' => $this->t->_("text_entity_property_id"),
				'type' => 'label',
				'newEntityValue' => '-1',
			), 
			// группы
			'group' => array(
				'id' => 'group',
				'name' => $this->t->_("text_resource_group"),
				'type' => 'select',
				'style' => 'name', //id,
				'values' => ['base', 'acl'], // порядок имеет значение!!! С клиента при сохранении получается индекс выбранного значения и по нему сохраняется нужное значение из массива
				'newEntityValue' => null,
				'required' => 1,
			), 
			'controller' =>	array(
				'id' => 'controller',
				'name' => $this->t->_("text_resource_controller"),
				'type' => 'text',
				'newEntityValue' => null,
				'required' => 1,
			), 
			'action' => array(
				'id' => 'action',
				'name' => $this->t->_("text_resource_action"),
				'type' => 'text',
				'newEntityValue' => null,
				'required' => 1,
			), 
			// модули
			'module' => array(
				'id' => 'module',
				'name' => $this->t->_("text_resource_module"),
				'type' => 'select',
				'style' => 'name', //id,
				'values' => ['backend', 'frontend'], // порядок имеет значение!!! С клиента при сохранении получается индекс выбранного значения и по нему сохраняется нужное значение из массива
				'newEntityValue' => null,
				'required' => 1,
			), 
			'description' => array(
				'id' => 'description',
				'name' => $this->t->_("text_entity_property_description"),
				'type' => 'text',
				'newEntityValue' => '',
			)
		];
		// наполняем поля данными
		parent::initFields();
	}
	
	/* 
	* Наполняет модель сущности из запроса при сохранении
	* Переопределяемый метод.
	*/
	protected function fillModelFieldsFromSaveRq() {
		//$this->entity->id получен ранее при select из БД или будет присвоен при создании записи в БД
		$this->entity->group = $this->fields['group']['value'];
		$this->entity->controller = $this->fields['controller']['value'];
		$this->entity->action = $this->fields['action']['value'];
		$this->entity->module = $this->fields['module']['value'];
		$this->entity->description = $this->fields['description']['value'];
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	protected function fillFieldsFromRow($row) {
		$this->fields["id"]["value"] = $row->id;
		$this->fields["group"]["value"] = $row->group;
		$this->fields["controller"]["value"] = $row->controller;
		$this->fields["action"]["value"] = $row->action;
		$this->fields["module"]["value"] = $row->module;
		$this->fields["description"]["value"] = $row->description;
	}
	
	/* 
	* Очищает параметры запроса
	* Расширяемый метод.
	*/
	protected function sanitizeSaveRqData($rq) {
		// id, //select, link
		if(!parent::sanitizeSaveRqData($rq)) return false;
		// controller
		if(isset($rq->fields->controller) && isset($rq->fields->controller->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->controller->value), ["trim", "string"]);
			if($val != '') $this->fields['controller']['value'] = $val;
			else {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => 'Поле "' . $this->fields['controller']['name'] . '" обязательно для указания'
				];
				return false;
			}
		}
		else {
			$this->error['messages'][] = [
				'title' => "Ошибка",
				'msg' => 'Поле "' . $this->fields['action']['name'] . '" обязательно для указания'
			];
			return false;
		}
		
		// action
		if(isset($rq->fields->action) && isset($rq->fields->action->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->action->value), ["trim", "string"]);
			if($val != '') $this->fields['action']['value'] = $val;
			else {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => 'Поле "' . $this->fields['action']['name'] . '" обязательно для указания'
				];
				return false;
			}
		}
		else {
			$this->error['messages'][] = [
				'title' => "Ошибка",
				'msg' => 'Поле "' . $this->fields['action']['name'] . '" обязательно для указания'
			];
			return false;
		}
		
		// description
		if(isset($rq->fields->description) && isset($rq->fields->description->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->description->value), ["trim", "string"]);
			$this->fields['description']['value'] = $val;
		}
		else $this->fields['description']['value'] = $this->fields['description']['newEntityValue'];
		
		return true;
	}
	
	/* 
	* Удаляет ссылки на сущность ($this->entity, если не передано отдельная сущность) из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function deleteEntityLinks($entity) {
		if(!isset($entity)) $entity = $this->entity;
		$userRoleResources = UserRoleResource::find([
			"conditions" => "resource_id = ?1",
			"bind" => array(1 => $entity->id)
		]);
		foreach($userRoleResources as $userRoleResource) {
			if ($userRoleResource->delete() == false) {
				$this->db->rollback();
				$dbMessages = '';
				foreach ($n->getMessages() as $message) {
					$dbMessages .= "<li>" . $message . "</li>";
				}
				$this->error['messages'][] = [
					'title' => "Не удалось удалить связь с ролью пользователя id=" . $userRoleResource->user_role_id,
					'msg' => "<ul>" . $dbMessages . "</ul>"
				];
				return false;
			}
		}
		return true;
	}
}