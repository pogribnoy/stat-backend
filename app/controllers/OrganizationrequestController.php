<?php
class OrganizationrequestController extends ControllerEntity {
	public $entityName = 'OrganizationRequest';
	public $tableName = 'organization_request';
	
	public $access = [
		"edit" => [
			//"id" => self::hiddenAccess,
			//"status" => self::hiddenAccess,
			//"response" => self::hiddenAccess,
			//"created_at" => self::hiddenAccess,
		],
	];
	
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
			'expense' => array(
				'id' => 'expense',
				'name' => $this->t->_("text_organizationrequest_expense"),
				'type' => 'link',
				'style' => 'id', //name
				'linkEntityName' => 'Expense',
				'required' => 2,
				//'newEntityValue' => null,
			), 
			'status' => array(
				'id' => 'status',
				'name' => $this->t->_("text_entity_property_status"),
				'type' => 'select',
				'style' => 'id', //name
				'linkEntityName' => 'RequestStatus',
				'linkEntityField' => 'name_code',
				'newEntityID' => "1",
				//'newEntityValue' => $this->t->_("status_new"),
			), 
			'request' => array(
				'id' => 'request',
				'name' => $this->t->_("text_organizationrequest_request"),
				'type' => 'textarea',
				'required' => 2,
				'newEntityValue' => null,
			), 
			'response' => array(
				'id' => 'response',
				'name' => $this->t->_("text_organizationrequest_response"),
				'type' => 'textarea',
				'required' => 1,
				'newEntityValue' => "-",
			), 
			'response_email' => array(
				'id' => 'response_email',
				'name' => $this->t->_("text_organizationrequest_response_email"),
				'type' => 'text',
				'required' => 2,
				'newEntityValue' => null,
			), 
			'created_at' => array(
				'id' => 'created_at',
				'name' => $this->t->_("text_entity_property_created_at"),
				'type' => 'date',
				'required' => 0,
				'newEntityValue' => null,
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
		$this->entity->status_id = $this->fields['status']['value_id'];
		$this->entity->expense_id = $this->fields['expense']['value_id'];
		$this->entity->request = $this->fields['request']['value'];
		$this->entity->response = $this->fields['response']['value'];
		$this->entity->response_email = $this->fields['response_email']['value'];
		
		if($this->isFieldAccessibleForUser($this->fields['created_at'])) $this->entity->created_at = (new DateTime($this->fields['created_at']['value']))->format("Y-m-d H:i:s");
		else $this->entity->created_at = (new DateTime('now'))->format("Y-m-d H:i:s");
	}
	
	/* 
	* Предоставляет текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhql() {
		// строим запрос к БД на выборку данных
		return "SELECT OrganizationRequest.*, Expense.id AS expense_id, Expense.name AS expense_name, RequestStatus.id AS request_status_id, RequestStatus.name_code AS request_status_name_code FROM OrganizationRequest JOIN Expense on Expense.id=OrganizationRequest.expense_id JOIN RequestStatus on RequestStatus.id=OrganizationRequest.status_id JOIN Organization on Organization.id=OrganizationRequest.organization_id WHERE OrganizationRequest.id = '" . $this->filter_values["id"] . "' LIMIT 1";
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		$this->logger->log(json_encode($row));
		$this->fields["id"]["value"] = $row->organizationRequest->id;
		$this->fields["expense"]["value"] = $row->expense_name;
		$this->fields["expense"]["value_id"] = $row->expense_id;
		$this->fields["request"]["value"] = $row->organizationRequest->request;
		$this->fields["response"]["value"] = $row->organizationRequest->response;
		$this->fields["response_email"]["value"] = $row->organizationRequest->response_email;
		$this->fields["status"]["value"] = $this->t->_($row->request_status_name_code);
		$this->fields["status"]["value_id"] = $row->request_status_id;
		$this->fields["created_at"]["value"] = $row->organizationRequest->created_at;
	}
	
	public function customizeFields() {
		$field = &$this->fields['status'];
		$newStatusID = $this->config['application']['requestStatus']['newStatusID'];
		$processedStatusID = $this->config['application']['requestStatus']['processedStatusID'];
		
		if(isset($field['values']) && $field['values'] != null && count($field['values']) > 0) {
			//$this->logger->log(__METHOD__ . ". value_id = " . $field['value_id']);
			if($field['value_id'] == $newStatusID) {
				//$this->logger->log(__METHOD__ . ". value_id = 1");
				foreach ($field['values'] as $id => &$value) {
					//$this->logger->log(__METHOD__ . ". id = " . $value['id']);
					if($value['id'] != $processedStatusID && $value['id'] != $newStatusID) unset($field['values'][$id]);
				}
			}
			else {
				foreach ($field['values'] as $id => $value) {
					//$this->logger->log(__METHOD__ . ". unset id = " . $value['id']);
					unset($field['values']);
					if($field['access'] == $this::editAccess) $field['access'] = $this::readonlyAccess;
				}
				if($this->fields['response']['access'] == $this::editAccess) $this->fields['response']['access'] = $this::readonlyAccess;
				foreach ($this->operations as $id => $operation) {
					if($operation['id'] == 'save') unset($this->operations[$id]);
					elseif($operation['id'] == 'check') unset($this->operations[$id]);
				}
				$this->operations = array_values($this->operations);
			}	
			$field['values'] = array_values($field['values']);
		}
		//$this->logger->log(__METHOD__ . ". values = " . json_encode($field['values']));
		//$this->logger->log(__METHOD__ . ". values2 = " . json_encode($this->fields['status']['values']));
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
