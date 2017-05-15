<?php
class OrganizationController extends ControllerEntity {
	public $entityName  = 'Organization';
	public $tableName  = 'organization';
	
	protected function initScrollers() {
		$this->scrollers = [
			'userlist' => [
				'linkEntityName' => 'User',
				'linkTableName' => 'UserOrganization',
				'linkTableLinkEntityFieldName' => 'user_id',
				'relationType' => 'nn',
				'controllerClass' => 'UserlistController',
				'addStyle' => 'scroller',
				'editStyle' => 'modal',
				// доп. фиьлтр для выборки данных скроллера
				'addFilter' => function() { return ["organization_id" => $this->fields["id"]["value"]]; },
			],
			'expenselist' => [
				'linkEntityName' => 'Expense',
				'linkEntityFieldName' => 'organization_id',
				'relationType' => 'n',
				'controllerClass' => 'ExpenselistController',
				'entityControllerClass' => 'ExpenseController',
				'addStyle' => 'entity',
				'editStyle' => 'modal',
				// доп. фиьлтр для выборки данных скроллера
				'addFilter' => function() { return ["organization_id" => $this->fields["id"]["value"]]; },
				'cascadeDelete' => true,
			],
			'organizationrequestlist' => [
				'linkEntityName' => 'OrganizationRequest',
				'linkEntityFieldName' => 'organization_id',
				'relationType' => 'n',
				'controllerClass' => 'OrganizationrequestlistController',
				'entityControllerClass' => 'OrganizationController',
				'addStyle' => 'scroller',
				'editStyle' => 'modal',
				// доп. фиьлтр для выборки данных скроллера
				'addFilter' => function() { return ["organization_id" => $this->fields["id"]["value"]]; },
				'cascadeDelete' => false,
			],
		];
	}
	
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
	* Удаляет ссылки на сущность ($this->entity, если не передано отдельная сущность) из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function deleteEntityLinks($entity) {
		if(!$entity) $entity = $this->entity;
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
		//$this->logger->log(__METHOD__ . '. userOrganizations deleted');
		// расходы  организации, удаляются сущнности, т.к. они создаются только для конкретной организации
		$exp = new ExpenseController();
		$expenses = Expense::find([
			"conditions" => "organization_id = ?1",
			"bind" => array(1 => $entity->id)
		]);
		foreach ($expenses as $expense) {
			//$this->logger->log(__METHOD__ . '. expense_id = ' . $expense->id);
			// удаляем расход
			$res = $exp->deleteEntity($expense);
			//$this->logger->log(__METHOD__ . '. res = ' . $res . '. error = ' . json_encode($res));
			// если в процессе удаления возникла ошибка, то транзакция уже откачена, копируем сообщения
			if(isset($res['error']) && count($res['error']['messages'])>0) {
				//$this->logger->log(__METHOD__ . '. return false');
				foreach($res['error']['messages'] as $message) {
					$this->error['messages'][] = $message;
				}
				/*foreach($res['success']['messages'] as $message) {
					$this->success['messages'][] = $message;
				}*/
				return false;
			}
			else if(isset($res['success'])) {
				// если ошибок не было, то копируем только успешные сообщения
				foreach ($res['success']['messages'] as $message) {
					$this->success['messages'][] = $message;
				}
			}
		}
		return true;
	}
}