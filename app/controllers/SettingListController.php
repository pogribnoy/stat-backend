<?php
class SettingListController extends ControllerList {
	public $entityName = 'Setting';
	public $controllerName = "SettingList";
	
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
			'code' => array(
				'id' => 'code',
				'name' => $this->controller->t->_("text_entity_property_code"),
				'type' => 'text',
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['code']) ? $this->filter_values['code'] : '',
				"sortable" => "DESC"
			),
			'value' => array(
				'id' => 'value',
				'name' => $this->controller->t->_("text_entity_property_value"),
				'type' => 'text',
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['value']) ? $this->filter_values['value'] : '',
				"sortable" => "DESC"
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
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {
		// строим запрос к БД на выборку данных
		return "SELECT <TableName>.* FROM <TableName> WHERE 1=1";
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
				"code" => array(
					'id' => 'code',
					'value' =>  $row->code
				),
				"value" => array(
					'id' => 'value',
					'value' =>  $row->value
				),
				"description" => array(
					'id' => 'description',
					'value' =>  $row->description
				)
			)
		);
	}
}
