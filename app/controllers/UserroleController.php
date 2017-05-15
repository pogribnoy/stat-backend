<?php
class UserroleController extends ControllerEntity {
	public $entityName  = 'UserRole';
	public $tableName  = 'user_role';
	
	protected function initScrollers() {
		$this->scrollers = [
			'resourcelist' => [
				'linkEntityName' => 'Resource',
				'linkTableName' => 'UserRoleResource',
				'linkTableLinkEntityFieldName' => 'resource_id',
				'controllerClass' => 'ResourcelistController',
				'relationType' => 'nn',
				'addStyle' => 'scroller',
				'editStyle' => 'modal',
				'addFilter' => function() { return ["user_role_id" => $this->fields["id"]["value"]]; },
			]
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
			'active' => array(
				'id' => 'active',
				'name' => $this->t->_("text_entity_property_active"),
				'type' => 'bool',
				'newEntityValue' => '1',
			), 
			'name' => array(
				'id' => 'name',
				'name' => $this->t->_("text_entity_property_name"),
				'type' => 'text',
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
		$this->entity->active = $this->fields['active']['value'];
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	protected function fillFieldsFromRow($row) {
		$this->fields["id"]["value"] = $row->id;
		$this->fields["name"]["value"] = $row->name;
		$this->fields["active"]["value"] = $row->active;
	}
	
	/* 
	* Заполняет свойство fields данными при создании новой сущности
	* Переопределяемый метод.
	*/
	protected function fillNewEntityFields() {
		// основные поля
		$this->fields["id"]["value"] = '-1';
		$this->fields["name"]["value"] = '';
		$this->fields["active"]["value"] = '';
	}
	
	/* 
	* Удаляет ссылки на сущность из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function deleteEntityLinks($rq) {
		// если надо удалить только то, что передано в запросе
		if(isset($rq)) {
			$resources = false;
			if(isset($rq->scrollers->resourcelist->deleted_items)) {
				$deleted_items_count = count($rq->scrollers->resourcelist->deleted_items);
				if($deleted_items_count>0) {
					$deleted_ids = implode(",", $rq->scrollers->resourcelist->deleted_items);
					//$this->logger->log(json_encode($this->entity));
					// удаляем связи
					$userRoleResources = UserRoleResource::find(array(
						"conditions" => "user_role_id = ?1 AND resource_id IN (?2)",
						"bind" => array(1 => $this->entity->id, 2 => $deleted_ids)
					));
					foreach($userRoleResources as $userRoleResource) {
						if ($userRoleResource->delete() == false) {
							$this->db->rollback();
							$dbMessages = '';
							foreach ($this->entity->getMessages() as $message) {
								$dbMessages .= "<li>" . $message . "</li>";
							}
							$this->error['messages'][] = [
								'title' => "Ошибка",
								'msg' => "Ошибка привязки ресурса:<ul>" . $dbMessages . "</ul>"
							];
							return false;
						}
					}
				}
			}
		}
		else {
			// ссылки из пользователей - блокирующая связь
			$users = false;
			$users = User::find([
				"conditions" => "user_role_id = ?1",
				"bind" => array(1 => $this->entity->id)
			]);
			if($users) {
				$this->db->rollback();
				$this->error['messages'][] = [
					'title' => "Ошибка удаления",
					'msg' => 'Роль назначена одному или более пользователям. Перейти к <a class="" href="/userlist?filter_user_role_id=' + $this->entity->id + '">списку пользователей</a>'
				];
				return false;
			}
		}
		return true;
	}
}
