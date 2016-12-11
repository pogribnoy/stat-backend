<?php
class ExpenseListController extends ControllerList {
	public $entityName = 'expense';
	public $controllerName = "expenselist";
	
	public function initialize() {
		parent::initialize();
	}
	
	/* 
	* Заполняет (инициализирует) свойство colmns
	* Переопределяемый метод.
	*/
	public function initColumns() {
		// описатель таблицы
		$this->columns = array(
			'id' => array(
				'id' => 'id',
				'name' => $this->controller->t->_("text_entity_property_id"),
				'type' => 'number',
				'filter' => 'number',
				'filter_value' => isset($this->filter_values['id']) ? $this->filter_values['id'] : '',
				"sortable" => "DESC",
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_name"),
				'type' => 'text',
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['name']) ? $this->filter_values['name'] : '',
				"sortable" => "DESC",
			),
			'amount' => array(
				'id' => 'amount',
				'name' => $this->controller->t->_("text_entity_property_amount"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['amount']) ? $this->filter_values['amount'] : '',
				"sortable" => "DESC",
			),
			/*'date' => array(
				'id' => 'date',
				'name' => $this->controller->t->_("text_entity_property_date"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['date']) ? $this->filter_values['date'] : '',
				"sortable" => "DESC",
			),*/
			'settlement' => array(
				'id' => 'settlement',
				'name' => $this->controller->t->_("text_expenselist_settlement"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['settlement']) ? $this->filter_values['settlement'] : '',
				"sortable" => "DESC",
			),
			'street_type' => array(
				'id' => 'street_type',
				'name' => $this->controller->t->_("text_entity_property_street_type"),
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['street_type_id']) ? $this->filter_values['street_type_id'] : '',
				'filter_id' => 'street_type_id', // задается, если отличается от id
				'style' => 'id',
				"sortable" => "DESC",
			),
			'street' => array(
				'id' => 'street',
				'name' => $this->controller->t->_("text_entity_property_street"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['street']) ? $this->filter_values['street'] : '',
				"sortable" => "DESC",
			),
			'house' => array(
				'id' => 'house',
				'name' => $this->controller->t->_("text_entity_property_house_building"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['house']) ? $this->filter_values['house'] : '',
				"sortable" => "DESC",
			),
			'expense_type' => array(
				'id' => 'expense_type',
				'name' => $this->controller->t->_("text_expenselist_expense_type"),
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['expense_type']) ? $this->filter_values['expense_type'] : '',
				'filter_id' => 'expense_type_id', // задается, если отличается от id
				'style' => 'id',
				"sortable" => "DESC",
			),
			'expense_status' => array(
				'id' => 'expense_status',
				'name' => $this->controller->t->_("text_entity_property_status"),
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['expense_status']) ? $this->filter_values['expense_status'] : '',
				'filter_id' => 'expense_status_id', // задается, если отличается от id
				'style' => 'id',
				"sortable" => "DESC",
			),
			/*'target_date' => array(
				'id' => 'target_date',
				'name' => $this->controller->t->_("text_entity_property_date"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['target_date']) ? $this->filter_values['target_date'] : '',
				//"sortable" => "DESC",
			),
			*/
			'executor' => array(
				'id' => 'executor',
				'name' => $this->controller->t->_("text_entity_property_executor"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['executor']) ? $this->filter_values['executor'] : '',
				"sortable" => "DESC",
			),
			'target_date' => array(
				'id' => 'target_date',
				'name' => $this->controller->t->_("text_expenselist_target_date"),
				'hideble' => 1,
			),
			'operations' => array(
				'id' => 'operations',
				'name' => $this->controller->t->_("text_entity_property_actions")
			)
		);
	}
	
	/* 
	* Заполняет свойство columns данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	public function fillColumnsWithLists() {
		// типы расходов для фильтрации
		$expense_type_rows = ExpenseType::find();
		$expense_types = array();
		foreach ($expense_type_rows as $row) {
			// наполняем массив
			$expense_types[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->columns['expense_type']['filter_values'] = $expense_types;
		
		// статусы расходов для фильтрации
		$expense_status_rows = ExpenseStatus::find();
		$expense_statuses = array();
		foreach ($expense_status_rows as $row) {
			// наполняем массив
			$expense_statuses[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->columns['expense_status']['filter_values'] = $expense_statuses;
		
		// типы улиц для фильтрации
		$street_type_rows = StreetType::find();
		$street_types = array();
		foreach ($street_type_rows as $row) {
			// наполняем массив
			$street_types[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->columns['street_type']['filter_values'] = $street_types;
	}
	
	/* 
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {
		$userRoleID = $this->controller->userData['role_id'];
		
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.*, ExpenseType.id AS expense_type_id, ExpenseType.name AS expense_type_name, ExpenseStatus.id AS expense_status_id, ExpenseStatus.name AS expense_status_name, StreetType.id AS street_type_id, StreetType.name AS street_type_name FROM <TableName> LEFT JOIN ExpenseType on ExpenseType.id=<TableName>.expense_type_id LEFT JOIN ExpenseStatus ON ExpenseStatus.id = <TableName>.expense_status_id LEFT JOIN StreetType ON StreetType.id = <TableName>.street_type_id";
		if($userRoleID != 1) $phql .= ' JOIN Organization ON Organization.id = <TableName>.organization_id INNER JOIN UserOrganization ON UserOrganization.organization_id = <TableName>.organization_id AND UserOrganization.user_id = ' . $this->controller->userData['id'];
		
		$phql .= " WHERE 1=1";
		
		// уточняем выборку, если переданы доп. фильтры, которые могут навязывать внешние контроллеры
		if(isset($this->add_filter["organization_id"])) {
			$phql .= " AND <TableName>.organization_id = " . $this->add_filter["organization_id"];
		}
		
		return $phql;
	}
	
	/* 
	* Добавляет текст запроса к БД параметры фильтрации
	* Расширяемый метод
	*/
	public function addFilterValuesToPhql($phql) {
		$phql = parent::addFilterValuesToPhql($phql);
		
		//if(isset($this->filter_values["expense_type_id"]) && isset($this->columns['expense_type_id'])) $phql .= " AND ExpenseType.id = '" . $this->filter_values["expense_type_id"] . "'";
		
		return $phql;
	}
	
	/* 
	* Заполняет свойство items['fields'] данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		$this->items[] = array(
			"fields" => array(
				"id" => array(
					'id' => 'id',
					'value' => $row->expense->id,
				),
				"name" => array(
					'id' => 'name',
					'value' =>  $row->expense->name,
				),
				"amount" => array(
					'id' => 'amount',
					//'value' => $row->expense->amount != null ? number_format($row->expense->amount / 100, 2, '.', ' ') : '',
					'value' => $row->expense->amount ? $row->expense->amount : '',
				),
				/*"date" => array(
					'id' => 'date',
					'value' =>  $row->expense->date,
				),*/
				"settlement" => array(
					'id' => 'settlement',
					'value' =>  $row->expense->settlement ? $row->expense->settlement : '',
				),
				"street_type" => array(
					'id' => 'street_type',
					'value_id' => $row->street_type_id ? $row->street_type_id : '',
					'value' => $row->street_type_name ? $row->street_type_name : '',
				),
				"street" => array(
					'id' => 'street',
					'value' =>  $row->expense->street ? $row->expense->street : '',
				),
				"house" => array(
					'id' => 'house',
					'value' =>  $row->expense->house ? $row->expense->house : '',
				),
				"executor" => array(
					'id' => 'executor',
					'value' =>  $row->expense->executor ? $row->expense->executor : '',
				),
				"expense_type" => array(
					'id' => 'expense_type',
					'value_id' => $row->expense_type_id ? $row->expense_type_id : '',
					'value' => $row->expense_type_name ? $row->expense_type_name : '',
				),
				"expense_status" => array(
					'id' => 'expense_status',
					'value_id' => $row->expense_status_id ? $row->expense_status_id : '',
					'value' => $row->expense_status_name ? $row->expense_status_name : '',
				),
				"target_date" => array(
					'id' => 'target_date',
					'value1' => $row->expense->target_date_from ? $row->expense->target_date_from : '',
					'value2' => $row->expense->target_date_to ? $row->expense->target_date_to : '',
				),
			)
		);
	}
}