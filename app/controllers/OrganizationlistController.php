<?php
class OrganizationlistController extends ControllerList {
	public $entityName = 'Organization';
	public $controllerName = "Organizationlist";
	
	// информация о поле и направлении сортировки по умолчанию
	public $defaultSort = [
		"column" => "name",
		"order" => "asc",
	];
	
	public function initialize() {
		parent::initialize();
	}
	
	/* 
	* Заполняет свойство fields данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	public function fillFieldsWithLists() {
		// регионы
		$regions_rows = Region::find();
		$regions = array();
		foreach ($regions_rows as $row) {
			// наполняем массив
			$regions[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->fields['region']['values'] = $regions;
	}
	
	/* 
	* Заполняет (инициализирует) свойство colmns
	* Переопределяемый метод.
	*/
	public function initColumns() {
		// описатель таблицы
		$this->columns = [
			'id' => [
				'id' => 'id',
				'name' => $this->controller->t->_("text_entity_property_id"),
				'filter' => 'number',
				//'filter_value' => isset($this->filter_values['id']) ? $this->filter_values['id'] : '',
				"sortable" => "DESC"
			],
			'name' => [
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_name"),
				'filter' => 'text',
				//'filter_value' => isset($this->filter_values['name']) ? $this->filter_values['name'] : '',
				"sortable" => "DESC"
			],
			'region' => [
				'id' => 'region',
				'name' => $this->controller->t->_("text_organizationlist_region"),
				'filter' => 'select',
				//'filter_value' => isset($this->filter_values['region']) ? $this->filter_values['region'] : '',
				'filter_style' => 'id',
				"sortable" => "DESC"
			],
			'contacts' => [
				'id' => 'contacts',
				'name' => $this->controller->t->_("text_entity_property_contacts"),
				'filter' => 'text',
				//'filter_value' => isset($this->filter_values['contacts']) ? $this->filter_values['contacts'] : ''
			],
			'email' => [
				'id' => 'email',
				'name' => $this->controller->t->_("text_entity_property_email"),
				'filter' => 'email',
				//'filter_value' => isset($this->filter_values['email']) ? $this->filter_values['email'] : '',
				"sortable" => "DESC"
			],
			'operations' => [
				'id' => 'operations',
				'name' => $this->controller->t->_("text_entity_property_actions")
			],
		];
	}
	
	/* 
	* Заполняет свойство columns данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	public function fillColumnsWithLists() {
		// регионы для фильтрации
		$regions_rows = Region::find();
		$regions = array();
		foreach ($regions_rows as $row) {
			// наполняем массив
			$regions[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->columns['region']['filter_values'] = $regions;
	}
	
	/* 
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {
		$userRoleID = $this->controller->userData['role_id'];
		$userID = $this->controller->userData['id'];
		
		// строим запрос к БД на выборку данных
		//$phql = "SELECT <TableName>.*, Region.id AS region_id, Region.name AS region_name FROM <TableName> JOIN Region on Region.id=<TableName>.region_id";
		$phql = "SELECT <TableName>.*, Region.id AS region_id, Region.name AS region_name FROM <TableName> JOIN Region on Region.id=<TableName>.region_id";
		
		// уточняем выборку, если переданы доп. фильтры (для связей 1-n, n-1, n-n), которые могут навязывать внешние контроллеры
		if(isset($this->add_filter["user_id"])) $phql .= " JOIN UserOrganization AS uo1 ON uo1.organization_id=<TableName>.id AND uo1.user_id=" . $this->add_filter["user_id"];
		
		// если не супервользователь, то проверям пересечение по спискам организаций
		if($userRoleID != $this->config->application->adminRoleID) {
			if(isset($this->add_filter["user_id"])) $phql .= " JOIN UserOrganization AS uo2 ON uo2.organization_id = uo1.organization_id AND uo2.user_id=" . $userID;
			else $phql .= " JOIN UserOrganization AS uo1 ON uo1.organization_id=<TableName>.id AND uo1.user_id=" . $userID;
		}
		
		$phql .= " WHERE 1=1";
		
		return $phql . ' GROUP BY <TableName>.id';
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
					'value' => $row->organization->id
				],
				"name" => [
					'id' => 'name',
					'value' => $row->organization->name
				],
				"region" => [
					'value' => $row->region_name ? $row->region_name : '',
					'value_id' => $row->region_id ? $row->region_id : ''
				],
				"contacts" => [
					'id' => 'contacts',
					'value' => $row->organization->contacts
				],
				"email" => [
					'id' => 'email',
					'value' => $row->organization->email
				],
			]
		];
		$this->items[] = $item;
	}
	
	protected function addSpecificSortLimitToPhql($phql, $id) {
		//$this->logger->log(__METHOD__ . '. id2 = ' . $id);
		
		if ($id == 'region') return $phql .= ' ORDER BY Region.name ' . $this->filter_values['order'];
		return null;
	}
}
