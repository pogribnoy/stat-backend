<?php
class OrganizationRequestTopicController extends ControllerEntity {
	public $entityName  = 'OrganizationRequestTopic';
	public $tableName  = 'organization_request_topic';
	
	public function initialize() {
		parent::initialize();
	}
	
	/* 
	* Заполняет (инициализирует) свойство fields
	* Переопределяемый метод.
	*/
	public function initFields() {
		$this->fields = [
			'id' => array(
				'id' => 'id',
				'name' => $this->t->_("text_entity_property_id"),
				'type' => 'label',
				'newEntityValue' => '-1',
			), 
			'name' => array(
				'id' => 'name',
				'name' => $this->t->_("text_entity_property_name"),
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
		//$this->logger->log('val: ' . json_encode($this->fields)); //DEBUG
		$this->entity->name = $this->fields['name']['value'];
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	protected function fillFieldsFromRow($row) {
		$this->fields["id"]["value"] = $row->id;
		$this->fields["name"]["value"] = $row->name;
	}
	
	/* 
	* Очищает параметры запроса
	* Расширяемый метод.
	*/
	protected function sanitizeSaveRqData($rq) {
		// id, select, link
		if(!parent::sanitizeSaveRqData($rq)) return false;
		// name
		if(isset($rq->fields->name) && isset($rq->fields->name->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->name->value), ["trim", "string"]);
			if($val != '') $this->fields['name']['value'] = $val;
			else {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => 'Поле "'. $this->fields['name']['name'] .'" обязательно для указания'
				];
				return false;
			}
		}
		else return false;
		
		return true;
	}
	
	/* 
	* Удаляет ссылки на сущность ($this->entity, если не передано отдельная сущность) из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function deleteEntityLinks($entity) {
		if(!isset($entity)) $entity = $this->entity;
		// ссылки из расходов - блокирующая связь
		$expenses = false;
		$expenses = Expense::find([
			"conditions" => "expense_type_id = ?1",
			"bind" => array(1 => $entity->id)
		]);
		if($expenses && count($expenses)>0) {
			if($this->acl->isAllowed($this->userData['role_id'], 'OrganizationRequestTopicList', 'index')) $msg = 'Тема обращения назначена одному или более обращениям. Перейти к <a class="" href="/OrganizationRequestList?filter_topic_id=' . $entity->id . '">списку запросов</a>';
			else $msg = 'Тема обращения назначена одному или более расходу';
			$this->error['messages'][] = [
				'title' => "Ошибка удаления",
				'msg' => $msg,
				'data' => json_encode($expenses),
			];
			return false;
		}
		return true;
	}
}