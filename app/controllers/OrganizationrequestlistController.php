<?php
class OrganizationrequestlistController extends ControllerList {
	public $entityName = 'OrganizationRequest';
	public $controllerName = "Organizationrequestlist";
	
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
				'filter' => 'number',
				"sortable" => "DESC",
			),
			'expense' => array(
				'id' => 'expense',
				'name' => $this->controller->t->_("text_organizationrequestlist_expense"),
			),
			'request' => array(
				'id' => 'request',
				'name' => $this->controller->t->_("text_organizationrequestlist_request"),
				'filter' => 'text',
				"sortable" => "DESC",
			),
			'response' => array(
				'id' => 'response',
				'name' => $this->controller->t->_("text_organizationrequestlist_response"),
				'filter' => 'text',
				"sortable" => "DESC",
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
	
	public function fillColumnsWithLists() {
		
		// статусы запросов для фильтрации
		$request_status_rows = RequestStatus::find();
		$request_statuses = array();
		foreach ($request_status_rows as $row) {
			// наполняем массив
			$request_statuses[] = [
				'id' => $row->id,
				"name" => $this->controller->t->_($row->name_code),
			];
		}
		$this->columns['status']['filter_values'] = $request_statuses;
	}
	
	/* 
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {
		$userRoleID = $this->controller->userData['role_id'];
		$userID = $this->controller->userData['id'];
		
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.*, Expense.id AS expense_id, Expense.name AS expense_name, RequestStatus.id AS request_status_id, RequestStatus.name_code AS request_status_name_code FROM <TableName> JOIN Expense on Expense.id=<TableName>.expense_id JOIN RequestStatus on RequestStatus.id=<TableName>.status_id";
		if($userRoleID != $this->config->application->adminRoleID) $phql .= ' JOIN Organization ON Organization.id = <TableName>.organization_id INNER JOIN UserOrganization ON UserOrganization.organization_id = <TableName>.organization_id AND UserOrganization.user_id = ' . $userID;
		
		//$this->logger->log(__METHOD__ . ". userRoleID = " . $userRoleID . ". Config userRoleID = " . $this->config->application->adminRoleID);
		
		$phql .= " WHERE 1=1";
		
		// уточняем выборку, если переданы доп. фильтры, которые могут навязывать внешние контроллеры
		if(isset($this->add_filter["organization_id"])) {
			$phql .= " AND <TableName>.organization_id = " . $this->add_filter["organization_id"];
		}
		
		return $phql;
	}
	
	/* 
	* Заполняет свойство items['fields'] данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		//$this->logger->log(__METHOD__ . ". row = " . json_encode($row));
		$item = [
			"fields" => [
				"id" => [
					'id' => 'id',
					'value' => $row->organizationRequest->id,
				],
				"expense" => [
					'id' => 'expense',
					'value_id' =>  $row->expense_id,
					'value' =>  $row->expense_name,
				],
				"request" => [
					'id' => 'request',
					'value' =>  $row->organizationRequest->request,
				],
				"response" => [
					'id' => 'response',
					'value' =>  $row->organizationRequest->response,
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
