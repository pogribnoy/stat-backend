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
				//'type' => 'number',
				'filter' => 'number',
				'filter_value' => isset($this->filter_values['id']) ? $this->filter_values['id'] : '',
				"sortable" => "DESC",
			),
			'organization' => array(
				'id' => 'organization',
				'name' => $this->controller->t->_("text_organizationrequestlist_organization"),
				//'type' => 'text',
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['organization']) ? $this->filter_values['organization'] : '',
				"sortable" => "DESC",
			),
			'user' => array(
				'id' => 'user',
				'name' => $this->controller->t->_("text_organizationrequestlist_user"),
				//'type' => 'text',
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['user']) ? $this->filter_values['user'] : '',
				"sortable" => "DESC",
			),
			'topic' => array(
				'id' => 'name',
				'name' => $this->controller->t->_("text_organizationrequestlist_topic"),
				//'type' => 'text',
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['topic']) ? $this->filter_values['topic'] : '',
				'style' => 'id',
				"sortable" => "DESC",
			),
			'request' => array(
				'id' => 'request',
				'name' => $this->controller->t->_("text_organizationrequestlist_request"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['request']) ? $this->filter_values['request'] : '',
				"sortable" => "DESC",
			),
			'response' => array(
				'id' => 'response',
				'name' => $this->controller->t->_("text_organizationrequestlist_response"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['response']) ? $this->filter_values['response'] : '',
				"sortable" => "DESC",
			),
			'response_email' => array(
				'id' => 'response_email',
				'name' => $this->controller->t->_("text_organizationrequestlist_response_email"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['response_email']) ? $this->filter_values['response'] : '',
				"sortable" => "DESC",
			),
			'status' => array(
				'id' => 'status',
				'name' => $this->controller->t->_("text_entity_status"),
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['status']) ? $this->filter_values['status'] : '',
				'style' => 'id',
				"sortable" => "DESC",
			),
			'created_at' => array(
				'id' => 'date',
				'name' => $this->controller->t->_("text_entity_property_date"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['date']) ? $this->filter_values['date'] : '',
				"sortable" => "DESC",
			),
			'operations' => array(
				'id' => 'operations',
				'name' => $this->controller->t->_("text_entity_property_actions"),
			)
		);
	}
	
	/* 
	* Заполняет свойство columns данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	public function fillColumnsWithLists() {
		// темы запросов для фильтрации
		$topic_rows = OrganizationRequestTopic::find();
		$topics = array();
		foreach ($topic_rows as $row) {
			// наполняем массив
			$topics[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->columns['topic']['filter_values'] = $topics;
	}
	
	/* 
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {
		$userRoleID = $this->controller->userData['role_id'];
		
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.*, OrganizationRequestTopic.id AS topic_id, OrganizationRequestTopic.name AS topic_name, RequestStatus.id AS request_status_id, RequestStatus.name AS request_status_name FROM <TableName> JOIN OrganizationRequestTopic on OrganizationRequestTopic.id=<TableName>.topic_id JOIN RequestStatus on RequestStatus.id=<TableName>.status_id";
		if($userRoleID != 1) $phql .= ' JOIN Organization ON Organization.id = <TableName>.organization_id INNER JOIN UserOrganization ON UserOrganization.organization_id = <TableName>.organization_id AND UserOrganization.user_id = ' . $userRoleID;
		
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
					'value' => $row->expense->amount ? number_format($row->expense->amount / 100, 2, '.', ' ') : '',
				),
				"date" => array(
					'id' => 'date',
					'value' =>  $row->expense->date,
				),
				"expense_type" => array(
					'value_id' => $row->expense_type_id ? $row->expense_type_id : '',
					'value' => $row->expense_type_name ? $row->expense_type_name : ''
				)
			)
		);
	}
}