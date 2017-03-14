<?php
class SettingController extends ControllerEntity {
	public $entityName  = 'Setting';
	
	public function initialize() {
		parent::initialize();
	}
	
	/* 
	* Заполняет (инициализирует) свойство fields
	* Переопределяемый метод.
	*/
	public function initFields() {
		$this->fields = [
			'id' => array(
				'id' => 'id',
				'name' => $this->t->_("text_entity_property_id"),
				'type' => 'label'
			), 
			'code' => array(
				'id' => 'code',
				'name' => $this->t->_("text_entity_property_code"),
				'type' => 'text'
			), 
			'value' => array(
				'id' => 'value',
				'name' => $this->t->_("text_entity_property_value"),
				'type' => 'text'
			), 
			'description' => array(
				'id' => 'description',
				'name' => $this->t->_("text_entity_property_description"),
				'type' => 'text'
			)
		];
		// наполняем поля данными
		parent::initFields();
	}
	
	/* 
	* Наполняет модель сущности из запроса при сохранении
	* Переопределяемый метод.
	*/
	protected function fillModelFieldsFromSaveRq() {
		//$this->entity->id получен ранее при select из БД или будет присвоен при создании записи в БД
		$this->entity->code = $this->fields['code']->['value'];
		$this->entity->value = $this->fields['value']['value'];
		$this->entity->description = $this->fields'[description']'[value'];
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		$this->fields["id"]["value"] = $row->id;
		$this->fields["code"]["value"] = $row->code;
		$this->fields["value"]["value"] = $row->value;
		$this->fields["description"]["value"] = $row->description;
	}
	
	/* 
	* Заполняет свойство fields данными при создании новой сущности
	* Переопределяемый метод.
	*/
	public function fillNewEntityFields() {
		// основные поля
		$this->fields["id"]["value"] = '-1';
		$this->fields["code"]["value"] = '';
		$this->fields["value"]["value"] = '';
		$this->fields["description"]["value"] = '';
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
	public function getDescriptor() {
		
		// перечень полей для фильтра (уникальные для разных таблиц)
		$filter_values = array();
		
		// запрос должен быть GET
		$filt = new Phalcon\Filter();
		if(isset($_GET["id"])) $filter_values["id"] = $filt->sanitize(urldecode($_GET["id"]), "int"); else $filter_values["id"] = '';
		
		// добавляем действия со строками, доступные пользователям с разными ролями
		$this->tools = Phalcon\DI::getDefault()->getTools();
		$operations = $this->tools->getEntityFormOperations($this->userData['role_id'], $this->entityName, $this->acl, $this->t);
		
		// вспомогательные данные
		// регионы
		$expense_type_rows = ExpenseType::find();
		$expense_types = array();
		foreach ($expense_type_rows as $row) {
			// наполняем массив
			$expense_types[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		
		// описатель формы
		$fields = array(
			'id' => array(
				'id' => 'id',
				'name' => $this->t->_("text_entity_property_id"),
				'type' => 'label'
			), 
			'code' => array(
				'id' => 'code',
				'name' => $this->t->_("text_entity_property_code"),
				'type' => 'text'
			), 
			'value' => array(
				'id' => 'value',
				'name' => $this->t->_("text_entity_property_value"),
				'type' => 'text'
			), 
			'description' => array(
				'id' => 'description',
				'name' => $this->t->_("text_entity_property_description"),
				'type' => 'text'
			)
		);
		
		// строим запрос к БД на выборку данных
		$phql = "SELECT Setting.* FROM Setting WHERE 1=1";
		// параметры фильтрации
		if(isset($filter_values["id"]) && $filter_values["id"] != "") $phql .= " AND Setting.id = '" . $filter_values["id"] . "'";
		
		// добавляем условие лимита в выборку
		$phql .= ' LIMIT 1';
		// выбираем данные с фильтром, сортировкой и лимитом
		$rows = false;
		$rows = $this->modelsManager->executeQuery($phql);
		
		// готовим ответ
				
		// основные поля
		if(count($rows)==1) {
			$fields["id"]["value"] = $rows[0]->id;
			$fields["code"]["value"] = $rows[0]->code;
			$fields["value"]["value"] = $rows[0]->value;
			$fields["description"]["value"] = $rows[0]->description;
		}
		
		$descriptor = array(
			"controllerName" => $this->controllerName,
			"entity" => $this->entityName,
			"type" => "entity",
			"fields" => $fields,
			"operations" => $operations,
			"filter_values" => $filter_values,
			"title" => $this->t->_("text_".$this->controllerName."_title")
		);

		
		// отладка
		//$this->view->setVar("dbg", $data);
		return $descriptor;
	}
}
