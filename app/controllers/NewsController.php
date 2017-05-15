<?php
class NewsController extends ControllerEntity {
	public $entityName  = 'News';
	
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
				'type' => 'label',
				'newEntityValue' => '-1',
			), 
			'name' => array(
				'id' => 'name',
				'name' => $this->t->_("text_entity_property_name"),
				'type' => 'text',
				'required' => 2,
				'newEntityValue' => null,
			), 
			'description' => array(
				'id' => 'description',
				'name' => $this->t->_("text_entity_property_description"),
				'type' => 'textarea',
				'required' => 2,
				'newEntityValue' => null,
				//'newEntityValue2' => function() { return $this->userData["id"]; },
				//'newEntityValue2' => null,
			), 
			'publication_date' => array(
				'id' => 'publication_date',
				'name' => $this->t->_("text_news_publication_date"),
				'type' => 'date',
				'required' => 2,
				'newEntityValue' => (new DateTime('now'))->format("Y-m-d"),
			), 
			'created_by' => array(
				'id' => 'created_by',
				'name' => $this->t->_("text_entity_property_created_by"),
				'type' => 'select',
				'style' => 'id', //name
				//'values' => $expense_types
				'linkEntityName' => 'User',
				'linkEntityDataField' => 'name',
				'required' => 2,
				//'newEntityValue' => null,
				'newEntityValue' => function() { return $this->userData["id"]; },
			)
		];
		// наполняем поля данными
		parent::initFields();
	}
	
	protected function fillNewEntityFields() {
		parent::fillNewEntityFields();
		/*if(isset($this->fields["description"]["newEntityValue2"])) {
			if(is_object($this->fields["description"]["newEntityValue2"])) $this->fields["description"]["value"] = $this->fields["description"]["newEntityValue2"]();
			else $this->fields["description"]["value"] = $this->fields["description"]["newEntityValue2"];
		}*/
		//$this->fields["description"]["value"] = is_object($this->fields["description"]["newEntityValue2"]) ? $this->fields["description"]["newEntityValue2"]() : $this->fields["description"]["newEntityValue2"];
	}
	
	/* 
	* Наполняет модель сущности из запроса при сохранении
	* Переопределяемый метод.
	*/
	protected function fillModelFieldsFromSaveRq() {
		//$this->entity->id получен ранее при select из БД или будет присвоен при создании записи в БД
		if(isset($this->fields['name']['access']) && $this->fields['name']['access'] == 'edit') $this->entity->name = $this->fields['name']['value'];
		if(isset($this->fields['description']['access']) && $this->fields['description']['access'] == 'edit') $this->entity->description = $this->fields['description']['value'];
		if(isset($this->fields['publication_date']['access']) && $this->fields['publication_date']['access'] == 'edit') $this->entity->publication_date = $this->fields['publication_date']['value'];
		if(isset($this->fields['created_by']['access']) && $this->fields['created_by']['access'] == 'edit') $this->entity->created_by = $this->fields['created_by']['value_id'];
	}
	
	/* 
	* Предоставляет текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhql() {
		// строим запрос к БД на выборку данных
		return "SELECT News.*, User.id AS created_by_id, User.name AS created_by_name FROM News JOIN User on User.id=News.created_by WHERE News.id = '" . $this->filter_values["id"] . "' LIMIT 1";
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		//$this->logger->log(json_encode($row));
		$this->fields["id"]["value"] = $row->news->id;
		$this->fields["name"]["value"] = $row->news->name;
		$this->fields["description"]["value"] = $row->news->description;
		$this->fields["publication_date"]["value"] = $row->news->publication_date;
		$this->fields["created_by"]["value"] = $row->created_by_name;
		$this->fields["created_by"]["value_id"] = $row->created_by_id;
	}
}