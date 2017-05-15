<?php
class UserrolelistController extends ControllerList{
	public $entityName = 'UserRole';
	public $controllerName = "Userrolelist";
	
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
				"sortable" => "DESC"
			),
			'active' => array(
				'id' => 'active',
				'name' => $this->controller->t->_("text_entity_property_active"),
				'type' => 'bool',
				'filter' => 'bool',
				'filter_value' => isset($this->filter_values['active']) ? $this->filter_values['active'] : '',
				"sortable" => "DESC"
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_name"),
				'type' => 'text',
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['name']) ? $this->filter_values['name'] : '',
				"sortable" => "DESC"
			),
			'operations' => array(
				'id' => 'operations',
				'name' => $this->controller->t->_("text_entity_property_actions")
			)
		);
	}
	
	/* 
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.* FROM <TableName> WHERE 1=1";
		
		$userRoleID = $this->controller->userData['role_id'];
		//$this->logger->log('getPhqlSelect    this->filter_values["user_role_id"])=' . $this->filter_values["user_role_id"]);
		// расширяем выборку, если переданы доп. фильтры (для связей 1-n, n-1, n-n), которые могут навязывать внешние контроллеры
		if($userRoleID == $this->config->application->orgAdminRoleID) {
			$phql .= " AND <TableName>.id IN (" . $this->config->application->orgOperatorRoleID . ", " . $this->config->application->orgAdminRoleID . ")";
		}
		
		//$this->logger->log(__METHOD__ . ". add_filter=" . json_encode($this->add_filter));
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
					'value' => $row->id
				),
				"active" => array(
					'id' => 'active',
					'value' =>  $row->active
				),
				"name" => array(
					'id' => 'name',
					'value' =>  $row->name
				)
			)
		);
	}
}
