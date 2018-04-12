<?php
class ProfileController extends ControllerEntity{
	public $entityName  = 'User';
	public $tableName  = 'user';
	
	public function passwordprintAction() {
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			$data = [
				'success' => [
					'messages' => [[
						'title' => "Операция успешна",
						'msg' => "Пароль отправлен на принтер",
					]],
				],
			];
			return json_encode($data);
		}
		else {
			$this->logger->error(__METHOD__ . '. AJAX method is only supported' . $this->request->getURI());
			$this->dispatcher->forward(array(
				'controller' => 'errors',
				'action' => 'show404',
				'sourceURL' => $this->request->getURI(),
			));
		}
	}
	
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
			'login' => array(
				'id' => 'login',
				'name' => $this->t->_("text_entity_property_login"),
				'type' => 'text',
				'min' => 1,
				'max' => 50,
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
			'email' => array(
				'id' => 'email',
				'name' => $this->t->_("text_entity_property_email"),
				'type' => 'email',
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
	
	protected function fillModelFieldsFromSaveRq() {
		//$this->entity->id получен ранее при select из БД или будет присвоен при создании записи в БД
		$this->entity->name = $this->fields['name']['value'];
		$this->entity->email = $this->fields['email']['value'];
		$this->entity->phone = $this->fields['phone']['value'];
		if(isset($this->fields['password']['value']) && $this->fields['password']['value'] != null) $this->entity->password = $this->fields['password']['value'];
	}
	
	public function getPhql() {
		//$this->logger->log(__METHOD__ . ". Пользователь id=" . $this->userData["id"]);
		// строим запрос к БД на выборку данных
		return "SELECT User.*, UserRole.id AS user_role_id, UserRole.name AS user_role_name FROM User JOIN UserRole on UserRole.id=User.user_role_id WHERE User.id = '" . $this->userData["id"] . "'  LIMIT 1";
	}
	
	public function fillFieldsFromRow($row) {
		$this->fields["id"]["value"] = $row->user->id;
		$this->fields["active"]["value"] = $row->user->active;
		$this->fields["login"]["value"] = $row->user->login;
		$this->fields["name"]["value"] = $row->user->name;
		$this->fields["user_role"]["value_id"] = $row->user_role_id;
		$this->fields["user_role"]["value"] = $row->user_role_name;
		$this->fields["phone"]["value"] = $row->user->phone;
		$this->fields["email"]["value"] = $row->user->email;
	}
	
	protected function getEntityFormOperations() {
		
		parent::getEntityFormOperations();
		
		$exludeOps = $this->exludeOps;
		$controllerNameLC = $this->controllerNameLC;
		$userRoleID = $this->userData['role_id'];
		$acl = $this->acl;
		$t = $this->t;
		
		// редактирование должно быть доступным, если доступно редактирование самой сущности или ее скроллеров
		if(!in_array('passwordprint', $exludeOps)) {
			if($acl->isAllowed($userRoleID, $controllerNameLC, 'passwordprint')) {
				$this->logger->log(__METHOD__ . ". test2");
				$this->operations[] = [
					'id' => 'password_print',
					'name' => $t->_('button_password_print'),
				];
			}
		}
	}
}