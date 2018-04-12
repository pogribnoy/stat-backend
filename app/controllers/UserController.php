<?php
class UserController extends ControllerEntity{
	public $entityName  = 'User';
	public $tableName  = 'user';
	
	protected function initScrollers() {
		$this->scrollers = [
			'organizationlist' => [
				'linkEntityName' => 'Organization',
				'linkTableName' => 'UserOrganization',
				'linkTableLinkEntityFieldName' => 'organization_id',
				'relationType' => 'nn',
				'controllerClass' => 'OrganizationlistController',
				'addStyle' => 'scroller',
				'editStyle' => 'modal',
				// доп. фиьлтр для выборки данных скроллера
				'addFilter' => function() { return ["user_id" => $this->fields["id"]["value"]]; },
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
				'required' => 2,
				'max' => 50,
				'min' => 1,
				'newEntityValue' => null,
			), 
			'name' => array(
				'id' => 'name',
				'name' => $this->t->_("text_entity_property_fio"),
				'type' => 'text',
				'required' => 1,
				'max' => 255,
				'newEntityValue' => null,
			), 
			'password' => array(
				'id' => 'password',
				'name' => $this->t->_("text_entity_property_password"),
				'type' => 'password',
				'max' => 50,
				'newEntityValue' => null,
			),
			'user_role' => array(
				'id' => 'user_role',
				'name' => $this->t->_("text_entity_property_role"),
				//'type' => 'select',
				//'style' => 'id' //name
				'type' => 'link',
				// TODO. Переделать поля controllerName и field в linkControllerName и linkField
				'controllerName' => 'userrolelist',
				'field' => 'name',
				'linkEntityName' => 'UserRole',
				'required' => 2,
				'newEntityValue' => null,
			),
			'email' => array(
				'id' => 'email',
				'name' => $this->t->_("text_entity_property_email"),
				'type' => 'email',
				'newEntityValue' => null,
			), 
			'phone' => array(
				'id' => 'phone',
				'name' => $this->t->_("text_entity_property_phone"),
				'type' => 'text',
				'max' => 15,
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
		$this->entity->active = $this->fields['active']['value'];
		$this->entity->login = $this->fields['login']['value'];
		$this->entity->name = $this->fields['name']['value'];
		$this->entity->phone = $this->fields['phone']['value'];
		if(isset($this->fields['password']['value'])) $this->entity->password = $this->fields['password']['value'];
		$this->entity->email = $this->fields['email']['value'];
		$this->entity->user_role_id = $this->fields['user_role']['value_id'];
	}
	
	/* 
	* Предоставляет текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhql() {
		// строим запрос к БД на выборку данных
		return "SELECT User.*, UserRole.id AS user_role_id, UserRole.name AS user_role_name FROM User JOIN UserRole on UserRole.id=User.user_role_id WHERE User.id = '" . $this->filter_values["id"] . "'  LIMIT 1";
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
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

	/* 
	* Заполняет свойство fields данными при создании новой сущности
	* Переопределяемый метод.
	*/
	/*public function fillNewEntityFields() {
		// основные поля
		$this->fields["id"]["value"] = '-1';
		$this->fields["active"]["value"] = 1;
		$this->fields["login"]["value"] = '';
		$this->fields["name"]["value"] = '';
		$this->fields["user_role"]["value_id"] = '';
		$this->fields["user_role"]["value"] = '';
		$this->fields["phone"]["value"] = '';
		$this->fields["email"]["value"] = '';
	}*/
	
	/* 
	* Заполняет свойство fields данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	public function fillFieldsWithLists() {
		// роли
		$user_role_rows = UserRole::find();
		$user_roles = array();
		foreach ($user_role_rows as $row) {
			// наполняем массив
			$user_roles[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		$this->fields['user_role']['values'] = $user_roles;
	}
	
	/* 
	* Заполняет свойство scrollers данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	/*public function fillScrollers() {
		$userRoleID = $this->userData['role_id'];
		// грид организаций
		$action = ($this->acl->isAllowed($userRoleID, "user_organizationlist", 'edit') ? ($this->actionNameLC == "show" ? 'show' : 'edit') : ($this->acl->isAllowed($userRoleID, "user_organizationlist", 'show') ? 'show' : null));
		if($action) {
			$controller_organization_list = new OrganizationlistController();
			$scroller_organization_list = $controller_organization_list->createDescriptor($this, array("user_id" => $this->fields["id"]["value"]), $action);
			//$scroller_expense_list['relationType'] = $this->scrollers[$controller_expense_list->controllerNameLC]['relationType'];
			$scroller_organization_list['edit_style']  = "modal";
			$scroller_organization_list["add_style"] = "scroller";
			
			$this->scrollers[$controller_organization_list->controllerNameLC] = $scroller_organization_list;
		}
		else unset($this->scrollers['organizationlist']);
	}*/
	
	/* 
	* Удаляет ссылки на сущность из связанных таблиц
	* Переопределяемый метод.
	*/
	public function deleteEntityLinks($entity) {
		if(!isset($entity)) $entity = $this->entity;
		
		$userOrganizations = UserOrganization::find([
			"conditions" => "user_id = ?1",
			"bind" => array(1 => $this->entity->id)
		]);
		foreach($userOrganizations as $userOrganization) {
			if ($userOrganization->delete() == false) {
				$this->db->rollback();
				$dbMessages = '';
				foreach ($userOrganizations->getMessages() as $message) {
					$dbMessages .= "<li>" . $message . "</li>";
				}
				$this->error['messages'][] = [
					'title' => "Не удалось удалить связь с организацией id=" . $userOrganization->organization_id,
					'msg' => "<ul>" . $dbMessages . "</ul>"
				];
				return false;
			}
		}
		
		return true;
	}
	
	public function sendPasswordAction() {
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			
			if(isset($_REQUEST["email"])) {
				$email = $this->filter->sanitize(urldecode($_REQUEST["email"]), ["trim", "email"]); 
				
				if($email != '') {
					$user = false;
					$user = User::findFirst(['conditions' =>'email = ?1', 'bind' => [1 => $email],]);
					if($user) {
						// отправляем письмо
						$this->success['messages'][] = [
							'title' => "Успех",
							'msg' => $this->t->_("text_password_recover_success"),
						];
					}
					else {
						$this->error['messages'][] = [
						'title' => "Ошибка",
						'msg' => "Пользователь с таким email не найден",
					];
					}
				}
				else {
					$this->error['messages'][] = [
						'title' => "Ошибка",
						'msg' => "Адрес электронной почты передан в неверном формате",
					];
				}
			}
			else {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => "Адрес электронной почты передан в неверном формате",
				];
			}
			
			if(isset($this->error['messages']) && count($this->error['messages'])>0) $this->data['error'] = $this->error;
			if(isset($this->success['messages']) && count($this->success['messages'])>0) $this->data['success'] = $this->success;
			return json_encode($this->data);
		}
	}
}