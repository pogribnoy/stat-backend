<?php
class ExpenseController extends ControllerEntity {
	public $entityName  = 'expense';
	
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
			'expense_type' => array(
				'id' => 'expense_type',
				'name' => $this->t->_("text_expense_expense_type"),
				'type' => 'select',
				'style' => 'id', //name
				//'values' => $expense_types
				'linkEntityName' => 'ExpenseType',
				'required' => 1,
				'newEntityValue' => null,
			),
			'expense_status' => array(
				'id' => 'expense_status',
				'name' => $this->t->_("text_entity_property_status"),
				'type' => 'select',
				'style' => 'id', //name
				//'values' => $expense_types
				'linkEntityName' => 'ExpenseStatus',
				'required' => 1,
				'newEntityValue' => null,
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->t->_("text_entity_property_name"),
				'type' => 'text',
				'required' => 1,
				'newEntityValue' => null,
			), 
			'amount' => array(
				'id' => 'amount',
				'name' => $this->t->_("text_entity_property_amount"),
				'type' => 'amount',
				'required' => 1,
				'min' => 200,
				'max' => 9900,
				'newEntityValue' => null,
			), 
			'date' => array(
				'id' => 'date',
				'name' => $this->t->_("text_entity_property_date"),
				'type' => 'date',
				'required' => 1,
				'newEntityValue' => (new DateTime('now'))->format("Y-m-d"),
			),
			'street_type' =>	array(
				'id' => 'street_type',
				'name' => $this->t->_("text_entity_property_street_type"),
				'type' => 'select',
				'style' => 'id', //name
				'linkEntityName' => 'streettype',
				//'required' => 1,
				'nullSubstitute' => '-',
				'newEntityValue' => null,
			),
			'street' =>	array(
				'id' => 'street',
				'name' => $this->t->_("text_entity_property_street"),
				'type' => 'text',
				//'required' => 1,
				'newEntityValue' => null,
			),
			'house' =>	array(
				'id' => 'house',
				'name' => $this->t->_("text_entity_property_house_building"),
				'type' => 'text',
				'newEntityValue' => null,
			),
			'executor' =>	array(
				'id' => 'executor',
				'name' => $this->t->_("text_entity_property_executor"),
				'type' => 'text',
				'newEntityValue' => null,
			),
			'target_date' =>	array(
				'id' => 'target_date',
				'name' => $this->t->_("text_expense_target_date"),
				'name1' => $this->t->_("text_entity_property_period_from"),
				'name2' => $this->t->_("text_entity_property_period_to"),
				'type' => 'period',
				'newEntityValue1' => null,
				'newEntityValue2' => (new DateTime('now'))->format("Y-m-d"),
			),
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
		$this->entity->expense_type_id = $this->fields['expense_type']['value_id'];
		$this->entity->expense_status_id = $this->fields['expense_status']['value_id'];
		$this->entity->name = $this->fields['name']['value'];
		$this->entity->date = $this->fields['date']['value'];
		$this->entity->amount = $this->fields['amount']['value'];
		$this->entity->street_type_id = $this->fields['street_type']['value_id'];
		$this->entity->street = $this->fields['street']['value'];
		$this->entity->house = $this->fields['house']['value'];
		$this->entity->executor = $this->fields['executor']['value'];
		$this->entity->target_date_from = $this->fields['target_date']['value1'];
		$this->entity->target_date_to = $this->fields['target_date']['value2'];
	}
	
	/* 
	* Предоставляет текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhql() {
		// строим запрос к БД на выборку данных
		return "SELECT Expense.*, ExpenseType.id AS expense_type_id, ExpenseType.name AS expense_type_name, StreetType.id AS street_type_id, StreetType.name AS street_type_name, ExpenseStatus.id AS expense_status_id, ExpenseStatus.name AS expense_status_name FROM Expense JOIN ExpenseType ON ExpenseType.id=Expense.expense_type_id LEFT JOIN ExpenseStatus ON ExpenseStatus.id=Expense.expense_status_id LEFT JOIN StreetType ON StreetType.id=Expense.street_type_id WHERE Expense.id = '" . $this->filter_values["id"] . "' LIMIT 1";
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		//$this->logger->log(json_encode($row));
		$this->fields["expense_type"]["value"] = $row->expense_type_name;
		$this->fields["expense_type"]["value_id"] = $row->expense_type_id;
		$this->fields["expense_status"]["value"] = $row->expense_status_name;
		$this->fields["expense_status"]["value_id"] = $row->expense_status_id;
		$this->fields["id"]["value"] = $row->expense->id;
		$this->fields["name"]["value"] = $row->expense->name;
		$this->fields["date"]["value"] = $row->expense->date;
		$this->fields["amount"]["value"] = $row->expense->amount!=null ? number_format($row->expense->amount / 100, 2, '.', '') : '';
		$this->fields["street_type"]["value"] = $row->street_type_name;
		$this->fields["street_type"]["value_id"] = $row->street_type_id;
		$this->fields["street"]["value"] = $row->expense->street;
		$this->fields["house"]["value"] = $row->expense->house;
		$this->fields["executor"]["value"] = $row->expense->executor;
		$this->fields["target_date"]["value1"] = $row->expense->target_date_from;
		$this->fields["target_date"]["value2"] = $row->expense->target_date_to;
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
					'msg' => 'Поле "'. $this->fields['name']['name'] .'" обязательно для указания',
				];
				return false;
			}
		}
		else return false;
		//date
		if(isset($rq->fields->date) && isset($rq->fields->date->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->date->value), ["trim", "string"]);
			if($val != '') $this->fields['date']['value'] = $val;
			else {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => 'Поле "'. $this->fields['date']['name'] .'" обязательно для указания',
				];
				return false;
			}
			//$this->logger->log('val = ' . $val);
		}
		else return false;
		//amount
		if(isset($rq->fields->amount) && isset($rq->fields->amount->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->amount->value), ["trim", "string"]);
			//$this->logger->log('1val = ' . $val);
			$val = str_replace([",", "-"], ".", $val);
			//$this->logger->log('2val = ' . $val);
			if($val != '') {
				$val = 100 * $val;
				if(isset($this->fields['amount']['min']) && $val < (int)$this->fields['amount']['min']) {
					$this->error['messages'][] = [
						'title' => "Ошибка",
						'msg' => 'Поле "'. $this->fields['amount']['name'] .'" содержит значение меньше допустимого',
					];
					return false;
				}
				if(isset($this->fields['amount']['max']) && $val > (int)$this->fields['amount']['max']) {
					$this->error['messages'][] = [
						'title' => "Ошибка",
						'msg' => 'Поле "'. $this->fields['amount']['name'] .'" содержит значение больше допустимого',
					];
					return false;
				}
				//$this->logger->log('3val = ' . $val);
				$this->fields['amount']['value'] = $val;
			}
			else if($val == '' && isset($this->fields['amount']['required'])) {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => 'Поле "'. $this->fields['amount']['name'] .'" обязательно для указания',
				];
				return false;
			}
			else {
				$this->fields['amount']['value'] = null;
			}
		}
		else {
			$this->error['messages'][] = [
				'title' => "Ошибка",
				'msg' => 'В поле "'. $this->fields['amount']['name'] .'" обязательно для указания',
			];
			return false;
		}
		
		//street
		if(isset($rq->fields->street) && isset($rq->fields->street->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->street->value), ["trim", "string"]);
			if($val != '') $this->fields['street']['value'] = $val;
			else $this->fields['street']['value'] = null;
			//$this->logger->log('val = ' . $this->fields['street']['value']);
		}
		else $this->fields['street']['value'] = null;
		
		//house
		if(isset($rq->fields->house) && isset($rq->fields->house->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->house->value), ["trim", "string"]);
			if($val == '') $this->fields['house']['value'] = null;
			else $this->fields['house']['value'] = $val;
			//$this->logger->log('val = ' . $this->fields['house']['value']);
		}
		else $this->fields['house']['value'] = null;
		
		//executor
		if(isset($rq->fields->executor) && isset($rq->fields->executor->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->executor->value), ["trim", "string"]);
			if($val == '') $this->fields['executor']['value'] = null;
			else $this->fields['executor']['value'] = $val;
			//$this->logger->log('val = ' . $this->fields['executor']['value']);
		}
		else $this->fields['executor']['value'] = null;
		
		//target_date
		if(isset($rq->fields->target_date) && isset($rq->fields->target_date->value1) && isset($rq->fields->target_date->value2)) {
			$val1 = $this->filter->sanitize(urldecode($rq->fields->target_date->value1), ["trim", "string"]);
			$val2 = $this->filter->sanitize(urldecode($rq->fields->target_date->value2), ["trim", "string"]);
			if($val1 == '') $this->fields['target_date']['value1'] = null;
			if($val2 == '') $this->fields['target_date']['value2'] = null;
			if($val1 != '' && $val2 != '') {
				$d1 = new dateTime ($val1); //(new DateTime('now'))->format("Y-m-d")
				$d2 = new dateTime ($val2);
				if($d1 > $d2) {
					$this->error['messages'][] = [
						'title' => "Ошибка",
						'msg' => 'В поле "'. $this->fields['target_date']['name'] .'" дата окончания периода не может быть меньше даты начала периода',
					];
					return false;
				}
			}
			$this->fields['target_date']['value1'] = $val1;
			$this->fields['target_date']['value2'] = $val2;
			
			//$this->logger->log('val1 = ' . $this->fields['target_date']['value1']);
			//$this->logger->log('val2 = ' . $this->fields['target_date']['value2']);
		}
		else {
			//$this->logger->log('target_date = ' . json_encode($rq->fields->target_date));
			$this->fields['target_date']['value1'] = null;
			$this->fields['target_date']['value2'] = null;
		}
		
		return true;
	}
}