<?php
class UserListController extends ControllerList {
	public $entityName = 'user';
	public $controllerName = "userlist";
	
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
				'filter_value' => isset($this->filter_values['id']) ? $this->filter_values['id'] : '',
				"sortable" => "DESC"
			),
			'active' => array(
				'id' => 'active',
				'name' => $this->controller->t->_("text_entity_property_active"),
				'filter' => 'bool',
				'filter_value' => isset($this->filter_values['active']) ? $this->filter_values['active'] : '',
				"sortable" => "DESC"
			),
			'email' => array(
				'id' => 'email',
				'name' => $this->controller->t->_("text_entity_property_email"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['email']) ? $this->filter_values['email'] : '',
				"sortable" => "DESC"
			),
			'user_role' => array(
				'id' => 'user_role',
				'name' => $this->controller->t->_("text_entity_property_role"),
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['user_role']) ? $this->filter_values['user_role'] : '',
				'style' => 'id',
				"sortable" => "DESC"
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_fio"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['name']) ? $this->filter_values['name'] : '',
				"sortable" => "DESC"
			),
			'phone' => array(
				'id' => 'phone',
				'name' => $this->controller->t->_("text_entity_property_phone"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['phone']) ? $this->filter_values['phone'] : '',
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
		// роли пользователей для фильтрации
		$user_role_rows = UserRole::find();
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
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.*, UserRole.id AS user_role_id, UserRole.name AS user_role_name{user_organization_columns} FROM <TableName> JOIN UserRole on UserRole.id=<TableName>.user_role_id";
		
		// уточняем выборку, если переданы доп. фильтры (для связей 1-n, n-1, n-n), которые могут навязывать внешние контроллеры
		if(isset($this->add_filter["organization_id"])) {
			$phql = str_replace("{user_organization_columns}", ", UserOrganization.*", $phql);
			$phql .= " JOIN UserOrganization on UserOrganization.user_id=<TableName>.id AND UserOrganization.organization_id=" . $this->add_filter["organization_id"];
		}
		else $phql = str_replace("{user_organization_columns}", "", $phql);
		
		return $phql . " WHERE 1=1";
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
		$this->items[] = array(
			"fields" => array(
				"id" => array(
					'id' => 'id',
					'value' => $row->user->id
				),
				"active" => array(
					'id' => 'active',
					'value' =>  $row->user->active
				),
				"phone" => array(
					'id' => 'phone',
					'value' =>  $row->user->phone
				),
				"email" => array(
					'id' => 'email',
					'value' =>  $row->user->email
				),
				"name" => array(
					'id' => 'name',
					'value' =>  $row->user->name
				),
				"user_role" => array(
					'id' => $row->user_role_id ? $row->user_role_id : '',
					'value' => $row->user_role_name ? $row->user_role_name : ''
				)
			)
		);
	}
}