<?php
class NewsListController extends ControllerList {
	public $entityName = 'News';
	public $controllerName = "NewsList";
	
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
			'name' => array(
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_name"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['name']) ? $this->filter_values['name'] : '',
				"sortable" => "DESC"
			),
			'description' => array(
				'id' => 'description',
				'name' => $this->controller->t->_("text_entity_property_description"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['amount']) ? $this->filter_values['amount'] : '',
			),
			'publication_date' => array(
				'id' => 'publication_date',
				'name' => $this->controller->t->_("text_newslist_publication_date"),
				'filter' => 'text',
				'filter_value' => isset($this->filter_values['publication_date']) ? $this->filter_values['publication_date'] : '',
				"sortable" => "DESC"
			),
			'created_by' => array(
				'id' => 'created_by',
				'name' => $this->controller->t->_("text_entity_property_created_by"),
				'filter' => 'select',
				'filter_value' => isset($this->filter_values['created_by']) ? $this->filter_values['created_by'] : '',
				'filter_id' => 'created_by_id', // задается, если отличается от id
				'style' => 'id',
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
		// типы расходов для фильтрации
		$user_rows = User::find();
		$users = array();
		foreach ($user_rows as $row) {
			// наполняем массив
			$users[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->columns['created_by']['filter_values'] = $users;
	}
	
	/* 
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {
		$userRoleID = $this->controller->userData['role_id'];
		
		// строим запрос к БД на выборку данных
		$phql = "SELECT <TableName>.*, User.id AS created_by_id, User.name AS created_by_name FROM <TableName> JOIN User on User.id=<TableName>.created_by";
		$phql .= " WHERE 1=1";
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
					'value' => $row->news->id,
				),
				"name" => array(
					'id' => 'name',
					'value' =>  $row->news->name,
				),
				"description" => array(
					'id' => 'description',
					'value' => $row->news->description ? $row->news->description : '',
				),
				"publication_date" => array(
					'id' => 'publication_date',
					'value' =>  $row->news->publication_date,
				),
				"created_by" => array(
					'value_id' => $row->created_by_id ? $row->created_by_id : '',
					'value' => $row->created_by_name ? $row->created_by_name : '',
				)
			)
		);
	}
}