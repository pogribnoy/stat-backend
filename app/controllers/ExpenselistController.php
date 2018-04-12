<?php
class ExpenselistController extends ControllerList {
	public $entityName = 'Expense';
	public $controllerName = "Expenselist";
	
	public function initColumns() {
		// описатель таблицы
		$this->columns = array(
			'id' => array(
				'id' => 'id',
				'name' => $this->controller->t->_("text_entity_property_id"),
				'type' => 'number',
				'filter' => 'number',
				"sortable" => "DESC",
				'hideble' => 1,
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_name"),
				'type' => 'text',
				'filter' => 'text',
				"sortable" => "DESC",
			),
			'amount' => array(
				'id' => 'amount',
				'name' => $this->controller->t->_("text_entity_property_amount"),
				'filter' => 'text',
				"sortable" => "DESC",
			),
			'settlement' => array(
				'id' => 'settlement',
				'name' => $this->controller->t->_("text_expenselist_settlement"),
				'filter' => 'text',
				"sortable" => "DESC",
			),
			/*'street_type' => array(
				'id' => 'street_type',
				'name' => $this->controller->t->_("text_entity_property_street_type"),
				'filter' => 'select',
				'filter_style' => 'id',
				'filterLinkEntityName' => 'StreetType',
				"sortable" => "DESC",
			),*/
			'street' => array(
				'id' => 'street',
				'name' => $this->controller->t->_("text_entity_property_street"),
				'filter' => 'text',
				"sortable" => "DESC",
			),
			'house' => array(
				'id' => 'house',
				'name' => $this->controller->t->_("text_entity_property_house_building"),
				'filter' => 'text',
				"sortable" => "DESC",
			),
			'expense_type' => array(
				'id' => 'expense_type',
				'name' => $this->controller->t->_("text_expenselist_expense_type"),
				'filter' => 'select',
				'filter_style' => 'id',
				'filterLinkEntityName' => 'ExpenseType',
				"sortable" => "DESC",
			),
			'expense_status' => array(
				'id' => 'expense_status',
				'name' => $this->controller->t->_("text_entity_property_status"),
				'filter' => 'select',
				'filter_style' => 'id',
				'filterLinkEntityName' => 'ExpenseStatus',
				"sortable" => "DESC",
			),
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
				'filter' => 'text',
				//'hideble' => 1,
			),
			'operations' => array(
				'id' => 'operations',
				'name' => $this->controller->t->_("text_entity_property_actions")
			)
		);
	}
	
	public function getPhqlSelect() {
		$userRoleID = $this->controller->userData['role_id'];
		
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.*, ExpenseType.id AS expense_type_id, ExpenseType.name AS expense_type_name, ExpenseStatus.id AS expense_status_id, ExpenseStatus.name AS expense_status_name, StreetType.id AS street_type_id, StreetType.name AS street_type_name FROM <TableName> LEFT JOIN ExpenseType on ExpenseType.id=<TableName>.expense_type_id LEFT JOIN ExpenseStatus ON ExpenseStatus.id = <TableName>.expense_status_id LEFT JOIN StreetType ON StreetType.id = <TableName>.street_type_id";
		if($userRoleID != 1) $phql .= ' JOIN Organization ON Organization.id = <TableName>.organization_id INNER JOIN UserOrganization ON UserOrganization.organization_id = <TableName>.organization_id AND UserOrganization.user_id = ' . $this->controller->userData['id'];
		
		$phql .= " WHERE <TableName>.deleted_at IS NULL";
		
		// уточняем выборку, если переданы доп. фильтры, которые могут навязывать внешние контроллеры
		if(isset($this->add_filter["organization_id"])) {
			$phql .= " AND <TableName>.organization_id = " . $this->add_filter["organization_id"];
		}
		
		return $phql;
	}
	
	protected function addSpecificFilterValuesToPhql($phql, $id) { 
		$filter_values = $this->filter_values;
		$column =  $this->columns[$id];
		if ($id == 'target_date') {
			if(isset($column["nullSubstitute"]) && $filter_values[$id] == $column["nullSubstitute"]) return $phql .= " AND (<TableName>." . $id . "_from IS NULL OR <TableName>." . $id . "_from = '' OR <TableName>." . $id . "_from = '" . $column["nullSubstitute"] . "' OR (<TableName>." . $id . "_to IS NULL OR <TableName>." . $id . "_to = '' OR <TableName>." . $id . "_to = '" . $column["nullSubstitute"] . "'))";
			else return $phql .= " AND (<TableName>." . $id . "_from LIKE '%" . $filter_values[$id] . "%' OR <TableName>." . $id . "_to LIKE '%" . $filter_values[$id] . "%')";
		}
		return null; 
	}

	
	protected function addSpecificSortLimitToPhql($phql, $id) {
		$filter_values = $this->filter_values;
		if($id == 'expense_status') return $phql .= ' ORDER BY ExpenseStatus.name ' . $filter_values['order'];
		else if($id == 'expense_type') return $phql .= ' ORDER BY ExpenseType.name ' . $filter_values['order'];
		//else if ($id == 'street_type') return $phql .= ' ORDER BY StreetType.name ' . $filter_values['order'];
		return null;
	}
	
	protected function addSortToPhql($phql) {
		$phql = parent::addSortToPhql($phql);
		$phql .= ', Expense.street ' . $this->filter_values['order'];
		return $phql;
	}
	
	
	
	public function fillFieldsFromRow($row) {
		$item = [
			"fields" => [
				"id" => [
					'id' => 'id',
					'value' => $row->expense->id,
				],
				"name" => [
					'id' => 'name',
					'value' =>  $row->expense->name,
				],
				"amount" => [
					'id' => 'amount',
					//'value' => $row->expense->amount != null ? number_format($row->expense->amount / 100, 2, '.', ' ') : '',
					'value' => $row->expense->amount ? $row->expense->amount : '',
				],
				"settlement" => [
					'id' => 'settlement',
					'value' =>  $row->expense->settlement ? $row->expense->settlement : '',
				],
				/*"street_type" => [
					'id' => 'street_type',
					'value_id' => $row->street_type_id ? $row->street_type_id : '',
					'value' => $row->street_type_name ? $row->street_type_name : '',
				],*/
				"street" => [
					'id' => 'street',
					'value' => $this->getFieldValueFromModel($row->expense->street, $this->columns["street"]),//($row->expense->street && !($this->columns["street"]["nullSubstitute"] && $row->expense->street == $this->columns["street"]["nullSubstitute"])) ? $row->expense->street : '',
					'values' => [
						'street_type' => [
							'value_id' => $row->street_type_id ? $row->street_type_id : '',
							'value' => $row->street_type_name ? $row->street_type_name : '',
						],
					],
				],
				"house" => [
					'id' => 'house',
					'value' =>  $row->expense->house ? $row->expense->house : '',
				],
				"executor" => [
					'id' => 'executor',
					'value' =>  $row->expense->executor ? $row->expense->executor : '',
				],
				"expense_type" => [
					'id' => 'expense_type',
					'value_id' => $row->expense_type_id ? $row->expense_type_id : '',
					'value' => $row->expense_type_name ? $row->expense_type_name : '',
				],
				"expense_status" => [
					'id' => 'expense_status',
					'value_id' => $row->expense_status_id ? $row->expense_status_id : '',
					'value' => $row->expense_status_name ? $row->expense_status_name : '',
				],
				"target_date" => [
					'id' => 'target_date',
					'value1' => $row->expense->target_date_from ? $row->expense->target_date_from : '',
					'value2' => $row->expense->target_date_to ? $row->expense->target_date_to : '',
				],
			]
		];
		
		$this->items[] = $item;
	}
	
}
