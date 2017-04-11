<?php
class OrganizationController extends ControllerEntity {
	public $entityName  = 'Organization';
	public $tableName  = 'Organization';
	
	protected $scrollers = [
		'userlist' => [
			'linkEntityName' => 'User',
			'linkTableName' => 'UserOrganization',
			'linkTableLinkEntityFieldName' => 'user_id',
			'relationType' => 'nn'
		],
		'expenselist' => [
			'linkEntityName' => 'Expense',
			'linkEntityFieldName' => 'organization_id',
			'relationType' => 'n'
		]
	];
	
	public function initialize() {
		parent::initialize();
	}
	
	/* 
	* Заполняет (инициализирует) свойство fields
	* Переопределяемый метод.
	*/
	protected function initFields() {
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
			'region' =>	array(
				'id' => 'region',
				'name' => $this->t->_("text_organization_region"),
				'type' => 'select',
				'style' => 'id', //name
				'linkEntityName' => 'Region',
				'required' => 2,
				'newEntityValue' => null,
			), 
			'contacts' => array(
				'id' => 'contacts',
				'name' => $this->t->_("text_entity_property_contacts"),
				'type' => 'text',
				'newEntityValue' => null,
			), 
			'email' => array(
				'id' => 'email',
				'name' => $this->t->_("text_entity_property_email"),
				'type' => 'email',
				'newEntityValue' => null,
			), 
			'img' => array(
				'id' => 'img',
				'name' => $this->t->_("text_entity_property_image"),
				'type' => 'img',
				'max_count' => 2,
				'min_count' => 0,
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
		if(isset($this->fields['name']['value'])) $this->entity->name = $this->fields['name']['value'];
		if(isset($this->fields['contacts']['value'])) $this->entity->contacts = $this->fields['contacts']['value'];
		if(isset($this->fields['email']['value'])) $this->entity->email = $this->fields['email']['value'];
		if(isset($this->fields['region']['value_id'])) $this->entity->region_id = $this->fields['region']['value_id'];
	}
	
	/* 
	* Предоставляет текст запроса к БД
	* Переопределяемый метод.
	*/
	protected function getPhql() {
		// строим запрос к БД на выборку данных
		return "SELECT Organization.*, Region.id AS region_id, Region.name AS region_name FROM Organization JOIN Region on Region.id=Organization.region_id WHERE Organization.id = '" . $this->filter_values["id"] . "' LIMIT 1";
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	protected function fillFieldsFromRow($row) {
		// TODO. Надо вынести заполнение в базовый класс, зпаолнять на основании свойст полей из $this->fields
		$this->fields["id"]["value"] = $row->organization->id;
		$this->fields["name"]["value"] = $row->organization->name;
		$this->fields["region"]["value"] = $row->region_name;
		$this->fields["region"]["value_id"] = $row->region_id;
		$this->fields["contacts"]["value"] = $row->organization->contacts;
		$this->fields["email"]["value"] = $row->organization->email;
		//$this->fields["img"]["value"] = $row->organization->img; // файлы заполняются отдельно
	}
	
	/* 
	* Заполняет свойство scrollers данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function fillScrollers() {
		$this->logger->log(__METHOD__ . ". actionName1: " . json_encode($this->actionName));
		$role_id = $this->userData['role_id'];
		// TODO. Надо вынести заполнение в базовый класс в  метод initFields, зпаолнять на основании свойст полей из $this->fields
		//$this->logger->log('fields: ' . json_encode($this->fields));
		// грид расходов
		// если имеется доступ к скроллеру
		$action = ($this->acl->isAllowed($role_id, "organization_expenselist", 'edit') ? 'edit' : ($this->acl->isAllowed($role_id, "organization_expenselist", 'show') ? 'show' : null));
		if($action) {
			$controller_expense_list = new ExpenselistController();
			$scroller_expense_list = $controller_expense_list->createDescriptor($this, array("organization_id" => $this->fields["id"]["value"]), $action);
			$scroller_expense_list['relationType'] = $this->scrollers[$controller_expense_list->controllerName]['relationType'];
			$scroller_expense_list["add_style"] = "entity";
			$scroller_expense_list["edit_style"]  = "modal";
			
			$this->scrollers[$controller_expense_list->controllerNameLC] = $scroller_expense_list;
		}
		else unset($this->scrollers['expenselist']);
		
		// грид пользователей
		// если имеется доступ к скроллеру
		$action = ($this->acl->isAllowed($role_id, "organization_userlist", 'edit') ? 'edit' : ($this->acl->isAllowed($role_id, "organization_userlist", 'show') ? 'show' : null));
		if($action) {
			$controller_user_list = new UserlistController();
			$scroller_user_list = $controller_user_list->createDescriptor($this, array("organization_id" => $this->fields["id"]["value"]), $action);
			$scroller_user_list['relationType'] = $this->scrollers[$controller_user_list->controllerName]['relationType'];
			$scroller_user_list["add_style"] = "scroller";
			$scroller_user_list['edit_style']  = "modal";
			
			$this->scrollers[$controller_user_list->controllerNameLC] = $scroller_user_list;
		}
		else unset($this->scrollers['userlist']);
		
		$this->logger->log(__METHOD__ . ". actionName2: " . json_encode($this->actionName));
	}
	
	/* 
	* Очищает параметры запроса
	* Расширяемый метод.
	*/
	/*protected function sanitizeSaveRqData($rq) {
		$res = 0;
		// id, //select, link
		if(!parent::sanitizeSaveRqData($rq)) return false;
		//if(!$this->sanitizeSaveRqData2($rq)) return false;
		
		//$this->error['messages'][] = ['title' => "Debug. " . __METHOD__, 'msg' => "id=" . $this->fields['id']['value']];
		
		// name
		$this->fields['name']['value'] = null;
		if(isset($rq->fields->name) && isset($rq->fields->name->value)) {
			$this->fields['name']['value'] = $this->filter->sanitize(urldecode($rq->fields->name->value), ["trim", "string"]);
			if($this->fields['name']['value'] == '') $this->fields['name']['value'] = null;
		}
		
		// email
		$this->fields['email']['value'] = null;
		if(isset($rq->fields->email) && isset($rq->fields->email->value)) {
			$this->fields['email']['value'] = $this->filter->sanitize(urldecode($rq->fields->email->value), ["trim", "string"]);
			if($this->fields['email']['value'] == '') $this->fields['email']['value'] = null;
		}
		
		// contacts
		$this->fields['contacts']['value'] = null;
		if(isset($rq->fields->contacts) && isset($rq->fields->contacts->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->contacts->value), ["trim", "string"]);
			if($this->fields['contacts']['value'] == '') $this->fields['contacts']['value'] = null;
		}
		
		// phone
		$this->fields['phone']['value'] = null;
		if(isset($rq->fields->phone) && isset($rq->fields->phone->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->phone->value), ["trim", "string"]);
			if($this->fields['phone']['value'] == '') $this->fields['phone']['value'] = null;
		}
		
		$res |= $this->check();
		
		if($res != 0) return false;
		
		// userlist, expenselist
		return $this->sanitizeSaveRqDataCheckRelations($rq);
	}*/
	
	protected function sanitizeSaveRqData($rq) {
		$res = 0;
		// id, //select, link
		$res |= parent::sanitizeSaveRqData($rq);
		
		//$this->error['messages'][] = ['title' => "Debug. " . __METHOD__, 'msg' => "id=" . $this->fields['id']['value']];
		
		// name
		$this->fields['name']['value'] = null;
		if(isset($rq->fields->name) && isset($rq->fields->name->value)) {
			$this->fields['name']['value'] = $this->filter->sanitize(urldecode($rq->fields->name->value), ["trim", "string"]);
			if($this->fields['name']['value'] == '') $this->fields['name']['value'] = null;
		}
		
		// email
		$this->fields['email']['value'] = null;
		if(isset($rq->fields->email) && isset($rq->fields->email->value)) {
			$this->fields['email']['value'] = $this->filter->sanitize(urldecode($rq->fields->email->value), ["trim", "string"]);
			if($this->fields['email']['value'] == '') $this->fields['email']['value'] = null;
		}
		
		// contacts
		$this->fields['contacts']['value'] = null;
		if(isset($rq->fields->contacts) && isset($rq->fields->contacts->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->contacts->value), ["trim", "string"]);
			if($this->fields['contacts']['value'] == '') $this->fields['contacts']['value'] = null;
		}
		
		// phone
		$this->fields['phone']['value'] = null;
		if(isset($rq->fields->phone) && isset($rq->fields->phone->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->phone->value), ["trim", "string"]);
			if($this->fields['phone']['value'] == '') $this->fields['phone']['value'] = null;
		}
		
		// userlist, expenselist
		if(!$this->sanitizeSaveRqDataCheckRelations($rq)) $res |= 2;
		
		$res |= $this->check();
		
		return $res;
	}
	
	/* 
	* Удаляет ссылки на сущность ($this->entity, если не передано отдельная сущность) из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function deleteEntityLinks($entity) {
		if(!isset($entity)) $entity = $this->entity;
		// пользователи организации, удаляются только связи
		$userOrganizations = UserOrganization::find([
			"conditions" => "organization_id = ?1",
			"bind" => array(1 => $entity->id)
		]);
		foreach($userOrganizations as $userOrganization) {
			if ($userOrganization->delete() == false) {
				$this->db->rollback();
				$dbMessages = '';
				foreach ($n->getMessages() as $message) {
					$dbMessages .= "<li>" . $message . "</li>";
				}
				$this->error['messages'][] = [
					'title' => "Не удалось удалить связь с пользователем id=" . $userOrganization->user_id,
					'msg' => "<ul>" . $dbMessages . "</ul>"
				];
				return false;
			}
		}
		// расходы  организации, удаляются сущнности, т.к. они создаются только для конкретной организации
		$exp = new ExpenseController();
		$expenses = Expense::find([
			"conditions" => "organization_id = ?1",
			"bind" => array(1 => $this->entity->id)
		]);
		foreach($expenses as $expense) {
			// удаляем расход
			$res = $exp->deleteEntity($expense);
			// если в процессе удаления возникла ошибка, то транзакция уже откачена, копируем сообщения
			if($res && count($res['error']['messages']>0)) {
				foreach($res['error']['messages'] as $message) {
					$this->error['messages'][] = $message;
				}
				foreach($res['success']['messages'] as $message) {
					$this->success['messages'][] = $message;
				}
				return false;
			}
			// если ошибок не было, то копируем только успешные сообщения
			foreach($res['success']['messages'] as $message) {
				$this->success['messages'][] = $message;
			}
			/*if ($expense->delete() == false) {
				$this->db->rollback();
				$dbMessages = '';
				foreach ($n->getMessages() as $message) {
					$dbMessages .= "<li>" . $message . "</li>";
				}
				$this->error['messages'][] = [
					'title' => "Не удалось удалить расход id=" . $expense->id,
					'msg' => "<ul>" . $dbMessages . "</ul>"
				];
				return false;
			}*/
		}
		return true;
	}
}