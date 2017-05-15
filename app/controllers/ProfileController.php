<?php
class ProfileController extends ControllerEntity{
	public $entityName  = 'User';
	public $tableName  = 'user';
	
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
			'active' => array(
				'id' => 'active',
				'name' => $this->t->_("text_entity_property_active"),
				'type' => 'bool',
				'newEntityValue' => 1,
			), 
			'email' => array(
				'id' => 'email',
				'name' => $this->t->_("text_entity_property_email"),
				'type' => 'email',
				'newEntityValue' => null,
			), 
			'user_role' => array(
				'id' => 'user_role',
				'name' => $this->t->_("text_entity_property_role"),
				//'type' => 'select',
				//'style' => 'id' //name
				'type' => 'link',
				'controllerName' => 'UserRoleList',
				'field' => 'name',
				'linkEntityName' => 'UserRole',
				'required' => 1,
				'newEntityValue' => null,
			),
			'name' => array(
				'id' => 'name',
				'name' => $this->t->_("text_entity_property_fio"),
				'type' => 'text',
				'required' => 1,
				'newEntityValue' => null,
			), 
			'password' => array(
				'id' => 'password',
				'name' => $this->t->_("text_entity_property_password"),
				'type' => 'password',
				'newEntityValue' => null,
			),
			'phone' => array(
				'id' => 'phone',
				'name' => $this->t->_("text_entity_property_phone"),
				'type' => 'text',
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
		$this->entity->phone = $this->fields['phone']['value'];
		if(isset($this->fields['password']['value']) && $this->fields['password']['value'] != null) $this->entity->password = $this->fields['password']['value'];
	}
	
	/* 
	* Предоставляет текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhql() {
		//$this->logger->log(__METHOD__ . ". Пользователь id=" . $this->userData["id"]);
		// строим запрос к БД на выборку данных
		return "SELECT User.*, UserRole.id AS user_role_id, UserRole.name AS user_role_name FROM User JOIN UserRole on UserRole.id=User.user_role_id WHERE User.id = '" . $this->userData["id"] . "'  LIMIT 1";
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {
		$this->fields["id"]["value"] = $row->user->id;
		$this->fields["active"]["value"] = $row->user->active;
		$this->fields["name"]["value"] = $row->user->name;
		$this->fields["user_role"]["value_id"] = $row->user_role_id;
		$this->fields["user_role"]["value"] = $row->user_role_name;
		$this->fields["phone"]["value"] = $row->user->phone;
		$this->fields["email"]["value"] = $row->user->email;
	}
	
	/* 
	* Заполняет свойство fields данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	public function fillFieldsWithLists() {
		// роли
		/*$user_role_rows = UserRole::find();
		$user_roles = array();
		foreach ($user_role_rows as $row) {
			// наполняем массив
			$user_roles[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->fields['user_role']['values'] = $user_roles;*/
	}
}