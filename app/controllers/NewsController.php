<?php
class NewsController extends ControllerEntity {
	public $entityName  = 'news';
	
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
				'required' => 1,
				'newEntityValue' => null,
			), 
			'description' => array(
				'id' => 'description',
				'name' => $this->t->_("text_entity_property_description"),
				'type' => 'textarea',
				'required' => 1,
				'newEntityValue' => null,
			), 
			'publication_date' => array(
				'id' => 'publication_date',
				'name' => $this->t->_("text_news_publication_date"),
				'type' => 'date',
				'required' => 1,
				'newEntityValue' => (new DateTime('now'))->format("Y-m-d"),
			), 
			'created_by' => array(
				'id' => 'created_by',
				'name' => $this->t->_("text_entity_property_created_by"),
				'type' => 'select',
				'style' => 'id', //name
				//'values' => $expense_types
				'linkEntityName' => 'User',
				'required' => 1,
				'newEntityValue' => null,
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
		$this->entity->name = $this->fields['name']['value'];
		$this->entity->description = $this->fields['description']['value'];
		$this->entity->publication_date = $this->fields['publication_date']['value'];
		$this->entity->created_by = $this->fields['created_by']['value_id'];
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
		
	/* 
	* Очищает параметры запроса
	* Расширяемый метод.
	*/
	protected function sanitizeSaveRqData($rq) {
		// id, select, link
		if(!parent::sanitizeSaveRqData($rq)) return false;
		// name
		if(isset($rq->fields->name) && isset($rq->fields->name->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->name->value), ["trim", "string"]);
			if($val != '') $this->fields['name']['value'] = $val;
			else {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => 'Поле "'. $this->fields['name']['name'] .'" обязательно для указания'
				];
				return false;
			}
		}
		else return false;
		//publication_date
		if(isset($rq->fields->publication_date) && isset($rq->fields->publication_date->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->publication_date->value), ["trim", "string"]);
			if($val != '') $this->fields['publication_date']['value'] = $val;
			else {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => 'Поле "'. $this->fields['publication_date']['name'] .'" обязательно для указания'
				];
				return false;
			}
			//$this->logger->log('val = ' . $val);
		}
		else return false;
		//description
		if(isset($rq->fields->description) && isset($rq->fields->description->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->description->value), ["trim", "string"]);
			if($val != '') $this->fields['description']['value'] = $val;
			else {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => 'Поле "'. $this->fields['description']['name'] .'" обязательно для указания'
				];
				return false;
			}
			//$this->logger->log('val = ' . $this->fields['description']['value']);
		}
		else return false;
		
		return true;
	}
}