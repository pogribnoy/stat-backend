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
		$this->columns = array(
			'id' => array(
				'id' => 'id',
				'name' => $this->controller->t->_("text_entity_property_id"),
				'filter' => 'number',
				'filter_value' => isset($this->filter_values['id']) ? $this->filter_values['id'] : '',
				"sortable" => "DESC"
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_name"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['name']) ? $this->filter_values['name'] : '',
				"sortable" => "DESC"
			),
			'region' => array(
				'id' => 'region',
				'name' => $this->controller->t->_("text_organizationlist_region"),
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['region']) ? $this->filter_values['region'] : '',
				'style' => 'id',
				"sortable" => "DESC"
			),
			'contacts' => array(
				'id' => 'contacts',
				'name' => $this->controller->t->_("text_entity_property_contacts"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['contacts']) ? $this->filter_values['contacts'] : ''
			),
			'email' => array(
				'id' => 'email',
				'name' => $this->controller->t->_("text_entity_property_email"),
				'filter' => 'email',
				'filter_value' => isset($this->filter_values['email']) ? $this->filter_values['email'] : '',
				"sortable" => "DESC"
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
		
		// строим запрос к БД на выборку данных
		//$phql = "SELECT <TableName>.*, Region.id AS region_id, Region.name AS region_name FROM <TableName> JOIN Region on Region.id=<TableName>.region_id";
		$phql = "SELECT <TableName>.*, Region.id AS region_id, Region.name AS region_name FROM <TableName> LEFT JOIN Region on Region.id=<TableName>.region_id";
		
		// проверяем, чтобы пользователь имел доступ к организации, если он не админ и добавляем доп фильтр на идентификатор пользвателя
		if($userRoleID != 1 || isset($this->add_filter["user_id"])) {
			$phql .= ' INNER JOIN UserOrganization ON UserOrganization.organization_id = <TableName>.id AND';
			if($userRoleID != 1) {
				$phql .= ' (UserOrganization.user_id = ' . $this->controller->userData['id'];
				if(isset($this->add_filter["user_id"])) $phql .= ' OR UserOrganization.user_id=' . $this->add_filter["user_id"];
				else $phql .= ')';
			}
			else $phql .= ' UserOrganization.user_id=' . $this->add_filter["user_id"];
		}
		
		return $phql . " WHERE 1=1";
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
					'value' => $row->organization->id
				),
				"name" => array(
					'id' => 'name',
					'value' => $row->organization->name
				),
				"region" => array(
					'value' => $row->region_name ? $row->region_name : '',
					'value_id' => $row->region_id ? $row->region_id : ''
				),
				"contacts" => array(
					'id' => 'contacts',
					'value' => $row->organization->contacts
				),
				"email" => array(
					'id' => 'email',
					'value' => $row->organization->email
				)
			)
		);
	}
}
