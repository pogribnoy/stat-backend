<?php
class ResourceController extends ControllerEntity {
	public $entityName = 'Resource';
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
				'newEntityValue' => 'acl',
				'required' => 2,
			), 
			'controller' =>	array(
				'id' => 'controller',
				'name' => $this->t->_("text_resource_controller"),
				'type' => 'text',
				'newEntityValue' => null,
				'required' => 2,
			), 
			'action' => array(
				'id' => 'action',
				'name' => $this->t->_("text_resource_action"),
				'type' => 'text',
				'newEntityValue' => null,
				'required' => 2,
			), 
			// модули
			'module' => array(
				'id' => 'module',
				'name' => $this->t->_("text_resource_module"),
				'type' => 'select',
				'style' => 'name', //id,
				'values' => ['backend', 'frontend'], // порядок имеет значение!!! С клиента при сохранении получается индекс выбранного значения и по нему сохраняется нужное значение из массива
				'newEntityValue' => 'backend',
				'required' => 2,
			), 
			'description' => array(
				'id' => 'description',
				'name' => $this->t->_("text_entity_property_description"),
				'type' => 'text',
				'newEntityValue' => null,
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
	
	
	protected function check() {
		$res = 0;
		$res |= parent::check();
		
		// Phalcon не позволяет использовать в качестве ресурсов и действий симол "*", поэтому на него надо проверять
		$field = $this->fields['controller'];
		// controller
		if($field['value'] == '*' ) {
			$field['value'] = null;
			$this->checkResult[] = [
				'type' => "error",
				'msg' => 'Поле "' . $field['name'] . '" не может содержать значение "*"',
			];
			$res |= 2;
		}
		
		// action
		$field = $this->fields['action'];
		if($field['value'] == '*') {
			$field['value'] = null;
			$this->checkResult[] = [
				'type' => "error",
				'msg' => 'Поле "' . $field['name'] . '" не может содержать значение "*"',
			];
			$res |= 2;
		}
		
		return $res;
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