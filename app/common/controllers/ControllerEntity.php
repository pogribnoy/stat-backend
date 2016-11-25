<?php
class ControllerEntity extends ControllerBase {
	// наименование сущности
	public $entityName;
	// наименование основной таблицы для хранения сущности
	public $tableName;
	
	// перечень полей для фильтра (уникальные для разных таблиц)
	protected $filter_values = array();
	
	// переменные для структурированного ответа
	protected $data = array('dbg' => '');
	protected $error = array('messages' => array());
	protected $success = array('messages' => array());
	
	// сущность - модель, прочитанная из БД
	protected $entity = false;
	
	// поля сущности, включая и связанные данные
	protected $fields;
	
	// операции, достыпные для сущности
	protected $operations;
	
	// скроллеры сущности
	protected $scrollers;
	
	// описатель скроллера
	public $descriptor;
	
	public function initialize() {
		parent::initialize();
	}
	
	/* 
	* Используется при открытии из скроллера сущности на просмотр на отдельной странице
	*/
	public function indexAction() {
		$this->createDescriptor();
		
		// передаем в представление имеющиеся данные
		$this->view->setVar('page_header', $this->t->_('text_' . $this->controllerName . '_title'));
		$this->view->setVar("descriptor", $this->descriptor);
	}
	
	/* 
	* Используется при открытии из скроллера сущности на редактирование на отдельной странице
	*/
	public function editAction() {
		$this->createDescriptor();
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			return json_encode($this->descriptor);
		}
		else {
			// передаем в представление имеющиеся данные
			$this->view->setVar('page_header', $this->t->_('text_' . $this->controllerName . '_title'));
			$this->view->setVar("descriptor", $this->descriptor);
		}
	}
	
	/* 
	* Используется при открытии из скроллера сущности на редактирование на отдельной странице
	*/
	public function showAction() {
		$this->createDescriptor();
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			return json_encode($this->descriptor);
		}
		else {
			// передаем в представление имеющиеся данные
			$this->view->setVar('page_header', $this->t->_('text_' . $this->controllerName . '_title'));
			$this->view->setVar("descriptor", $this->descriptor);
		}
	}
	
	/* 
	* Используется при открытии из скроллера сущности на редактирование в модалке
	*/
	/*public function getDataAction() {
		
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		
		$this->createDescriptor();
		
		return json_encode($this->descriptor);
	}*/
	
	/* 
	* Сохраняет или обнослявяет запись в БД
	*/
	public function saveAction() {
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		
		if ($this->request->isPost()) {
			$rq = $this->request->getJsonRawBody();
			$id = null;
			
			$this->initFields();
			
			if($this->sanitizeSaveRqData($rq)) {
				$this->logger->log("saveAction. sanitizeSaveRqData: " . json_encode($this->fields));
				$id = $this->fields['id']['value'];
				
				// открываем транзакцию
				$this->db->begin();
				
				// ищем сущность
				$this->entity = $this->createOrFindEntity($id);
				
				// если сущность не нашли и не создали
				if($this->entity == false){
					//$this->db->rollback();
					$this->error['messages'][] = [
						'title' => "Ошибка",
						'msg' => "Ошибка обновления данных при попытке сохранения сущности с id=".$id
					];
				}
				// нашли или создали
				else {
					$this->logger->log("saveAction. entity found: " . json_encode($this->entity));
					// наполняем поля
					$this->fillModelFieldsFromSaveRq();
					if($id<0){
						if ($this->entity->create() == false) {
							//$this->db->rollback();
							$dbMessages = '';
							foreach ($this->entity->getMessages() as $message) {
								$dbMessages .= "<li>" . $message . "</li>";
							}
							$this->error['messages'][] = [
								'title' => "Ошибка создания",
								'msg' => "<ul>" . $dbMessages . "</ul>"
							];
						}
					}
					else {
						if($this->entity->update() == false) {
							//$this->db->rollback();
							$dbMessages = '';
							foreach ($this->entity->getMessages() as $message) {
								$dbMessages .= "<li>" . $message . "</li>";
							}
							$this->error['messages'][] = [
								'title' => "Ошибка обновления данных",
								'msg' => "<ul>" . $dbMessages . "</ul>"
							];
						}
					}
				}
				if(count($this->error['messages'])==0) {
					// если сущность сохранена в БД, то создаем ее связи
					if($this->saveEntityLinksFromSaveRq($rq)) {
						$this->data['scrollers'] = $this->scrollers;
						$this->db->commit();
						$this->updateEntityFieldsFromModelAfterSave();
						if($id<0) $this->data['newID'] = $this->entity->id;
						$this->success['messages'][] = [
							'title' => "Операция успешна",
							'msg' => "Запись с идентификатором " . $this->entity->id ." сохранена"
						];
					}
					else $this->db->rollback();
				}
			}
			/*else {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => "Не получены необходимые параметры сущности"
				];
			}*/
		}
		else {
			$this->error['messages'][] = [
				'title' => "Ошибка",
				'msg' => "Запрошено сохранение данных методом GET. Ожидается метод POST"
			];
		}
		//$data['dbg'] = $id;
		$this->data['item'] = $this->entity;	
		$this->data['rq'] = $rq; // исходные параметры запроса для отладки
		if(isset($this->error['messages']) && count($this->error['messages'])>0) $this->data['error'] = $this->error;
		else if(isset($this->success['messages']) && count($this->success['messages'])>0) $this->data['success'] = $this->success;
		echo json_encode($this->data);
	}
	
	/* 
	* Удаляет запись в БД
	*/
	public function deleteAction() {
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		
		$filt = new Phalcon\Filter();
	
		if(isset($_REQUEST["id"])) $this->filter_values["id"] = $filt->sanitize(urldecode($_REQUEST["id"]), ["trim", "int"]); 
		else $this->filter_values["id"] = '';
		
		// TODO. Проверяем права на удаление сущности
		
		// ищем сущность
		$this->entity = $this->findEntity($this->filter_values["id"]);
		
		// если сущность не нашли
		if($this->entity == false){
			$this->error['messages'][] = [
				'title' => "Ошибка",
				'msg' => "Запись с id=" . $this->filter_values["id"] . ' не найдена'
			];
		}
		// нашли
		else {
			// открываем транзакцию
			$this->db->begin();
			
			// удаляем сущность
			$this->deleteEntity();
			
			if(count($this->error['messages'])==0) {
				$this->db->commit();
				//$this->db->rollback();
				$this->success['messages'][] = [
					'title' => "Операция успешна",
					'msg' => "Запись с идентификатором " . $this->entity->id ." удалена"
				];
			}
			else $this->db->rollback();
		}
		
		//$data['dbg'] = $id;
		//$this->data['item'] = $this->entity;
		
		if(isset($this->error['messages']) && count($this->error['messages'])>0) $this->data['error'] = $this->error;
		else {
			$scrollerName =  $this->controllerName . 'list';
			if($this->acl->isAllowed($this->userData['role_id'], $scrollerName, 'index')) $this->data['redirectURL'] = '/' . $scrollerName;
			else $this->data['redirectURL'] = '/';
		}
		if(isset($this->success['messages']) && count($this->success['messages'])>0) $this->data['success'] = $this->success;
		echo json_encode($this->data);
	}
	
	/* 
	* Удаляет сушность, хранимую в $this->entity, если не передана отдельная сущность
	*/
	public function deleteEntity($entity=null) {
		if(!isset($entity)) $entity = $this->entity;
		// проверяем возможность удаления по связанным сущностям и удаляем возможные ссылки на сущность из связывающих таблиц
		if($this->deleteEntityLinks($entity)) {
			// удаляем саму сущность
			if(!$entity->delete()) {
				$dbMessages = '';
				foreach ($entity->getMessages() as $message) {
					$dbMessages .= "<li>" . $message . "</li>";
				}
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => "Ошибка удаления:<ul>" . $dbMessages . "</ul>"
				];
			}
		}
		return ['error' => $this->error, 'success' => $this->success];
	}
	
	/* 
	* Общий метод, формирующий дескриптор сущности для ответа
	*/
	public function createDescriptor() {
		$this->sanitizeGetDataRqFilters();
		
		$this->fillOperations();
		
		$this->initFields();
		$this->logger->log("initFields");
		
		//$this->fillFieldsWithLists();
		
		// если запрошена конкретная сущность
		if($this->filter_values["id"] != "") {
			// строим запрос к БД на выборку данных
			$phql = $this->getPhql();
			
			// выбираем данные
			$rows = false;
			$rows = $this->modelsManager->executeQuery($phql);
			
			// наполняем $fields
			$this->fillFieldsFromRows($rows);
			// если значнение id не заполнено (не нашли единственную запись в БД)
			if(!isset($this->fields["id"]["value"])) {
				$this->dispatcher->forward([
					'controller' => 'errors',
					'action'     => 'show404',
				]);
				return;
			}
				// наполняем поля с файлами
				//$this->fillFieldsWithLists();
				$this->getFiles();
			//}
		}
		// если запрошена пустая сущность (создание сущности)
		else {
			$this->fillNewEntityFields();
			//$this->logger->log(json_encode($this->fields));
		}
			
		$this->fillScrollers();
		
		$this->createDescriptorObject();
	}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	*/
	protected function fillFieldsFromRows($rows) {
		if(count($rows)==1) {
			$this->fillFieldsFromRow($rows[0]);
		}
		// если не найдены записи
		else{
			$this->error['messages'][] = [
				'title' => "Ошибка",
				'msg' => "Выборка данных из БД либо пуста, либо содержит более 1 записи"
			];
		}
	}
	
	/* 
	* Предоставляет текст шаблона для рисования сущности, если он есть
	*/
	protected function getTmpl() {
		// передаем шаблон, если он есть
		$tmplFileName = APP_PATH . $this->config->application->templatesDir . $this->controllerName . ".phtml";
		//$this->logger->log(json_encode($tmplFileName));
		if (file_exists($tmplFileName)) return file_get_contents($tmplFileName);
		else return null;
	}
	
	/* 
	* Наполняет массив доступных операций для сущности
	*/
	protected function fillOperations() {
		// добавляем действия, доступные пользователям с разными ролями
		// формируем список действий, который не должен быть доступен
		$exludeOps = $this->getExludeOps();
		
		// получаем действия, доступные пользователю
		$this->tools = Phalcon\DI::getDefault()->getTools();
		$this->operations = $this->tools->getEntityFormOperations($this->userData['role_id'], $this->entityName, $this->acl, $this->t, $exludeOps, $this->actionName);
	}
	
	/* 
	* Предоставляет массив операций для сущности, которые надо исключить
	*/
	protected function getExludeOps() {
		$exludeOps[] = array();
		// если запрошена пустая сущность, то не нужна операция удаления
		if($this->filter_values["id"] == "") {
			$exludeOps[] = 'delete';
		}
		return $exludeOps;
	}
	
	/* 
	* Заполняет свойство descriptor данными
	*/
	protected function createDescriptorObject() {
		$this->descriptor = array(
			"controllerName" => $this->controllerName,
			"entity" => $this->entityName,
			"type" => "entity",
			"fields" => $this->fields,
			"scrollers" => $this->scrollers,
			"operations" => $this->operations,
			"filter_values" => $this->filter_values,
			"title" => (isset($this->fields["name"]) && $this->fields["name"]["value"] != '') ? $this->fields["name"]["value"] : 
				($this->fields["id"] == -1) ? $this->t->_("text_" . $this->controllerName . "_new_entity_title") : $this->t->_("text_" . $this->controllerName . "_title"),
			"template" => $this->getTmpl(),
			'data' => $this->data,
			'actionName' => $this->actionName,
		);
		//$this->logger->log(json_encode($this->descriptor));
	}
	
	/* 
	* Проверяет наличие сущностей из списков изменений в скроллере
	*/
	protected function sanitizeSaveRqDataCheckRelations($rq) {
		if(isset($rq->scrollers)) {
			foreach($rq->scrollers as $scrollerName => $scroller) {
				if(array_key_exists($scrollerName, $this->scrollers)) {
					$entities = false;
					$linkEntityName = $this->scrollers[$scrollerName]['linkEntityName'];
					
					// добавляемые связи
					if(isset($rq->scrollers->$scrollerName->added_items) && count($rq->scrollers->$scrollerName->added_items) > 0) {
						$this->scrollers[$scrollerName]['added_items'] = [];
						foreach($rq->scrollers->$scrollerName->added_items as $id) {
							$id = $this->filter->sanitize(urldecode($id), ['trim', "int"]);
							if($id != '') $this->scrollers[$scrollerName]['added_items'][] = $id;
							else {
								$this->error['messages'][] = [
									'title' => "Ошибка",
									'msg' => $scrollerName . ". Передан некорректный идентификатор"
								];
								return false;
							}
						}
						if($this->scrollers[$scrollerName]['relationType'] == 'n') {
							// проверяем, чтобы ВСЕ привязываемые сущности существовали
							$entities = $linkEntityName::find(["conditions" => "id IN (?1)", "bind" => [
								1 => implode(',', $this->scrollers[$scrollerName]['added_items']),
							], "for_update" => true]);
							if(!$entities || count($entities) < count($this->scrollers[$scrollerName]['added_items'])) {
								$this->error['messages'][] = [
									'title' => "Ошибка",
									'msg' => $scrollerName . ". Запись не найдена в БД"
								];
								return false;
							}
							/*$linkEntityFieldName = $this->scrollers[$scrollerName]['linkEntityFieldName'];
							foreach($entities as $e) {
								$e->organization_id = $this->fields['id']['value'];
							}*/
							$this->scrollers[$scrollerName]['added_entities'] = $entities;
						}
						else if($this->scrollers[$scrollerName]['relationType'] == 'nn') {
							$linkTableName = $this->scrollers[$scrollerName]['linkTableName'];
							$linkTableLinkEntityFieldName = $this->scrollers[$scrollerName]['linkTableLinkEntityFieldName'];
							// проверяем, чтобы такие сущности еще не были привязаны к текущей
							$links = $linkTableName::find(["conditions" => $linkTableLinkEntityFieldName . " IN ({added_items:array}) AND " . $this->tableName . '_id = ?2', "bind" => [
								'added_items' => $this->scrollers[$scrollerName]['added_items'],
								2 => $this->fields['id']['value'],
							]]);
							// если привязываются уже привязанные записи, то просто их убираем из списка привязываемых
							if($links && count($links)>0) {
								$elid = $linkTableLinkEntityFieldName;
								foreach($links as $link) {
									while (($i = array_search($link->$elid, $this->scrollers[$scrollerName]['added_items'])) !== false) {
										unset($this->scrollers[$scrollerName]['added_items'][$i]);
									} 
								}
							}
							// проверяем, чтобы ВСЕ привязываемые сущности существовали
							$entities = $linkEntityName::find(["conditions" => "id IN ({added_items:array})", "bind" => [
								'added_items' => $this->scrollers[$scrollerName]['added_items'],
							]]);
							if(!$entities || count($entities) < count($this->scrollers[$scrollerName]['added_items'])) {
								$this->error['messages'][] = [
									'title' => "Ошибка",
									'msg' => $scrollerName . ". Не найдены добавляемые записи1"
								];
								
								return false;
							}
						}
					}
					// удаляемые связи
					if(isset($rq->scrollers->$scrollerName->deleted_items) && count($rq->scrollers->$scrollerName->deleted_items) > 0) {
						$this->scrollers[$scrollerName]['deleted_items'] = [];
						foreach($rq->scrollers->$scrollerName->deleted_items as $id) {
							$id = $this->filter->sanitize(urldecode($id), ['trim', "int"]);
							/*$this->success['messages'][] = [
								'title' => "Ошибка",
								'msg' => "id = " . $id
							];*/
							if($id != '') $this->scrollers[$scrollerName]['deleted_items'][] = $id;
							else {
								$this->error['messages'][] = [
									'title' => "Ошибка",
									'msg' => $scrollerName . ". Передан некорректный идентификатор"
								];
								return false;
							}
						}
						/*$this->success['messages'][] = [
							'title' => "Ошибка",
							'msg' => "del = " . implode(',', $this->scrollers[$scrollerName]['deleted_items'])
						];*/
					}
				}
				else {
					$this->error['messages'][] = [
						'title' => "Ошибка",
						'msg' => "Передан неизвестный массив " . $key
					];
					return false;
				}
			}
		}
		else $this->error['messages'][] = [
			'title' => "Ошибка",
			'msg' => "Не переданы связанные массивы"
		];
		return true;
	}
	
	/* 
	* Сохраняет изменения связей сущности с сущностями из списков изменений в скроллере
	*/
	protected function saveRqDataRelations($scrollerName) {
		$entities = false;
		
		// добавляемые связи
		if(isset($this->scrollers[$scrollerName]['added_items']) && count($this->scrollers[$scrollerName]['added_items']) > 0) {
			if($this->scrollers[$scrollerName]['relationType'] == 'n') {
				// обновляем привязываемые сущности (они были открыты for_update при очистке)
				$linkEntityFieldName = $this->scrollers[$scrollerName]['linkEntityFieldName'];
				foreach($this->scrollers[$scrollerName]['added_entities'] as $key => $scroller_entity) {
					$scroller_entity->$linkEntityFieldName = $this->fields['id']['value'];
					
					if($scroller_entity->update() == false) {
						$dbMessages = '';
						foreach ($scroller_entity->getMessages() as $message) {
							$dbMessages .= "<li>" . $message . "</li>";
						}
						$this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => "Ошибка обновления данных:<ul>" . $dbMessages . "</ul>"
						];
						return false;
					}
				}
			}
			else if($this->scrollers[$scrollerName]['relationType'] == 'nn') {
				$linkTableName = $this->scrollers[$scrollerName]['linkTableName'];
				$linkTableLinkEntityFieldName = $this->scrollers[$scrollerName]['linkTableLinkEntityFieldName'];
				// обновляем связи с сущностями
				foreach($this->scrollers[$scrollerName]['added_items'] as $itemID) {
					$link = new $linkTableName();
					$eID = $this->tableName . '_id';
					$link->$eID = $this->fields['id']['value'];
					$link->$linkTableLinkEntityFieldName = $itemID;
					$this->data['ai'] = $linkTableLinkEntityFieldName;
					$this->data['ai2'] = $eID;
					if($link->create() == false) {
						$dbMessages = '';
						foreach ($link->getMessages() as $message) {
							$dbMessages .= "<li>" . $message . "</li>";
						}
						$this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => "Ошибка обновления данных:<ul>" . $dbMessages . "</ul>"
						];
						return false;
					}
				}
			}
		}
		// удаляемые связи
		if(isset($this->scrollers[$scrollerName]['deleted_items']) && count($this->scrollers[$scrollerName]['deleted_items']) > 0) {
			if($this->scrollers[$scrollerName]['relationType'] == 'n') {
				$linkTableNameCall = 'get' . $this->scrollers[$scrollerName]['linkEntityName'];
				if($this->entity->$linkTableNameCall()->delete(function ($le) use ($scrollerName) {
						if (in_array($le->id, $this->scrollers[$scrollerName]['deleted_items'])) return true;
						return false;
					}) == false) {
					$dbMessages = '';
					foreach ($entity->getMessages() as $message) {
						$dbMessages .= "<li>" . $message . "</li>";
					}
					$this->error['messages'][] = [
						'title' => "Ошибка",
						'msg' => "Ошибка удаления данных:<ul>" . $dbMessages . "</ul>"
					];
					return false;
				}
			}
			else if($this->scrollers[$scrollerName]['relationType'] == 'nn') {
				$linkTableName = $this->scrollers[$scrollerName]['linkTableName'];
				$linkTableLinkEntityFieldName = $this->scrollers[$scrollerName]['linkTableLinkEntityFieldName'];
				// обновляем связи с сущностями
				$links = $linkTableName::find(["conditions" => $linkTableLinkEntityFieldName . " IN ({deleted_items:array}) AND " . $this->tableName . '_id = ?2', "bind" => [
					'deleted_items' => $this->scrollers[$scrollerName]['deleted_items'],
					2 => $this->fields['id']['value'],
				]]);
				// если привязываются уже привязанные записи, то просто их убираем из списка привязываемых
				if($links && count($links)>0) {
					foreach($links as $link) {
						if($link->delete() === false) {
							$this->db->rollback();
							$dbMessages = '';
							foreach ($link->getMessages() as $message) {
								$dbMessages .= "<li>" . $message . "</li>";
							}
							$this->error['messages'][] = [
								'title' => "Ошибка",
								'msg' => "Ошибка удаления связей:<ul>" . $dbMessages . "</ul>"
							];
							return false;
						}
					}
				}
			}
		}
		return true;
	}
	
	/* 
	* Сохраняет изменения связей сущности с сущностями из списков изменений в скроллере
	*/
	protected function saveEntityLinksFromSaveRq() {
		if(isset($this->scrollers)) {
			foreach($this->scrollers as $scrollerName => $scroller) {
				if(!$this->saveRqDataRelations($scrollerName)) return false;
			}
		}
		return true;
	}
	
	/* 
	* Заполняет свойство filter_values данными из запроса
	* Расширяемый метод.
	*/
	protected function sanitizeGetDataRqFilters() {
		if(isset($_REQUEST["id"])) $this->filter_values["id"] = $this->filter->sanitize(urldecode($_REQUEST["id"]), ["trim", "int"]); 
		else $this->filter_values["id"] = '';
		// доп параметры фильтрации, которые могут использоваться для заполнения полей сущности
		if(isset($_REQUEST["filter_organization_id"])) $this->filter_values["organization_id"] = $this->filter->sanitize(urldecode($_REQUEST["filter_organization_id"]), ["trim", "int"]); 
		
		$this->logger->log("sanitizeGetDataRqFilters: " . json_encode($this->filter_values));
	}
	
	/* 
	* Формирует текст запроса к БД
	* Переопределяемый метод.
	*/
	protected function getPhql() {
		$phql = "SELECT <TableName>.* FROM <TableName> WHERE <TableName>.id = '" . $this->filter_values["id"] . "' LIMIT 1";
		//$this->logger->log("entityName = " . $this->entityName);
		$phql = str_replace("<TableName>", $this->entityName, $phql);
		return $phql;
	}
	
	/* 
	* Заполняет (инициализирует) свойство fields
	* Расширяемый метод.
	*/
	protected function initFields() {
		foreach($this->fields as $fieldID => &$field) {	
			if($field['type'] == 'select' && $field['style'] == 'id') {
				$this->fillFieldWithLists($field);
			}
		}
	}
	
	/* 
	* Наполняет модель сущности из запроса при сохранении
	* Переопределяемый метод.
	*/
	protected function fillModelFieldsFromSaveRq() {}
	
	/* 
	* Заполняет свойство fields данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	protected function fillFieldsFromRow($row) {}
	
	/* 
	* Заполняет свойство fields данными при создании новой сущности
	* Переопределяемый метод. Переопределяется при необходимости
	*/
	protected function fillNewEntityFields() {
		$this->logger->log("fillNewEntityFields: " . json_encode($this->filter_values));
		foreach($this->fields as $fieldID => &$field) {
			// если поля связывает по id
			if($field['type'] == 'link' || ($field['type'] == 'select' && $field['style'] == 'id')) {
				$field["value"] = null;
				$linkEntityName = $field['linkEntityName'];
				if(isset($field["value_id"]) && $field["value_id"] != null) {
					$entity = false;
					$entity = $linkEntityName::findFirst( array(
						"conditions" => "id = ?1",
						"bind" => array(1 => $field["newEntityValue"])
					));
					if(!$entity) {
						$field["value"] = null;
						$this->logger->log('Controller/Action: ' . $this->controllerName . '/' . $this->actionName . '. Function: ' . __FUNCTION__ . '. Default entity "' . $linkEntityName . '" with ID=' . $field["value_id"] . ' for field "' . $fieldID . '" not found');
					}
					else $field["value"] = null;
				}
				// наполняем выпадающий список для select
				/*if($field['type'] == 'select') {
					$this->fillFieldWithLists($field);
				}*/
			}
			// если поле связывает по тексту
			else if($field['type'] == 'select') {
				if(isset($field['style']) && $field['style'] == 'name') {
					$field["value"] = null;
					if(isset($field["values"])) {
						if(in_array($field["newEntityValue"], $field["values"]))  $field["value"] = $field["newEntityValue"];
						else $this->logger->log('Controller/Action: ' . $this->controllerName . '/' . $this->actionName . '. Function: ' . __FUNCTION__ . '. Default value "' . $field["newEntityValue"] . '" for field "' . $fieldID . '" not found in inline array "field["values"]"');
					}
					else if(isset($field["newEntityValue"]) && $field["newEntityValue"] != '' && $field["newEntityValue"] != null) $this->logger->log('Controller/Action: ' . $this->controllerName . '/' . $this->actionName . '. Function: ' . __FUNCTION__ . '. Default value "' . $field["newEntityValue"] . '" for field "' . $fieldID . '" is set, but inline array "field["values"]" is not set');
				}
			}
			else if($field['type'] == 'img') {
				// для изображениq значение по умолчанию пока не предусмотрено
			}
			else if(isset($field["newEntityValue"]) && $field["newEntityValue"] != '' && $field["newEntityValue"] != null) $field["value"] = $field["newEntityValue"];
			// если предусмотрена выборка по ID, то надо запросить значение в связанной таблице
			else if(isset($field["newEntityID"]) && $field["newEntityID"] != '' && $field["newEntityID"] != null && 
					isset($this->filter_values[$field["id"] . "_id"]) && $this->filter_values[$field["id"] . "_id"] != '' && $this->filter_values[$field["id"] . "_id"] != null) {
				$linkEntityName = $field["linkEntityName"];
				$linkEntityField = $field["linkEntityField"];
				$entities = $linkEntityName::find([
					"conditions" => "id = ?1", "bind" => [
						1 => $this->filter_values[$field["id"] . "_id"],
					],
					'limit' => 1,
				]);
				if($entities && count($entities) == 1) $field["value"] = $entities[0]->$linkEntityField;
				else $field["value"] = null;
			}
			else $field["value"] = null;
		}
	}
	
	/* 
	* Заполняет свойства fields данными файлов из связанных коллекций
	*/
	protected function getFiles() {
		foreach($this->fields as $fieldID => &$field) {	
			if($field['type'] == 'img') {
				//$this->logger->log(json_encode($this->fields));
				$row = false;
				$entityName = $this->tableName;
				$row = $entityName::findFirst([
					'conditions' => 'id = ?1',
					'bind' => [1 => $this->fields["id"]["value"]],
				]);
				//$this->data['dbg'] = json_encode($row);
				if($row) {
					$files = false;
					$files = $row->getFile(["limit" => $field["max_count"]]);
					//$this->data['files'] = json_encode($files);
					if($files && count($files)>0) {
						$field['files'] = array();
						foreach($files as $file) {
							$field['files'][] = [
								'id' => $file->id,
								'name' => $file->name,
								'key' => $file->id,
								'url' => '/' . $file->directory . $file->name
							];
						}
					}
				}
			}
		}
	}
	
	/* 
	* Заполняет свойство fields данными списков из связанных таблиц
	* Переопределяемый метод. Переопределяется при необходимости
	*/
	protected function fillFieldWithLists(&$field) {
		$linkEntityName = $field['linkEntityName'];
		$rows = $linkEntityName::find();
		//$this->logger->log('rows: ' . json_encode($rows));// DEBUG
		$entities = array();
		foreach ($rows as $row) {
			// наполняем массив
			$entities[] = array(
				'id' => $row->id,
				"name" => $row->name
			);
		}
		//$this->data['asd'] = json_encode($rows);
		$field['values'] = $entities;
	}
	
	/* 
	* Заполняет свойство scrollers данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function fillScrollers() {}
	
	/* 
	* Ищет запись в БД или создает новую
	* Переопределяемый метод.
	*/
	protected function createOrFindEntity($id) {
		$entity = $this->findEntity($id);
		if(!$entity) {
			$entityName = $this->entityName;
			// создается новая сущность
			return new $entityName();
		}
		else return $entity;
	}
	
	/* 
	* Ищет запись в БД
	* Переопределяемый метод.
	*/
	protected function findEntity($id) { 
		if($id && $id >= 0) {
			$entityName = $this->entityName;
			return $entityName::findFirst( array(
				"conditions" => "id = ?1",
				"bind" => array(1 => $id)
			));
		}
		else return false;
	}
	
	/* 
	* Удаляет ссылки на сущность из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function deleteEntityLinks($entity) {return true;}
	
	/* 
	* Обновляет данные сущности после сохранения в БД (например, проставляется дата создвания записи)
	* Переопределяемый метод.
	*/
	protected function updateEntityFieldsFromModelAfterSave() {}
	
	/* 
	* Очищает параметры запроса
	* Расширяемый метод.
	*/
	protected function sanitizeSaveRqData($rq) {
		// id
		if(isset($rq->fields->id) && isset($rq->fields->id->value)) {
			$val = $this->filter->sanitize(urldecode($rq->fields->id->value), ["trim", "int"]);
			if($val == '') {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => "Не получен идентификатор сущности"
				];
				return false;
			}
			$this->fields['id']['value'] = $val;
		}
		else {
			$this->error['messages'][] = [
				'title' => "Ошибка",
				'msg' => "Не получен идентификатор сущности"
			];
			return false;
		}
		
		// ссылочные поля (select, link)
		foreach($this->fields as $fieldID => $field) {
			if($field['type'] == 'select' || $field['type'] == 'link') {
				// ожидается поле типа 'id'
				if((isset($field['style']) && $field['style'] == 'id') || $field['type'] == 'link') {
					// поле есть в запросе
					if(isset($rq->fields->$fieldID)) {
						// поле содержит id значения
						if(isset($rq->fields->$fieldID->value_id)) {
							$id = $this->filter->sanitize(urldecode($rq->fields->$fieldID->value_id), ['trim', "int"]);
							if($id != '') {
								$linkEntityName = $field['linkEntityName'];
								$entity = false;
								$entity = $linkEntityName::findFirst(["conditions" => "id = ?1", "bind" => [1 => $id]]);
								if(!$entity) {
									$this->error['messages'][] = [
										'title' => "Ошибка",
										'msg' => $fieldID . ". Передан некорректный идентификатор (не найден  БД)"
									];
									return false;
								}
								$this->fields[$fieldID]['value_id'] = $id;
							}
							else {
								$this->error['messages'][] = [
									'title' => "Ошибка",
									'msg' => 'Поле "' . $this->fields[$fieldID]['name'] . '" обязательно для указания'
								];
								return false;
							}
						}
						// поле не содержит id значения
						else {
							$this->error['messages'][] = [
								'title' => "Ошибка",
								'msg' => 'Поле "' . $this->fields[$fieldID]['name'] . '" обязательно для указания'
							];
							return false;
						}
					}
					// поля нет в запросе
					else {
						// но оно обязательное
						if(isset($field['required']) && $field['required'] == 1) {
							$this->error['messages'][] = [
								'title' => "Ошибка",
								'msg' => 'Поле "' . $this->fields[$fieldID]['name'] . '" обязательно для указания'
							];
							return false;
						}
					}
				}
				else if(isset($field['style']) && $field['style'] == 'name') {
					// поле есть в запросе
					if(isset($rq->fields->$fieldID)) {
						// поле содержит значение
						if(isset($rq->fields->$fieldID->value)) {
							$val = $this->filter->sanitize(urldecode($rq->fields->$fieldID->value_id), ['trim', "int"]);
							// значение не пустое или пустое, но поле не обязательное
							if(($val != '') || ($val == '' && !isset($field['required'])) || ($val == '' && isset($field['required']) && $field['required'] == 0)) {
								$this->fields[$fieldID]['value'] = $this->fields[$fieldID]['values'][$val];
							}
							else {
								$this->error['messages'][] = [
									'title' => "Ошибка",
									'msg' => 'Поле "' . $this->fields[$fieldID]['name'] . '" обязательно для указания'
								];
								return false;
							}
						}
						else {
							$this->error['messages'][] = [
								'title' => "Ошибка",
								'msg' => 'Поле "' . $this->fields[$fieldID]['name'] . '" обязательно для указания'
							];
							return false;
						}
					}
					// поля нет в запросе
					else {
						// но оно обязательное
						if(isset($field['required']) && $field['required'] == 1) {
							$this->error['messages'][] = [
								'title' => "Ошибка",
								'msg' => 'Поле "' . $this->fields[$fieldID]['name'] . '" обязательно для указания'
							];
							return false;
						}
					}
				}
			}
		}
		return true;
	}
	
	/* 
	* Очищает параметры запроса
	* Расширяемый метод.
	*/
	protected function check() {
		foreach($this->fields as $fieldID => $field) {
			// проверка на обязательность не зависимо от типа
			if(isset($field['required']) && $field['required'] == 1 && (!isset($field['value']) || $field['value'] == null || $field['value'] == '')) {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => 'Поле "' .  $field['name'] . '" обязательно для указания',
				];
			}
			// если поле заполнено, то выполняем остальные проверки
			else {
				if($field['type'] == 'amount') {
					if(isset($field['min']) && $field['value'] < $field['min']) {
						$this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => 'Поле "' . $field['name'] . '" содержит значение меньше допустимого',
						];
					}
					if(isset($field['max']) && $field['value'] > $field['max']) {
						$this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => 'Поле "' .$field['name'] . '" содержит значение больше допустимого',
						];
					}
				}
				if($field['type'] == 'email') {
					if(!strpos($field['value'], "@") === FALSE) {
						$this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => 'Поле "' . $field['name'] . '" содержит значение, не соответствущее адресу электронной почты',
						];
					}
				}
				if($field['type'] == 'text' || $field['type'] == 'textarea') {
					if(isset($field['min']) && $field['value'] < $field['min']) {
						$this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => 'Поле "' . $field['name'] . '" содержит слишком короткое значение (должно быть не менее ' . $field['min'] . ' символов)',
						];
					}
					if(isset($field['max']) && $field['value'] > $field['max']) {
						$this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => 'Поле "' . $field['name'] . '" содержит слишком длинное значение (должно быть не более ' . $field['max'] . ' символов)',
						];
					}
				}
			}
		}
		return true;
	}
}