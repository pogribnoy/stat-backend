<?php
class ResourcelistController extends ControllerList {
	public $entityName = 'Resource';
	public $controllerName = "Resourcelist";
	
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
			'group' => array(
				'id' => 'group',
				'name' => $this->controller->t->_("text_resourcelist_group"),
				'type' => 'text',
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['group']) ? $this->filter_values['group'] : 'acl',
				"sortable" => "DESC",
				'style' => 'name', //'id'
			),
			'controller' => array(
				'id' => 'controller',
				'name' => $this->controller->t->_("text_resourcelist_controller"),
				'type' => 'text',
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['controller']) ? $this->filter_values['controller'] : '',
				"sortable" => "DESC"
			),
			'action' => array(
				'id' => 'action',
				'name' => $this->controller->t->_("text_resourcelist_action"),
				'type' => 'text',
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['action']) ? $this->filter_values['action'] : '',
				"sortable" => "DESC"
			),
			'module' => array(
				'id' => 'module',
				'name' => $this->controller->t->_("text_resourcelist_module"),
				'type' => 'text',
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['module']) ? $this->filter_values['module'] : 'backend',
				"sortable" => "DESC",
				'style' => 'name', //'id'
			),
			'description' => array(
				'id' => 'description',
				'name' => $this->controller->t->_("text_entity_property_description"),
				'type' => 'text',
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['description']) ? $this->filter_values['description'] : '',
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
		$this->columns['group']['filter_values'] = ['base', 'acl'];
		$this->columns['module']['filter_values'] = ['backend', 'frontend'];
	}
	
	/* 
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.*{user_role_resource_columns} FROM <TableName>";
		
		//$this->logger->log('getPhqlSelect    this->filter_values["user_role_id"])=' . $this->filter_values["user_role_id"]);
		// расширяем выборку, если переданы доп. фильтры (для связей 1-n, n-1, n-n), которые могут навязывать внешние контроллеры
		if(isset($this->add_filter["user_role_id"]) && $this->add_filter["user_role_id"] != "") {
			$phql = str_replace("{user_role_resource_columns}", ", UserRoleResource.*", $phql);
			$phql .= " JOIN UserRoleResource on UserRoleResource.resource_id=<TableName>.id AND UserRoleResource.user_role_id=" . $this->add_filter["user_role_id"];
		}
		else $phql = str_replace("{user_role_resource_columns}", "", $phql);
		
		return $phql . " WHERE 1=1";
	}
	
	/* 
	* Заполняет свойство items['fields'] данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		//$this->logger->log(__METHOD__ . 'row=' . json_encode($row));
		$this->items[] = array(
			"fields" => array(
				"id" => array(
					'id' => 'id',
					'value' => isset($row->id) ? $row->id : $row->resource->id,
				),
				"group" => array(
					'id' => 'group',
					'value' => isset($row->group) ? $row->group : $row->resource->group,
				),
				"controller" => array(
					'id' => 'controller',
					'value' => isset($row->controller) ? $row->controller : $row->resource->controller,
				),
				"action" => array(
					'id' => 'action',
					'value' => isset($row->action) ? $row->action : $row->resource->action,
				),
				"module" => array(
					'id' => 'module',
					'value' => isset($row->module) ? $row->module : $row->resource->module,
				),
				"description" => array(
					'id' => 'description',
					'value' => isset($row->description) ? $row->description : $row->resource->description,
				)
			)
		);
	}
}
