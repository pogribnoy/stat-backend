<?php
class ExpenseController extends ControllerEntity {
	public $entityName  = 'Expense';
	
	public function initialize() {
		parent::initialize();
	}
	
	/*protected $access = [
		'edit' => [
			//'created_at' => self::readonlyAccess,
		]
	];*/
	
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
			'expense_type' => array(
				'id' => 'expense_type',
				'name' => $this->t->_("text_expense_expense_type"),
				'type' => 'select',
				'style' => 'id', //name
				//'values' => $expense_types
				'linkEntityName' => 'ExpenseType',
				'required' => 2,
				'newEntityValue' => null,
			),
			'expense_status' => array(
				'id' => 'expense_status',
				'name' => $this->t->_("text_entity_property_status"),
				'type' => 'select',
				'style' => 'id', //name
				//'values' => $expense_types
				'linkEntityName' => 'ExpenseStatus',
				'required' => 2,
				'newEntityValue' => null,
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->t->_("text_entity_property_name"),
				'type' => 'text',
				'required' => 2,
				'newEntityValue' => null,
			), 
			'amount' => array(
				'id' => 'amount',
				'name' => $this->t->_("text_entity_property_amount"),
				'type' => 'amount',
				'required' => 2,
				'min' => 1,
				'max' => 99999999,
				'newEntityValue' => null,
			), 
			'settlement' =>	array(
				'id' => 'settlement',
				'name' => $this->t->_("text_expense_settlement"),
				'type' => 'text',
				//'required' => 1,
				'newEntityValue' => null,
			),
			'street_type' =>	array(
				'id' => 'street_type',
				'name' => $this->t->_("text_entity_property_street_type"),
				'type' => 'select',
				'style' => 'id', //name
				'linkEntityName' => 'StreetType',
				//'required' => 1,
				'nullSubstitute' => '-',
				'newEntityValue' => null,
			),
			'street' =>	array(
				'id' => 'street',
				'name' => $this->t->_("text_entity_property_street"),
				'type' => 'text',
				//'required' => 1,
				'newEntityValue' => null,
			),
			'house' =>	array(
				'id' => 'house',
				'name' => $this->t->_("text_entity_property_house_building"),
				'type' => 'text',
				'newEntityValue' => null,
			),
			'executor' =>	array(
				'id' => 'executor',
				'name' => $this->t->_("text_entity_property_executor"),
				'type' => 'text',
				'newEntityValue' => null,
			),
			'target_date' =>	array(
				'id' => 'target_date',
				'name' => $this->t->_("text_expense_target_date"),
				'name1' => $this->t->_("text_entity_property_period_from"),
				'name2' => $this->t->_("text_entity_property_period_to"),
				'type' => 'period',
				//'newEntityValue1' => null,
				//'newEntityValue2' => function () { return ["value1" => (new DateTime('now'))->format("Y-m-d"),
				'newEntityValue' => function () { return [
					"value2" => (new DateTime('now'))->format("Y-m-d"),
				]; },
				'required' => 3,	// 1-обязательно value1, 2-обязательно value2, 3-обязательно value1 ИЛИ value2, 4-обязательно value1 И value2
			),
			'created_at' =>	array(
				'id' => 'created_at',
				'name' => $this->t->_("text_entity_property_created_at"),
				'type' => 'text',
				'newEntityValue' => null,
				//'access' => $this->readonlyAccess,
			),
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
		$this->entity->expense_type_id = $this->fields['expense_type']['value_id'];
		$this->entity->expense_status_id = $this->fields['expense_status']['value_id'];
		$this->entity->name = $this->fields['name']['value'];
		$this->entity->amount = $this->fields['amount']['value'];
		$this->entity->settlement = $this->fields['settlement']['value'];
		$this->entity->street_type_id = $this->fields['street_type']['value_id'];
		$this->entity->street = $this->fields['street']['value'];
		$this->entity->house = $this->fields['house']['value'];
		$this->entity->executor = $this->fields['executor']['value'];
		$this->entity->target_date_from = $this->fields['target_date']['value1'];
		$this->entity->target_date_to = $this->fields['target_date']['value2'];
		$this->entity->created_at = (new DateTime('now'))->format("Y-m-d");
		$this->logger->log(__METHOD__ . ". created_at = " . $this->entity->created_at);
	}
	
	/* 
	* Предоставляет текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhql() {
		// строим запрос к БД на выборку данных
		return "SELECT Expense.*, ExpenseType.id AS expense_type_id, ExpenseType.name AS expense_type_name, StreetType.id AS street_type_id, StreetType.name AS street_type_name, ExpenseStatus.id AS expense_status_id, ExpenseStatus.name AS expense_status_name FROM Expense JOIN ExpenseType ON ExpenseType.id=Expense.expense_type_id LEFT JOIN ExpenseStatus ON ExpenseStatus.id=Expense.expense_status_id LEFT JOIN StreetType ON StreetType.id=Expense.street_type_id WHERE Expense.id = '" . $this->filter_values["id"] . "' LIMIT 1";
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		//$this->logger->log(json_encode($row));
		$this->fields["expense_type"]["value"] = $row->expense_type_name;
		$this->fields["expense_type"]["value_id"] = $row->expense_type_id;
		$this->fields["expense_status"]["value"] = $row->expense_status_name;
		$this->fields["expense_status"]["value_id"] = $row->expense_status_id;
		$this->fields["id"]["value"] = $row->expense->id;
		$this->fields["name"]["value"] = $row->expense->name;
		$this->fields["amount"]["value"] = $row->expense->amount;
		$this->fields["settlement"]["value"] = $row->expense->settlement;
		$this->fields["street_type"]["value"] = $row->street_type_name;
		$this->fields["street_type"]["value_id"] = $row->street_type_id;
		$this->fields["street"]["value"] = $row->expense->street;
		$this->fields["house"]["value"] = $row->expense->house;
		$this->fields["executor"]["value"] = $row->expense->executor;
		$this->fields["target_date"]["value1"] = $row->expense->target_date_from;
		$this->fields["target_date"]["value2"] = $row->expense->target_date_to;
		$this->fields["created_at"]["value"] = $row->expense->created_at;
	}
	
	/* 
	* Обновляет данные сущности после сохранения в БД (например, проставляется дата создания записи)
	* Переопределяемый метод.
	*/
	protected function updateEntityFieldsFromModelAfterSave() {
		$this->fields["id"]["value"] = $this->entity->id;
		$this->fields["created_at"]["value"] = $this->entity->created_at;
	}
}