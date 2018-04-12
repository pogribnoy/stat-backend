<?php
class UserrolelistController extends ControllerList{
	public $entityName = 'UserRole';
	public $controllerName = "Userrolelist";
	
	public function initColumns() {
		// описатель таблицы
		$this->columns = array(
			'id' => array(
				'id' => 'id',
				'name' => $this->controller->t->_("text_entity_property_id"),
				'filter' => 'number',
				"sortable" => "DESC"
			),
			'active' => array(
				'id' => 'active',
				'name' => $this->controller->t->_("text_entity_property_active"),
				'filter' => 'bool',
				"sortable" => "DESC"
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_name"),
				'filter' => 'text',
				"sortable" => "DESC"
			),
			'operations' => array(
				'id' => 'operations',
				'name' => $this->controller->t->_("text_entity_property_actions")
			)
		);
	}
	
	public function getPhqlSelect() {
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.* FROM <TableName> WHERE <TableName>.deleted_at IS NULL";
		
		$userRoleID = $this->controller->userData['role_id'];
		//$this->logger->log('getPhqlSelect    this->filter_values["user_role_id"])=' . $this->filter_values["user_role_id"]);
		// расширяем выборку, если переданы доп. фильтры (для связей 1-n, n-1, n-n), которые могут навязывать внешние контроллеры
		if($userRoleID == $this->config->application->orgAdminRoleID) {
			$phql .= " AND <TableName>.id IN (" . $this->config->application->orgOperatorRoleID . ", " . $this->config->application->orgAdminRoleID . ")";
		}
		
		//$this->logger->log(__METHOD__ . ". add_filter=" . json_encode($this->add_filter));
		return $phql;
	}
	
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
