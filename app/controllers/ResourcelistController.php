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
				'filter' => 'number',
				"sortable" => "DESC"
			),
			'group' => array(
				'id' => 'group',
				'name' => $this->controller->t->_("text_resourcelist_group"),
				'filter' => 'select',
				"sortable" => "DESC",
				'filter_style' => 'name', //'id'
				'filter_values' => ['base', 'acl'],
			),
			'controller' => array(
				'id' => 'controller',
				'name' => $this->controller->t->_("text_resourcelist_controller"),
				'filter' => 'text',
				"sortable" => "DESC"
			),
			'action' => array(
				'id' => 'action',
				'name' => $this->controller->t->_("text_resourcelist_action"),
				'filter' => 'text',
				"sortable" => "DESC"
			),
			'module' => array(
				'id' => 'module',
				'name' => $this->controller->t->_("text_resourcelist_module"),
				'filter' => 'select',
				"sortable" => "DESC",
				'filter_style' => 'name', //'id'
				'filter_values' => ['backend', 'frontend'],
			),
			'description' => array(
				'id' => 'description',
				'name' => $this->controller->t->_("text_entity_property_description"),
				'filter' => 'text',
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
		$phql = "SELECT <TableName>.* FROM <TableName>";
		
		//$this->logger->log('getPhqlSelect    this->filter_values["user_role_id"])=' . $this->filter_values["user_role_id"]);
		// расширяем выборку, если переданы доп. фильтры (для связей 1-n, n-1, n-n), которые могут навязывать внешние контроллеры
		if(isset($this->add_filter["user_role_id"]) && $this->add_filter["user_role_id"] != "") {
			$phql .= " JOIN UserRoleResource AS urr ON urr.resource_id=<TableName>.id AND urr.user_role_id=" . $this->add_filter["user_role_id"];
		}
		//$this->logger->log(__METHOD__ . ". add_filter=" . json_encode($this->add_filter));
		
		$phql .= " WHERE 1=1";
		
		return $phql;
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
					'value' => isset($row->resource->id) ? $row->resource->id : $row->id,
				),
				"group" => array(
					'id' => 'group',
					'value' => isset($row->resource->group) ? $row->resource->group : $row->group,
				),
				"controller" => array(
					'id' => 'controller',
					'value' => isset($row->resource->controller) ? $row->resource->controller : $row->controller,
				),
				"action" => array(
					'id' => 'action',
					'value' => isset($row->resource->action) ? $row->resource->action : $row->action,
				),
				"module" => array(
					'id' => 'module',
					'value' => isset($row->resource->module) ? $row->resource->module : $row->module,
				),
				"description" => array(
					'id' => 'description',
					'value' => isset($row->resource) ? $row->resource->description : $row->description,
				)
			)
		);
	}
}
