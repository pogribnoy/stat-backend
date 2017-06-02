<?php
class UserlistController extends ControllerList {
	public $entityName = 'User';
	public $controllerName = "Userlist";
	
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
			'active' => array(
				'id' => 'active',
				'name' => $this->controller->t->_("text_entity_property_active"),
				'filter' => 'bool',
				"sortable" => "DESC",
			),
			'email' => array(
				'id' => 'email',
				'name' => $this->controller->t->_("text_entity_property_email"),
				'filter' => 'text',
				"sortable" => "DESC",
			),
			'user_role' => array(
				'id' => 'user_role',
				'name' => $this->controller->t->_("text_entity_property_role"),
				'filter' => 'select',
				'filter_style' => 'id',
				'filterLinkEntityName' => 'UserRole',
				'filterFillConditions' => function() { 
					$userRoleID = $this->controller->userData['role_id'];
					$conditions = ''; 

					if($userRoleID == $this->controller->config->application->orgAdminRoleID) $conditions .= "id IN (" . $this->config->application->orgOperatorRoleID . ", " . $this->config->application->orgAdminRoleID . ")";
					return $conditions; 
				},
				"sortable" => "DESC",
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_fio"),
				'filter' => 'text',
				"sortable" => "DESC",
			),
			'phone' => array(
				'id' => 'phone',
				'name' => $this->controller->t->_("text_entity_property_phone"),
				'filter' => 'text',
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
	/**/public function fillColumnsWithLists() {
		$userRoleID = $this->controller->userData['role_id'];
		
		// роли пользователей для фильтрации
		$conditions = '';
		//$this->logger->log(__METHOD__ . '. userRoleID=' . $userRoleID . ", orgAdminRoleID=" . $this->controller->config->application->orgAdminRoleID);
		if($userRoleID == $this->controller->config->application->orgAdminRoleID) $conditions .= "id IN (" . $this->config->application->orgOperatorRoleID . ", " . $this->config->application->orgAdminRoleID . ")";
		$user_role_rows = UserRole::find(['conditions' => $conditions, 'order' => 'name ASC']);
		$user_roles = array();
		foreach ($user_role_rows as $row) {
			// наполняем массив
			$user_roles[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->columns['user_role']['filter_values'] = $user_roles;
	}
	
	/* 
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {
		$userRoleID = $this->controller->userData['role_id'];
		$userID = $this->controller->userData['id'];
		
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.*, UserRole.id AS user_role_id, UserRole.name AS user_role_name FROM <TableName> JOIN UserRole ON UserRole.id=<TableName>.user_role_id";
		
		// уточняем выборку, если переданы доп. фильтры (для связей 1-n, n-1, n-n), которые могут навязывать внешние контроллеры
		if(isset($this->add_filter["organization_id"])) $phql .= " JOIN UserOrganization AS uo1 ON uo1.user_id=<TableName>.id AND uo1.organization_id=" . $this->add_filter["organization_id"];
		
		// если не супервользователь, то проверям пересечение по спискам организаций
		if($userRoleID != $this->config->application->adminRoleID) {
			if(isset($this->add_filter["organization_id"])) $phql .= " JOIN UserOrganization AS uo2 ON uo2.organization_id = uo1.organization_id AND uo2.user_id=" . $userID;
			else $phql .= " JOIN UserOrganization AS uo1 ON uo1.user_id=<TableName>.id AND uo1.user_id=" . $userID;
		}
		
		$phql .= " WHERE 1=1";
		
		// если у пользователя роль "Администратор муниципалитета", то у пользователя из списка должна быть роль "Оператор" или "Администратор муниципалитета"
		if($userRoleID == $this->config->application->orgAdminRoleID) $phql .= " AND <TableName>.user_role_id IN (" . $this->config->application->orgOperatorRoleID . ", " . $this->config->application->orgAdminRoleID . ")";
		
		return $phql;
	}
	
	/* 
	* Добавляет текст запроса к БД параметры фильтрации
	* Расширяемый метод
	*/
	public function addFilterValuesToPhql($phql) {
		$phql = parent::addFilterValuesToPhql($phql);
		
		if(isset($this->filter_values["user_role_id"])) $phql .= " AND UserRole.id = '" . $this->filter_values["user_role_id"] . "'";
		
		return $phql;
	}
	
	/* 
	* Заполняет свойство items['fields'] данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		$item = [
			"fields" => [
				"id" => [
					'id' => 'id',
					'value' => $row->user->id
				],
				"active" => [
					'id' => 'active',
					'value' =>  $row->user->active
				],
				"phone" => [
					'id' => 'phone',
					'value' =>  $row->user->phone
				],
				"email" => [
					'id' => 'email',
					'value' =>  $row->user->email
				],
				"name" => [
					'id' => 'name',
					'value' =>  $row->user->name
				],
				"user_role" => [
					'id' => $row->user_role_id ? $row->user_role_id : '',
					'value' => $row->user_role_name ? $row->user_role_name : ''
				],
			]
		];
		$this->items[] = $item;
	}
	
	protected function addSpecificSortLimitToPhql($phql, $id) {
		if ($id == 'user_role') return $phql .= ' ORDER BY UserRole.name ' . $this->filter_values['order'];
		return null;
	}
	
}
