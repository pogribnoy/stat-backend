<?php
class ExpensetypeController extends ControllerEntity {
	public $entityName  = 'ExpenseType';
	public $tableName  = 'expense_type';
	
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
		$res = 0;
		// id, select, link
		$res |= parent::sanitizeSaveRqData($rq);
		
		// name
		$this->fields['name']['value'] = null;
		if(isset($rq->fields->name) && isset($rq->fields->name->value)) {
			$this->fields['name']['value'] = $this->filter->sanitize(urldecode($rq->fields->name->value), ["trim", "string"]);
			if($this->fields['name']['value'] == '') $this->fields['name']['value'] = null;
		}
		
		$res |= $this->check();
		
		return $res;
	}
	
	/* 
	* Удаляет ссылки на сущность ($this->entity, если не передано отдельная сущность) из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function deleteEntityLinks($entity) {
		if(!isset($entity)) $entity = $this->entity;
		// ссылки из расходов - блокирующая связь
		$expense = false;
		$expense = Expense::findFirst([
			"conditions" => "expense_type_id = ?1",
			"bind" => array(1 => $entity->id)
		]);
		if($expense) {
			if($this->acl->isAllowed($this->userData['role_id'], 'expenselist', 'index')) $msg = 'Тип расхода назначен одному или более расходу. Перейти к <a class="" href="/expenselist?filter_expense_type_id=' . $entity->id . '">списку расходов</a>';
			else $msg = 'Тип расхода назначен одному или более расходу';
			$this->error['messages'][] = [
				'title' => "Ошибка удаления",
				'msg' => $msg,
				'data' => json_encode($expense),
			];
			return false;
		}
		return true;
	}
}
