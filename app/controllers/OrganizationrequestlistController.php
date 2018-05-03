<?php
class OrganizationrequestlistController extends ControllerList {
	public $entityName = 'OrganizationRequest';
	public $controllerName = "Organizationrequestlist";
	
	public function initColumns() {
		// описатель таблицы
		$this->columns = array(
			'id' => array(
				'id' => 'id',
				'name' => $this->controller->t->_("text_entity_property_id"),
				'filter' => 'number',
				"sortable" => "DESC",
			),
			'expense_type' => array(
				'id' => 'expense_type',
				'name' => $this->controller->t->_("text_organizationrequestlist_expense_type"),
				'filter' => 'select',
				'filter_style' => 'id',
				'filterLinkEntityName' => 'ExpenseType',
				"sortable" => "DESC",
			),
			'expense_name' => array(
				'id' => 'expense_name',
				'name' => $this->controller->t->_("text_organizationrequestlist_expense_name"),
				'filter' => 'text',
				'filterLinkEntityName' => 'Expense',
			),
			'expense_settlement' => array(
				'id' => 'expense_settlement',
				'name' => $this->controller->t->_("text_organizationrequestlist_expense_settlement"),
				//'filter' => 'text',
				//'filterLinkEntityName' => 'Expense',
			),
			'expense_street_type' => array(
				'id' => 'expense_street_type',
				'name' => $this->controller->t->_("text_entity_property_street_type"),
				//'filter' => 'text',
				//'filterLinkEntityName' => 'Expense',
			),
			'expense_street' => array(
				'id' => 'expense_street',
				'name' => $this->controller->t->_("text_entity_property_street"),
				//'filter' => 'text',
				//'filterLinkEntityName' => 'Expense',
			),
			'expense_house' => array(
				'id' => 'expense_house',
				'name' => $this->controller->t->_("text_entity_property_house_building"),
				//'filter' => 'text',
				//'filterLinkEntityName' => 'Expense',
			),
			'response_email' => array(
				'id' => 'response_email',
				'name' => $this->controller->t->_("text_organizationrequestlist_response_email"),
				'filter' => 'text',
				"sortable" => "DESC",
			),
			'status' => array(
				'id' => 'status',
				'name' => $this->controller->t->_("text_entity_property_status"),
				'filter' => 'select',
				'filter_style' => 'id',
				'filterLinkEntityName' => 'RequestStatus',
				'filterLinkEntityFieldID' => 'name_code',
				"sortable" => "DESC",
			),
			'created_at' => array(
				'id' => 'created_at',
				'name' => $this->controller->t->_("text_entity_property_date"),
				'filter' => 'text',
				"sortable" => "DESC",
			),
			'operations' => array(
				'id' => 'operations',
				'name' => $this->controller->t->_("text_entity_property_actions"),
			)
		);
	}
	
	public function getPhqlSelect() {
		$userRoleID = $this->controller->userData['role_id'];
		$userID = $this->controller->userData['id'];
		
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.*, ExpenseType.id AS expense_type_id, ExpenseType.name AS expense_type_name, Expense.id AS expense_id, Expense.name AS expense_name, Expense.settlement AS expense_settlement, Expense.street_type_id AS expense_street_type_id, StreetType.name AS expense_street_type_name, Expense.street AS expense_street, Expense.house AS expense_house, RequestStatus.id AS request_status_id, RequestStatus.name_code AS request_status_name_code FROM <TableName> LEFT JOIN Expense on Expense.id=<TableName>.expense_id LEFT JOIN ExpenseType on ExpenseType.id=Expense.expense_type_id LEFT JOIN StreetType on StreetType.id=Expense.street_type_id JOIN RequestStatus on RequestStatus.id=<TableName>.status_id";
		if($userRoleID != $this->config->application->adminRoleID) $phql .= ' JOIN Organization ON Organization.id = <TableName>.organization_id INNER JOIN UserOrganization ON UserOrganization.organization_id = <TableName>.organization_id AND UserOrganization.user_id = ' . $userID;
		
		//$this->logger->log(__METHOD__ . ". userRoleID = " . $userRoleID . ". Config userRoleID = " . $this->config->application->adminRoleID);
		
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
		/*if ($id == 'target_date') {
			if(isset($column["nullSubstitute"]) && $filter_values[$id] == $column["nullSubstitute"]) return $phql .= " AND (<TableName>." . $id . "_from IS NULL OR <TableName>." . $id . "_from = '' OR <TableName>." . $id . "_from = '" . $column["nullSubstitute"] . "' OR (<TableName>." . $id . "_to IS NULL OR <TableName>." . $id . "_to = '' OR <TableName>." . $id . "_to = '" . $column["nullSubstitute"] . "'))";
			else return $phql .= " AND (<TableName>." . $id . "_from LIKE '%" . $filter_values[$id] . "%' OR <TableName>." . $id . "_to LIKE '%" . $filter_values[$id] . "%')";
		}*/
		if ($id == 'expense_type') {
			$fid = 'ExpenseType.id';
			if(isset($column["nullSubstitute"]) && $filter_values[$id] == $column["nullSubstitute"]) return $phql .= " AND (" . $fid . " IS NULL OR " . $fid . " = '' OR " . $fid . " = '" . $column["nullSubstitute"] . ")";
			else return $phql .= " AND " . $fid . " = " . $filter_values[$id];
		}
		if ($id == 'expense_name') {
			$fid = 'Expense.name';
			if(isset($column["nullSubstitute"]) && $filter_values[$id] == $column["nullSubstitute"]) return $phql .= " AND (" . $fid . " IS NULL OR " . $fid . " = '' OR " . $fid . " = '" . $column["nullSubstitute"] . ")";
			else return $phql .= " AND " . $fid . " LIKE '%" . $filter_values[$id] . "%'";
		}
		return null; 
	}
	
	protected function addSpecificSortLimitToPhql($phql, $id) {
		$filter_values = $this->filter_values;
		$column =  $this->columns[$id];
		if ($id == 'expense_type') {
			$fid = 'ExpenseType.name';
			return $phql .= ' ORDER BY ' . $fid . ' ' . $filter_values['order'];
		}
		return null; 
	}
	
	public function fillFieldsFromRow($row) {
		//$this->logger->log(__METHOD__ . ". row = " . json_encode($row));
		$item = [
			"fields" => [
				"id" => [
					'id' => 'id',
					'value' => $row->organizationRequest->id,
				],
				"expense_type" => [
					'id' => 'expense_type',
					'value_id' =>  $row->expense_type_id,
					'value' =>  $row->expense_type_name,
				],
				"expense_name" => [
					'id' => 'expense_name',
					'value_id' =>  $row->expense_id,
					'value' =>  $row->expense_name,
				],
				"expense_settlement" => [
					'id' => 'expense_settlement',
					'value_id' =>  $row->expense_id,
					'value' =>  $row->expense_settlement,
				],
				"expense_street_type" => [
					'id' => 'expense_street_type',
					'value_id' =>  $row->expense_street_type_id,
					'value' =>  $row->expense_street_type_name,
				],
				"expense_street" => [
					'id' => 'expense_street',
					'value_id' =>  $row->expense_id,
					'value' =>  $row->expense_street,
				],
				"expense_house" => [
					'id' => 'expense_house',
					'value_id' =>  $row->expense_id,
					'value' =>  $row->expense_house,
				],
				"response_email" => [
					'id' => 'response_email',
					'value' =>  $row->organizationRequest->response_email,
				],
				"status" => [
					'id' => 'status',
					'value_id' =>  $row->request_status_id,
					'value' =>  $this->controller->t->_($row->request_status_name_code),
				],
				"created_at" => [
					'id' => 'created_at',
					'value' =>  $row->organizationRequest->created_at,
				],
			]
		];
		if($row->request_status_id === $this->config['application']['requestStatus']['newStatusID']) $this->newCount++;
		//$this->logger->log(__METHOD__ . ". request_status_id = " . $row->request_status_id . ". Config newStatusID = " . $this->config->application->requestStatus->newStatusID . " . Сравнение: " . ($row->request_status_id === $this->config->application->requestStatus->newStatusID) . ". newCount = " . $this->newCount);
		
		$this->items[] = $item;
	}
}
