<?php
class StreettypeController extends ControllerEntity {
	public $entityName  = 'StreetType';
	public $tableName  = 'street_type';
	
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
				'required' => 2,
				'newEntityValue' => '-1',
			), 
			'name' => array(
				'id' => 'name',
				'name' => $this->t->_("text_entity_property_name"),
				'type' => 'text',
				'required' => 2,
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
	* Удаляет ссылки на сущность ($this->entity, если не передано отдельная сущность) из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function deleteEntityLinks($entity) {
		if(!isset($entity)) $entity = $this->entity;
		// ссылки из муниципалитета - блокирующая связь
		$orgs = false;
		$orgs = Expense::findFirst([
			"conditions" => "street_type_id = ?1",
			"bind" => array(1 => $entity->id)
		]);
		if($orgs) {
			$msg = 'Тип улицы назначен одному или более муниципалитету';
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
