<?php
class ControllerEntity extends ControllerBase {
	// наименование сущности
	public $entityName;
	// наименование сущности в нижнем регистре
	public $entityNameLC;
	// наименование основной таблицы для хранения сущности
	public $tableName;
	// результат выполнения контролей
	public $checkResult = array();
	
	// перечень полей для фильтра (уникальные для разных таблиц)
	protected $filter_values = array();
	
	// переменные для структурированного ответа
	protected $data = array('dbg' => '');
	protected $error = array('messages' => array());
	protected $success = array('messages' => array());
	
	// сущность - модель, прочитанная из БД
	public $entity = false;
	
	// поля сущности, включая и связанные данные
	protected $fields = null;
	
	// операции, достыпные для сущности
	protected $operations = null;
	
	// операции, которые по тем или иным причинам не должны быть доступны для сущности
	protected $exludeOps = null;
	
	// скроллеры сущности
	protected $scrollers = null;
	
	// описатель скроллера
	public $descriptor = null;
	
	// шаблон по умолчанию
	public $templateName = "entity_template";
	
	public function initialize() {
		parent::initialize();
	}
	
	/* 
	* Используется при открытии сущности на просмотр/редактирование, когда непонятно, какие права на нее имеются
	*/
	public function indexAction() {
		$role_id = $this->userData['role_id'];
		//var_dump($this->request->getURI());
		
		if($this->acl->isAllowed($role_id, $this->controllerNameLC, 'edit')) {
			$this->dispatcher->forward(array(
				'controller' => $this->controllerNameLC,
				'action'     => 'edit',
			));
			//$this->forward($this->request->getURI());
		}
		else if($this->acl->isAllowed($role_id, $this->controllerNameLC, 'show')) {
			$this->dispatcher->forward(array(
				'controller' => $this->controllerNameLC,
				'action'     => 'show',
			));
			//$this->forward($this->request->getURI());
		}
		else {
			$this->logger->error(__METHOD__ . '. "edit" and "show" action are not allowed. Forward to "/errors/show404". URL=' . $this->request->getURI());
			$this->dispatcher->forward(array(
				'controller' => 'errors',
				'action' => 'show404',
				'sourceURL' => $this->request->getURI(),
			));
		}
	}
	
	/* 
	* Используется при фильтрации скроллеров/гридов сущности
	*/
	public function filterAction() {
		$role_id = $this->userData['role_id'];
		$scrollerName = $this->request->get("scrollerName", ["trim", "string"]);
		if(!$scrollerName) {
			$this->dispatcher->forward(array(
				'controller' => 'errors',
				'action' => 'show404',
				'sourceURL' => $this->request->getURI(),
			));
			return;
		}
		//$resource = $this->controllerNameLC . '_' . $scrollerName;
		//var_dump($resource);
		
		$actionName = $this->request->get("actionName", ["trim", "string"], "show");
		//var_dump($actionName);
		$this->dispatcher->forward(array(
			'controller' => $scrollerName,
			'action' => 'index',
			'priorAction' => $actionName,
		));
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
			//$this->view->setVar('page_header', $this->t->_('text_' . $this->controllerNameLC . '_title'));
			$this->view->setVar("descriptor", $this->descriptor);
		}
	}
	
	/* 
	* Используется при открытии из скроллера сущности на редактирование на отдельной странице
	*/
	public function showAction() {
		//$this->logger->log(__METHOD__ . ". createDescriptor() before, actionName: " . json_encode($this->actionName));
		$this->createDescriptor();
		
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			return json_encode($this->descriptor);
		}
		else {
			// передаем в представление имеющиеся данные
			//$this->view->setVar('page_header', $this->t->_('text_' . $this->controllerNameLC . '_title'));
			$this->view->setVar("descriptor", $this->descriptor);
		}
	}
	
	/* 
	* Сохраняет или обнослявяет запись в БД
	*/
	public function saveAction() {
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		
		//$this->logger->log(__METHOD__ . ". qwe ");
		
		if ($this->request->isPost()) {
			$this->initServerSideData();
			
			$this->rq = $this->request->getJsonRawBody();
			$id = null;

			//$this->logger->log(__METHOD__ . ". Fields: " . json_encode($this->fields));
			
			$res = $this->sanitizeSaveRqData();
			$res |= $this->check();
			
			//$this->logger->log(__METHOD__ . ". res: " . $res);
			if(!isset($_REQUEST["check_only"]) && ($res==0 || ($res==1 && isset($_REQUEST["mandatory_save"])))) {
				//$this->logger->log(__METHOD__ . ". sanitizeSaveRqData: " . json_encode($this->fields));
				$id = $this->fields['id']['value'];
				if(isset($this->fields["id"]["value"])) {
					$id = $this->fields['id']['value'];
					//$this->error['messages'][] = ['title' => "Debug. " . __METHOD__, 'msg' => "id=" . $this->fields['id']['value']];
					
					// открываем транзакцию
					$this->db->begin();
					
					// ищем сущность в БД
					$entityName = $this->entityName;
					$this->entity = $entityName::findFirst( array(
						"conditions" => "id = ?1",
						"bind" => array(1 => $id)
					));
					// если сущность не найдена в БД создается новая сущность
					if($this->entity == false) $this->entity = new $entityName();
					
					// если сущность не нашли и не создали
					if(!$this->entity){
						//$this->logger->log(__METHOD__ . ". Entity not created");
						$this->error['messages'][] = [
							'title' => $this->t->_("msg_error_title"),
							'msg' => "Ошибка обновления данных при попытке сохранения сущности с id=" . $id,
						];
					}
					// нашли или создали
					else {
						//$this->logger->log(__METHOD__ . ". Entity created");
						// наполняем поля
						// TODO. Необходимо переименовать функцию fillModelFieldsFromSaveRq в fillEntityFromFields
						$this->fillModelFieldsFromSaveRq();
						//$this->logger->log(__METHOD__ . ". Fields: " . json_encode($this->fields));
						//$this->logger->log(__METHOD__ . ". Entity: " . json_encode($this->entity));
						
						if($id<0){
							$this->logger->log(__METHOD__ . ". entity->create()");
							if($this->entity->create() == false) {
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
							//$this->logger->log(__METHOD__ . ". entity->update()");
							//$this->logger->log(__METHOD__ . ". this->entity = " . json_encode($this->entity));
							//$this->logger->log(__METHOD__ . ". Entity: " . json_encode($this->entity));
							if($this->entity->update() == false) {
								//$this->db->rollback();
								$dbMessages = '';
								foreach ($this->entity->getMessages() as $message) {
									$dbMessages .= "<li>" . $message . "</li>";
								}
								$this->error['messages'][] = [
									'title' => "Ошибка обновления данных (id=" . $id . ')',
									'msg' => "<ul>" . $dbMessages . "</ul>"
								];
							}
						}
					}
				}
				else {
					$this->error['messages'][] = [
						'title' => $this->t->_("msg_error_title"),
						'msg' => 'Не получено значение идентификатора сущности (id)',
					];
					$this->logger->log(__METHOD__ . ". Не установлено значение fields['id']['value']");
					$this->logger->log(__METHOD__ . ". Строка, выбранная из БД (rows): " . json_encode($rows));
				}
				if(count($this->error['messages'])==0) {
					// если сущность сохранена в БД, то создаем ее связи
					if($this->saveEntityLinksFromSaveRq()) {
						$this->data['scrollers'] = $this->scrollers;
						$this->db->commit();
						$this->updateEntityFieldsFromModelAfterSave();
						if($id<0) $this->data['newID'] = $this->entity->id;
						$this->success['messages'][] = [
							'title' => $this->t->_("msg_success_title"),
							'msg' => $this->t->_("msg_success_entity_saved") . ". ID = " . $this->entity->id,
						];
					}
					else $this->db->rollback();
				}
			}
		}
		else {
			$this->error['messages'][] = [
				'title' => $this->t->_("msg_error_title"),
				'msg' => $this->t->_("msg_error_post_is_expected"),
			];
		}
		//$data['dbg'] = $id;
		$this->data['item'] = $this->entity;	
		$this->data['rq'] = $this->rq; // исходные параметры запроса для отладки
		if(isset($this->error['messages']) && count($this->error['messages'])>0) $this->data['error'] = $this->error;
		else if(isset($this->success['messages']) && count($this->success['messages'])>0) $this->data['success'] = $this->success;
		if(count($this->checkResult)>0) $this->data['checkResult'] = $this->checkResult;
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
		$entityName = $this->entityName;
		$this->entity = $entityName::findFirst( array(
			"conditions" => "id = ?1",
			"bind" => array(1 => $this->filter_values["id"])
		));
		
		// если сущность не нашли
		if($this->entity == false){
			$this->error['messages'][] = [
				'title' => $this->t->_("msg_error_title"),
				'msg' => "Запись с id=" . $this->filter_values["id"] . ' не найдена'
			];
		}
		// нашли
		else {
			// открываем транзакцию
			$this->db->begin();
			
			// удаляем сущность
			$this->deleteEntity($this->entity);
			
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
	public function deleteEntity($entity) {
		// проверяем возможность удаления по связанным сущностям и удаляем возможные ссылки на сущность из связывающих таблиц
		if($this->deleteEntityLinks($entity)) {
			
			// удаляем саму сущность
			if(!$entity->delete()) {
				$dbMessages = '';
				foreach ($entity->getMessages() as $message) {
					$dbMessages .= "<li>" . $message . "</li>";
				}
				$this->error['messages'][] = [
					//'title' => $this->t->_("msg_error_title"),
					'title' => 'Ошибка',
					'msg' => "Ошибка удаления:<ul>" . $dbMessages . "</ul>",
				];
			}
		}
		else {
			$this->error['messages'][] = [
				//'title' => $this->t->_("msg_error_title"),
				//'msg' => $this->t->_("msg_error_delete_fail"),
				'title' => 'Ошибка',
				'msg' => 'Неуспешное удаление',
			];
		}
		return ['error' => $this->error, 'success' => $this->success];
	}
	
	/* 
	* Общий метод, формирующий дескриптор сущности для полной сущности (со всем связями, файлами и пр.)
	*/
	// TODO. Надо начало функции заменить на вызов initServerSideData()
	public function createDescriptor() {
		$this->sanitizeRqFilters();
		
		$this->fillOperations();
		
		$this->initFields();
		//$this->logger->log("initFields");
		
		// если запрошена конкретная сущность
		if($this->filter_values["id"] != "" && $this->filter_values["id"] != null) {
			// строим запрос к БД на выборку данных
			$phql = $this->getPhql();
			
			// выбираем данные
			$rows = false;
			$rows = $this->modelsManager->executeQuery($phql);
			
			// наполняем $fields
			//$this->fillFieldsFromRows($rows);
			if(count($rows)==1) {
				$this->fillFieldsFromRow($rows[0]);
				
				// если значнение id не заполнено (не нашли единственную запись в БД)
				// TODO. Надо избавиться от форворда при AJAX-запросах
				if(!isset($this->fields["id"]["value"])) {
					$this->dispatcher->forward([
						'controller' => 'errors',
						'action'     => 'show404',
					]);
					$this->logger->log(__METHOD__ . ". Не установлено значение fields['id']['value']");
					$this->logger->log(__METHOD__ . ". Строка, выбранная из БД (rows): " . json_encode($rows));
					return;
				}
				else {
					// наполняем поля с файлами
					$this->getFiles();
				}
			}
			// если не найдены записи
			else{
				$this->logger->log(__METHOD__ . ". count(rows) = " . count($rows));
				$this->fillNewEntityFields();
				/*$this->error['messages'][] = [
					'title' => $this->t->_("msg_error_title"),
					'msg' => "Выборка данных из БД либо пуста, либо содержит более 1 записи"
				];*/
			}
		}
		// если запрошена пустая сущность (создание сущности)
		else {
			$this->fillNewEntityFields();
			//$this->logger->log(json_encode($this->fields));
		}
			
		$this->fillScrollers();
		
		$this->customizeFields();
		
		$this->createDescriptorObject();
	}
	
	/* 
	* Общий метод, формирующий дескриптор сущности для полной сущности (со всем связями, файлами и пр.)
	*/
	public function initServerSideData() {
		$this->sanitizeRqFilters();
		
		$this->initFields();
		//$this->logger->log("initFields");
		
		// если запрошена конкретная сущность
		if($this->filter_values["id"] != "" && $this->filter_values["id"] != null) {
			// строим запрос к БД на выборку данных
			$phql = $this->getPhql();
			
			// выбираем данные
			$rows = false;
			$rows = $this->modelsManager->executeQuery($phql);
			
			// наполняем $fields
			//$this->fillFieldsFromRows($rows);
			if(count($rows)==1) {
				$this->fillFieldsFromRow($rows[0]);
				
				// если значнение id не заполнено (не нашли единственную запись в БД)
				// TODO. Надо избавиться от форворда при AJAX-запросах
				if(!isset($this->fields["id"]["value"])) {
					$this->dispatcher->forward([
						'controller' => 'errors',
						'action'     => 'show404',
					]);
					$this->logger->log(__METHOD__ . ". Не установлено значение fields['id']['value']");
					$this->logger->log(__METHOD__ . ". Строка, выбранная из БД (rows): " . json_encode($rows));
				}
				else {
					// наполняем поля с файлами
					$this->getFiles();
				}
			}
			// если не найдены записи
			else{
				$this->error['messages'][] = [
					'title' => $this->t->_("msg_error_title"),
					'msg' => $this->t->_("msg_error_not_one_rows"),
				];
			}
		}
		// если запрошена пустая сущность (создание сущности)
		else {
			$this->fillNewEntityFields();
			//$this->logger->log(json_encode($this->fields));
		}
		$this->customizeFields();
	}
	
	/* 
	* Предоставляет текст шаблона для рисования сущности, если он есть
	*/
	protected function getTmpl() {
		// передаем шаблон, если он есть
		$tmplFileName = APP_PATH . $this->config->application->templatesDir . $this->controllerNameLC . ".phtml";
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
		$this->getExludeOps();
		
		// получаем действия, доступные пользователю
		// TODO. Вроде tools и так внедрен, как сервис
		//$this->logger->log(__METHOD__ . ". actionName1: " . json_encode($this->actionName));
		$this->getEntityFormOperations();
		//$this->logger->log(__METHOD__ . ". actionName2: " . json_encode($this->actionName));
	}
	
	/* 
	* Предоставляет массив операций для сущности, которые надо исключить
	*/
	protected function getExludeOps() {
		if($this->exludeOps == null) $this->exludeOps[] = array();
		
		// если запрошена пустая сущность, то не нужна операция удаления
		if($this->filter_values["id"] == null || $this->filter_values["id"] == "") {
			$this->exludeOps[] = 'delete';
		}
		
		// если запрошен просмотр, то надо убрать остальные операции с сущностью
		if($this->actionNameLC == "show") {
			$this->exludeOps[] = "edit";
			$this->exludeOps[] = "delete";
		}
	}
	
	/* 
	* Заполняет свойство descriptor данными
	*/
	protected function createDescriptorObject() {
		//$this->logger->log(json_encode($this->fields["name"]));
		$this->descriptor = [
			"controllerName" => $this->controllerNameLC,
			"controllerNameLC" => $this->controllerNameLC,
			"entityNameLC" => $this->entityNameLC,
			"entityName" => $this->entityName,
			"type" => "entity",
			"fields" => $this->fields,
			"scrollers" => $this->scrollers,
			"operations" => $this->operations,
			"filter_values" => $this->filter_values,
			"title" => (isset($this->fields["name"]) && $this->fields["name"]["value"] != '') ? $this->fields["name"]["value"] : 
				(($this->fields["id"]['value'] == '-1') ? $this->t->_("text_" . $this->controllerNameLC . "_new_entity_title") : $this->t->_("text_" . $this->controllerNameLC . "_title")),
			"template" => $this->getTmpl(),
			'data' => $this->data,
			'actionName' => $this->actionName,
		];
		if(isset($this->templateName)) $this->descriptor['templateName'] = $this->templateName;
		if(!$this->request->isAjax()) $this->view->page_header = $this->descriptor['title'];
		//$this->logger->log(__METHOD__ . 'fields_id = ' . json_encode($this->descriptor['fields']['id']));
		//$this->logger->log(__METHOD__ . 'title = ' . $this->descriptor['title']);
		//$this->logger->log(__METHOD__ . 'value=-1 = ' . $this->fields["id"]['value'] == '-1');
	}
	
	/* 
	* Проверяет наличие сущностей из списков изменений в скроллере
	*/
	protected function sanitizeSaveRqDataCheckRelations($rq = null) {
		if(isset($this->rq->scrollers)) {
			// TODO. переделать цикл. Обходить скроллеры $this->scrollers и смотреть есть ли что в запросе - остальное игнорить
			foreach($this->rq->scrollers as $scrollerName => $scroller) {
				if(array_key_exists($scrollerName, $this->scrollers)) {
					$entities = false;
					$linkEntityName = $this->scrollers[$scrollerName]['linkEntityName'];
					
					// добавляемые связи
					if(isset($this->rq->scrollers->$scrollerName->added_items) && count($this->rq->scrollers->$scrollerName->added_items) > 0) {
						$this->scrollers[$scrollerName]['added_items'] = [];
						foreach($this->rq->scrollers->$scrollerName->added_items as $id) {
							$id = $this->filter->sanitize(urldecode($id), ['trim', "int"]);
							if($id != '') $this->scrollers[$scrollerName]['added_items'][] = $id;
							else {
								$this->error['messages'][] = [
									'title' => $this->t->_("msg_error_title"),
									'msg' => $scrollerName . ". Передан некорректный идентификатор"
								];
								return false;
							}
						}
						if($this->scrollers[$scrollerName]['relationType'] == 'n') {
							// TODO. При id < 0 необходимо вызывать метод sanitizeSaveRqData связанной сущности для проверки данных еще не созданной сущности
							// проверяем, чтобы ВСЕ привязываемые сущности существовали
							$entities = $linkEntityName::find(["conditions" => "id IN ({added_items:array})", "bind" => [
								//1 => implode(',', $this->scrollers[$scrollerName]['added_items']),
								"added_items" => $this->scrollers[$scrollerName]['added_items'],
							], "for_update" => true]);
							if(!$entities || count($entities) < count($this->scrollers[$scrollerName]['added_items'])) {
								//$this->logger->log("entities = " . json_encode($entities));
								$this->error['messages'][] = [
									'title' => $this->t->_("msg_error_title"),
									'msg' => $scrollerName . ". Одна или более записей связанной таблицы не найдены в БД"
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
									'title' => $this->t->_("msg_error_title"),
									'msg' => $scrollerName . ". Не найдены добавляемые записи1"
								];
								
								return false;
							}
						}
					}
					// удаляемые связи
					if(isset($this->rq->scrollers->$scrollerName->deleted_items) && count($this->rq->scrollers->$scrollerName->deleted_items) > 0) {
						$this->scrollers[$scrollerName]['deleted_items'] = [];
						foreach($this->rq->scrollers->$scrollerName->deleted_items as $id) {
							$id = $this->filter->sanitize(urldecode($id), ['trim', "int"]);
							if($id != '') $this->scrollers[$scrollerName]['deleted_items'][] = $id;
							else {
								$this->error['messages'][] = [
									'title' => $this->t->_("msg_error_title"),
									'msg' => $scrollerName . ". Передан некорректный идентификатор"
								];
								return false;
							}
						}
					}
				}
				else {
					$this->error['messages'][] = [
						'title' => $this->t->_("msg_error_title"),
						'msg' => "Передан неизвестный массив " . $key
					];
					return false;
				}
			}
		}
		return true;
	}
	
	/* 
	* Сохраняет изменения связей сущности с сущностями из списков изменений в скроллере
	*/
	protected function saveRqDataRelations($scrollerName) {
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
							'title' => $this->t->_("msg_error_title"),
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
					//$link->$eID = $this->fields['id']['value'];
					$link->$eID = $this->entity->id;
					$link->$linkTableLinkEntityFieldName = $itemID;
					$this->data['ai'] = $linkTableLinkEntityFieldName;
					$this->data['ai2'] = $eID;
					//$this->logger->log(__METHOD__ . " link = " . json_encode($link));
					//$this->logger->log(__METHOD__ . " linkTableName = " . json_encode($linkTableName));
					//$this->logger->log(__METHOD__ . " linkTableLinkEntityFieldName = " . json_encode($linkTableLinkEntityFieldName));
					//$this->logger->log(__METHOD__ . " this->data = " . json_encode($this->data));
					if($link->create() == false) {
						$dbMessages = '';
						foreach ($link->getMessages() as $message) {
							$dbMessages .= "<li>" . $message . "</li>";
						}
						$this->error['messages'][] = [
							'title' => $this->t->_("msg_error_title"),
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
						'title' => $this->t->_("msg_error_title"),
						'msg' => "Ошибка удаления данных:<ul>" . $dbMessages . "</ul>"
					];
					return false;
				}
			}
			else if($this->scrollers[$scrollerName]['relationType'] == 'nn') {
				$linkTableName = $this->scrollers[$scrollerName]['linkTableName'];
				$linkTableLinkEntityFieldName = $this->scrollers[$scrollerName]['linkTableLinkEntityFieldName'];
				//$this->logger->log("Trulala");
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
								'title' => $this->t->_("msg_error_title"),
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
	protected function sanitizeRqFilters() {
		if(isset($_REQUEST["id"]) && $_REQUEST["id"] != "-1") $this->filter_values["id"] = $this->filter->sanitize(urldecode($_REQUEST["id"]), ["trim", "int"]); 
		else $this->filter_values["id"] = '';
		// указание открыть сущность на конкретной вкладке
		if(isset($_REQUEST["selected_tab"]) && $_REQUEST["selected_tab"] != "") $this->filter_values["selected_tab"] = $this->filter->sanitize(urldecode($_REQUEST["selected_tab"]), ["trim", "string"]); 
		//else $this->filter_values["selected_tab"] = null;
		
		// доп параметры фильтрации, которые могут использоваться для заполнения полей сущности
		//if(isset($_REQUEST["filter_organization_id"])) $this->filter_values["organization_id"] = $this->filter->sanitize(urldecode($_REQUEST["filter_organization_id"]), ["trim", "int"]); 
		
		
		//$this->logger->log(__METHOD__ . filter_values=". " . json_encode($this->filter_values));
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
		$this->initScrollers();
		
		$this->initAccess();
		
		foreach($this->fields as $fieldID => &$field) {	
			// устанавливаем доступы на основе роли из ACL
			$field['access'] = $this->getFieldAccess($fieldID);
			
			// корректируем доступ на основе настроек сущности и действия (независимо от роди)
			if(isset($this->access) && $this->access != null && isset($this->access[$this->actionNameLC]) && isset($this->access[$this->actionNameLC][$fieldID])) $field['access'] = $this->access[$this->actionNameLC][$fieldID];
			
			// предзаполняем списками, если поля доступно для редактирования
			if($field['type'] == 'select' && $field['style'] == 'id' && (!isset($field['access']) || (isset($field['access']) && $field['access'] == $this::editAccess))) {
				$this->fillFieldWithLists($field);
			}
		}
	}
	
	protected function initScrollers() {}
	
	public function initAccess() {
		if(isset($this->access) && isset($this->access["edit"])) $this->access["save"] = $this->access["edit"];
	}
	
	/* 
	* Заполняет свойство fields данными списков из связанных таблиц
	* Переопределяемый метод. Переопределяется при необходимости
	*/
	protected function fillFieldWithLists(&$field) {
		$userRoleID = $this->userData['role_id'];
		
		if(isset($field['linkEntityName']) && $field['linkEntityName'] != null) {
			$linkEntityName = $field['linkEntityName'];
			$alterName = null;
			if(isset($field['linkEntityField']) && $field['linkEntityField'] != null) {
				$alterName = $field['linkEntityField'];
				$conditions = '';
				if($userRoleID == $this->config->application->orgAdminRoleID && strtolower($field['linkEntityName']) == 'userrole') $conditions .= "id IN (" . $this->config->application->orgOperatorRoleID . ", " . $this->config->application->orgAdminRoleID . ")";
				$rows = $linkEntityName::find(['conditions' => $conditions, 'order' => $alterName . ' ASC']);
			}
			else $rows = $linkEntityName::find(['order' => 'name ASC']);
			//$this->logger->log('rows: ' . json_encode($rows));// DEBUG
			$entities = array();
			foreach ($rows as $row) {
				// наполняем массив
				$entities[] = array(
					'id' => $row->id,
					"name" => $alterName ? $this->t->_($row->$alterName) : $row->name,
				);
			}
			//$this->data['asd'] = json_encode($rows);
			$field['values'] = $entities;
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
		//$this->logger->log("fillNewEntityFields: " . json_encode($this->filter_values));
		foreach($this->fields as $fieldID => &$field) {
			$field["value"] = null;
			
			// если поля связывает по id
			if($field['type'] == 'link' || ($field['type'] == 'select' && $field['style'] == 'id')) {
				
				//$field["value"] = null;
				$field["value_id"] = null;
				if(isset($field["newEntityValue"])) {
					$id = null;
					//$this->logger->log(__METHOD__ . '. fieldID=' . $fieldID . '. newEntityValue=' . $field["newEntityValue"]);
					if(is_object($field["newEntityValue"])) $id = $field["newEntityValue"]();
					else $id = $field["newEntityValue"];
					
					if($id != null) {
						$linkEntityName = $field['linkEntityName'];
						$linkEntityField = $field["linkEntityField"];
						$entity = false;
						$entity = $linkEntityName::findFirst( array(
							"conditions" => "id = ?1",
							"bind" => array(1 => $id)
						));
						if(!$entity) {
							//$this->logger->log(__METHOD__ . '. Controller/Action: ' . $this->controllerNameLC . '/' . $this->actionNameLC . '. Default entity "' . $linkEntityName . '" with ID=' . $id . ' in "newEntityValue" for field "' . $fieldID . '" not found');
						}
						else {
							$field["value"] = $entity->$linkEntityField;
							$field["value_id"] = $entity->id;
						}
					}
				}
				else if(isset($field["newEntityID"]) && $field["newEntityID"] != '' && $field["newEntityID"] != null) {
					//$field["value"] = null;
					$field["value_id"] = null;
					if(is_object($field["newEntityID"])) $field["value"] = $field["newEntityID"]();
					//else if(is_numeric($field["newEntityID"])) {
					else if(is_bool($field["newEntityID"]) && $field["newEntityID"] != false) {
						//$this->logger->log(__METHOD__ . '. newEntityID = ' . $field["newEntityID"] . " gettype=" . gettype($field["newEntityID"]));
						//$this->logger->log(__METHOD__ . '. BOOL field_id = ' . $field["id"] . " gettype=" . gettype(ctype_digit($field["newEntityID"])) . " ||| newEntityID=" . $field["newEntityID"] . " ||| is_int=" . ctype_digit($field["newEntityID"]) . " ||| is_bool=" . is_bool($field["newEntityID"]) . " ||| is_object=" . is_object($field["newEntityID"]));
						$filterValueID = strtolower($field["linkEntityName"] . "_id");
						if(isset($this->filter_values[$filterValueID]) && $this->filter_values[$filterValueID] != '' && $this->filter_values[$filterValueID] != null) {
							$linkEntityName = $field["linkEntityName"];
							$linkEntityField = $field["linkEntityField"];
							$entity = false;
							$entity = $linkEntityName::findFirst([
								"conditions" => "id = ?1", 
								"bind" => [
									1 => $this->filter_values[$filterValueID],
								],
							]);
							if($entity) {
								$field["value"] = $entity->$linkEntityField;
								$field["value_id"] = $entity->id;
							}
							else if(!isFieldAccessibleForUser($field) ){
								$this->error['messages'][] = [
									'title' => $this->t->_("msg_error_title"),
									'msg' => "Ошибка формирования данных сущности",
								];
							}
						}
						else $this->logger->log(__METHOD__ . '. filter_values = ' . json_ENCODE($this->filter_values));
					}
					else if(ctype_digit($field["newEntityID"])) {
						//$this->logger->log(__METHOD__ . '. INT field_id = ' . $field["id"] . " gettype=" . gettype(ctype_digit($field["newEntityID"])) . " ||| newEntityID=" . $field["newEntityID"] . " ||| is_int=" . ctype_digit($field["newEntityID"]) . " ||| is_bool=" . is_bool($field["newEntityID"]) . " ||| is_object=" . is_object($field["newEntityID"]));
						$linkEntityName = $field["linkEntityName"];
						$linkEntityField = $field["linkEntityField"];
						$entity = false;
						$entity = $linkEntityName::findFirst([
							"conditions" => "id = ?1", 
							"bind" => [
								1 => (int)$field["newEntityID"],
							],
						]);					
						if($entity) {
							$this->logger->log(__METHOD__ . '. entity = ' . count($entity) . " id=" . $entity->id . " linkEntityField=" . $linkEntityField . " entity_linkEntityField=" . $entity->$linkEntityField);
							$field["value_id"] = $entity->id;
							$search = "_code";
							$val = $linkEntityField;
							//$this->logger->log(__METHOD__ . '. substr = ' . substr($val, strlen($val) - strlen($search)));
							if(substr($val, strlen($val) - strlen($search)) == $search) $field["value"] = $this->t->_("code_" . $entity->$linkEntityField);
							else $field["value"] = $entity->$linkEntityField;
						}
						else $this->logger->log(__METHOD__ . '. qwe = ');
					}
					//$this->logger->log(__METHOD__ . '. fieldID=' . $fieldID . '. newEntityID=' . $field["newEntityID"]);
				}
				//$this->logger->log(__METHOD__ . '. fieldID=' . $fieldID . '. id=' . $id);
			}
			// если поле связывает по тексту
			else if($field['type'] == 'select' && isset($field['style']) && $field['style'] == 'name') {
				$field["value"] = null;
				$value = null;
				if(isset($field["newEntityValue"])) {
					if(is_object($field["newEntityValue"])) $value = $field["newEntityValue"]();
					else $value = $field["newEntityValue"];
				}
				if(isset($field["values"])) {
					if(in_array($value, $field["values"]))  $field["value"] = $value;
					else $this->logger->log(__METHOD__ . '. Controller/Action: ' . $this->controllerNameLC . '/' . $this->actionNameLC . '. Default value "' . $value . '" for field "' . $fieldID . '" not found in inline array "field["values"]"');
				}
				else if($value != null) $this->logger->log(__METHOD__ . '. Controller/Action: ' . $this->controllerNameLC . '/' . $this->actionNameLC . '. Default value "' . $field["newEntityValue"] . '" for field "' . $fieldID . '" is set, but inline array "field["values"]" is not set');
			}
			else if($field['type'] == 'img') {
				// для изображениq значение по умолчанию пока не предусмотрено
			}
			else if($field["type"] == 'period') {
				$period = null;
				$field["value1"] = null;
				$field["value2"] = null;
				
				if(isset($field["newEntityValue"])) {
					if(is_object($field["newEntityValue"])) {
						$period = $field["newEntityValue"]();
						if(isset($period['value1'])) $field["value1"] = $period['value1'];
						if(isset($period['value2'])) $field["value2"] = $period['value2'];
					}
				}
			}
			else if(isset($field["newEntityValue"])) {
				//$this->logger->log(__METHOD__ . '. newEntityValue: ' . $field["newEntityValue"]);
				if(is_object($field["newEntityValue"])) $field["value"] = $field["newEntityValue"]();
				else $field["value"] = $field["newEntityValue"];
			}
			// если предусмотрена выборка по ID, то надо запросить значение в связанной таблице
			else {
				$field["value"] = null;
				$field["value_id"] = null;
			}
			//$this->logger->log(__METHOD__ . '. field_id = ' . $field["id"] . " ||| newEntityID=" . $field["newEntityID"] . " ||| is_int=" . is_int($field["newEntityID"]) . " ||| is_bool=" . is_bool($field["newEntityID"]) . " ||| is_object=" . is_object($field["newEntityID"]));
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
				$entityName = $this->entityName;
				$row = $entityName::findFirst([
					'conditions' => 'id = ?1',
					'bind' => [1 => $this->fields["id"]["value"]],
				]);
				//$this->data['dbg'] = json_encode($row);
				if($row) {
					$files = false;
					$conditions = [];
					if(isset($field["max_count"])) $conditions['limit'] = $field["max_count"];
					$files = $row->getFile($conditions);
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
	* Заполняет свойство scrollers данными списков из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function fillScrollers() {
		$userRoleID = $this->userData['role_id'];
		
		if(isset($this->scrollers)) {
			foreach($this->scrollers as $scrollerControllerNameLC => $scroller) {
				$resource = $this->controllerNameLC . "_" . $scrollerControllerNameLC;
				$action = ($this->acl->isAllowed($userRoleID, $resource, 'edit') ? ($this->actionNameLC == "show" ? 'show' : 'edit') : ($this->acl->isAllowed($userRoleID, $resource, 'show') ? 'show' : null));
				if($action) {
					$scrollerControllerClass = $scroller['controllerClass'];
					$controller = new $scrollerControllerClass();
					$scrollerController = $controller->createDescriptor($this, $scroller['addFilter'](), $action);
					$scrollerController['relationType'] = $this->scrollers[$scrollerControllerNameLC]['relationType'];
					$scrollerController["add_style"] = $scroller['addStyle'];
					$scrollerController["edit_style"]  = $scroller['editStyle'];
					
					$this->scrollers[$scrollerControllerNameLC] = $scrollerController;
				}
				else unset($this->scrollers[$scrollerControllerNameLC]);
			}
		}
	}
	
	/* 
	* Используется после инициализации и наполнения полей для настройки доступных данных. Например, очищает список доступных статусов в зависимости от текущего статуса
	*/
	public function customizeFields() {}
	
	/* 
	* Ищет запись в БД или создает новую
	* Переопределяемый метод.
	*/
	/*protected function createOrFindEntity($id) {
		$entity = false;
		$entity = $this->findEntity($id);
		if($entity == false) {
			$entityName = $this->entityName;
			// создается новая сущность
			return new $entityName();
		}
		else return $entity;
	}*/
	
	/* 
	* Ищет запись в БД
	* Переопределяемый метод.
	*/
	/*protected function findEntity($id) { 
		if($id && $id >= 0) {
			$entityName = $this->entityName;
			return $entityName::findFirst( array(
				"conditions" => "id = ?1",
				"bind" => array(1 => $id)
			));
		}
		else return false;
	}*/
	
	/* 
	* Удаляет ссылки на сущность из связанных таблиц
	* Переопределяемый метод.
	*/
	protected function deleteEntityLinks($entity) { 
		$this->success['messages'][] = [
			//'title' => $this->t->_("msg_error_title"),
			'title' => 'deleteEntityLinks',
			'msg' => "return true",
		];
		return true;
	}
	
	protected function deleteEntityLinks1($entity) {
		if(!isset($entity)) $entity = $this->entity;
		$userRoleID = $this->userData['role_id'];
		
		if(isset($this->scrollers)) {
			foreach($this->scrollers as $scrollerControllerNameLC => $scroller) {
				// TODO. надо проверять,е сть ли у пользователя право удалять свзанные сущности. При этом удалять связи можно, если можно редактировать текущую сущность.
				$resource = $this->controllerNameLC . "_" . $scrollerControllerNameLC;
				if($scroller['relationType'] == 'nn') {
					$linkTableName = $scroller['linkTableName'];
					$rows = $linkTableName::find([
						"conditions" => $scroller['linkEntityField'] . " = ?1",
						"bind" => array(1 => $entity->id)
					]);
					foreach($rows as $row) {
						// удаляем запись
						if ($row->delete() == false) {
							$this->db->rollback();
							$dbMessages = '';
							foreach ($n->getMessages() as $message) {
								$dbMessages .= "<li>" . $message . "</li>";
							}
							$this->error['messages'][] = [
								'title' => 'Не удалось удалить связь из таблицы "' . $linkTableName . '" c ' . $scroller['linkEntityField'] . ' = ' . $entity->id,
								'msg' => "<ul>" . $dbMessages . "</ul>"
							];
							return false;
						}
					}
				}
				elseif($scroller['relationType'] == 'n') {
					$entityControllerClass = $scroller['entityControllerClass'];
					$e = new $entityControllerClass();
					$linkEntityName = $scroller['linkEntityName'];
					$rows = $linkEntityName::find([
						"conditions" => $scroller['linkEntityField'] . " = ?1",
						"bind" => array(1 => $entity->id)
					]);
					foreach($rows as $row) {
						// удаляем расход
						$res = $e->deleteEntity($row);
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
					}
				}
			}
		}
		
		return true;
	}
	
	/* 
	* Обновляет данные сущности после сохранения в БД (например, проставляется дата создания записи)
	* Переопределяемый метод.
	*/
	protected function updateEntityFieldsFromModelAfterSave() {}
	
	/* 
	* Очищает параметры запроса
	* Расширяемый метод.
	*/
	protected function sanitizeSaveRqData() {
		$res = 0;
		// id
		$this->fields['id']['value'] = null;
		if(isset($this->rq->fields->id) && isset($this->rq->fields->id->value)) {
			$val = $this->filter->sanitize(urldecode($this->rq->fields->id->value), ["trim", "int"]);
			if($val != '') $this->fields['id']['value'] = $val;
		}
		
		if($this->fields['id']['value'] == null) {
			$this->error['messages'][] = [
				'title' => $this->t->_("msg_error_title"),
				'msg' => $this->t->_("msg_error_no_id"),
			];
		}
		
		//$this->error['messages'][] = ['title' => "Debug1. " . __METHOD__, 'msg' => "res=" . $res . ". id=" . $this->fields['id']['value']];
		
		// ссылочные поля (select, link, text, textarea, bool)
		foreach($this->fields as $fieldID => &$field) {
			// если поле не было доступно для редактирования, то ничего с клиента сохранять не надо
			//if(!$this->isFieldAccessibleForUser($field)) continue;
			//$this->logger->log(__METHOD__ . ". Accessible field. fieldID = " . $fieldID . ". value = " . $field['value'] . ". value_id = " . $field['value_id']);
			
			// поле есть в запросе и содержит id значения
			if(($field['type'] == 'select' || $field['type'] == 'link')) {
				$field['value'] = null;
				$field['value_id'] = null;
				//$this->logger->log(__METHOD__ . ". fieldID = " . $fieldID . ". value_id = " . $field['value_id']);
				if(isset($this->rq->fields->$fieldID) && isset($this->rq->fields->$fieldID->value_id)) {
					// выбрано не пустое значение 
					if($this->rq->fields->$fieldID->value_id != '') {
						if($this->rq->fields->$fieldID->value_id == '*') $field['value_id'] = '*';
						else $field['value_id'] = $this->filter->sanitize($this->rq->fields->$fieldID->value_id, ['trim', "int"]);
						//$this->logger->log(__METHOD__ . ". fieldID = " . $fieldID . ". value_id = " . $field['value_id']);
						if($field['value_id'] == '' || $field['value_id'] == '*') $field['value_id'] = null;
						else {
							// если select style=id
							if($field['type'] == 'select' && isset($field['style']) && $field['style'] == "id") {
								// ничего делать не надо
							}
							// если select style=name
							else if($field['type'] == 'select'  && isset($field['style']) && $field['style'] == "name" && isset($field['values']) && isset($field['values'][$field['value_id']])) {
								$field['value'] = $field['values'][$field['value_id']];
							}
							else if($field['type'] == 'select') {
								$this->error['messages'][] = [
									'title' => $this->t->_("msg_error_title"),
									'msg' => $this->t->_("msg_check_field_invalid_value", ['field_name' => $field['name']]) . '. value_id=' . $field['value_id'] . ', rq_id=' . $this->rq->fields->$fieldID->value_id,
								];
							}
						}
					}
					else {
						$this->error['messages'][] = [
							'title' => $this->t->_("msg_error_title"),
							'msg' => $this->t->_("msg_check_field_invalid_value", ['field_name' => $field['name']]) . '. value_id2=' . $field['value_id'] . ', rq_id2=' . $this->rq->fields->$fieldID->value_id,
						];
					}
					
					//$this->logger->log(__METHOD__ . ". fieldID = " . $fieldID . ". value_id = " . $field['value_id']);
				}
				//$this->logger->log(__METHOD__ . ". fieldID = " . $fieldID . ". value_id = " . $field['value_id']);
			}
			else if($field['type'] == 'text' || $field['type'] == 'textarea' || $field['type'] == 'email' || $field['type'] == 'password' || $field['type'] == 'date') {
				$field['value'] = null;
				if(isset($this->rq->fields->$fieldID) && isset($this->rq->fields->$fieldID->value)) {
					$field['value'] = $this->filter->sanitize(urldecode($this->rq->fields->$fieldID->value), ["trim", "string"]);
					if($field['value'] == '') $field['value'] = null;
				}
			}
			else if($field['type'] == 'period') {
				$field['value1'] = null;
				$field['value2'] = null;
				if(isset($this->rq->fields->$fieldID)) {
					$this->logger->log(__METHOD__ . '. 1');
					if(isset($this->rq->fields->$fieldID->value1)) {
						$field['value1'] = $this->filter->sanitize(urldecode($this->rq->fields->$fieldID->value1), ["trim", "string"]);
						if($field['value1'] == '') $field['value1'] = null;
						$this->logger->log(__METHOD__ . '. value1=' . $field['value1']);
						//$this->logger->log('val = ' . $this->fields['target_date']['value1']);
					}
					if(isset($this->rq->fields->$fieldID->value2)) {
						$field['value2'] = $this->filter->sanitize(urldecode($this->rq->fields->$fieldID->value2), ["trim", "string"]);
						if($field['value2'] == '') $field['value2'] = null;
						$this->logger->log(__METHOD__ . '. value2=' . $field['value2']);
						//$this->logger->log('val = ' . $this->fields['target_date']['value2']);
					}
				}
			}
			else if($field['type'] == 'bool' || $field['type'] == 'amount') {
				$field['value'] = null;
				if(isset($this->rq->fields->$fieldID) && isset($this->rq->fields->$fieldID->value)) {
					$field['value'] = $this->filter->sanitize($this->rq->fields->$fieldID->value, ["trim", "int"]);
					if($field['value']=='') $field['value'] = null;
				}
			}
			else if($field['type'] == 'recaptcha') {
				$field['value'] = null;
				if(isset($this->rq->fields->$fieldID) && isset($this->rq->fields->$fieldID->value)) {
					$field['value'] = $this->filter->sanitize($this->rq->fields->$fieldID->value, ["trim", "string"]);
					if($field['value']=='') $field['value'] = null;
				}
			}
		}
		//$this->error['messages'][] = ['title' => "Debug2. " . __METHOD__, 'msg' => "res=" . $res . ". id=" . $this->fields['id']['value']];
		
		if(isset($this->scrollers)) if(!$this->sanitizeSaveRqDataCheckRelations()) $res |= 2;
		
		//$res |= $this->check();
		
		return $res;
	}
	
	/* 
	* Проверяет параметры запроса
	* Расширяемый метод.
	*/
	protected function check() {
		//$this->logger->log(__METHOD__ );
		/*$bt = debug_backtrace();
		$this->logger->log(__METHOD__ . "debug_backtrace()");
		foreach($bt as $id => $value) {
			$this->logger->log(__METHOD__ . ". fn: " . $value["function"]);// . ". value = " . implode(PHP_EOL, $value));
		}*/
		
		$res = 0;
		//$this->data["asd"] = [];
		foreach($this->fields as $fieldID => $field) {
			//$this->logger->log(__METHOD__ . ". fieldID = " . $fieldID . ". type = " . $field['type'] . ". access = " . $field['access']);
			// если поле не было доступно для редактирования, то проверять не надо, т.к. клиент не поймет, что не так
			if(!$this->isFieldAccessibleForUser($field)) continue;
			
			//$this->data["asd"][$fieldID] = $field["type"];
			if($field['type'] == 'amount') {
				// проверка на обязательность
				$res |= $this->checkBasicRequire($field);
				
				$res |= $this->checkBasicAmount($field);
			}
			else if($field['type'] == 'email') {
				// проверка на обязательность
				$res |= $this->checkBasicRequire($field);
				
				$res |= $this->checkBasicEmail($field);
			}
			else if($field['type'] == 'text' || $field['type'] == 'textarea') {
				//$this->logger->log(__METHOD__ . ". fieldID = " . $fieldID . ". type = " . $field['type']);
				// проверка на обязательность
				$res |= $this->checkBasicRequire($field);
				
				$res |= $this->checkBasicText($field);
			}
			else if($field['type'] == 'bool') {
				// проверка на обязательность
				$res |= $this->checkBasicRequire($field);
				
				//$res |= $this->checkBasicText($field);
			}
			else if($field['type'] == 'period') {
				// проверка на обязательность
				//$res |= $this->checkBasicRequire($field);
				
				$res |= $this->checkBasicPeriod($field);
			}
			else if($field['type'] == 'select') {
				if(isset($field['style']) && $field['style'] == 'id') {
					$res |= $this->checkBasicLink($field);
				}
				else {
					$res |= $this->checkBasicSelectName($field);
				}
			}
			else if($field['type'] == 'link') {
				$res |= $this->checkBasicLink($field);
			}
			else if($field['type'] == 'recaptcha' && !isset($_REQUEST["check_only"])) {
				$res |= $this->checkBasicRecaptcha($field);
			}
		}
		return $res;
	}
	
	protected function checkBasicRequire($field) {
		$res = 0;
		if(isset($field['required']) && $field['required'] == 1 && (!isset($field['value']) || $field['value'] == null || $field['value'] == '')) {
			$this->checkResult[] = [
				'type' => "warning",
				'msg' => $this->t->_("msg_check_field_mandatory", ['field_name' => $field['name']]),
			];
			$res |=1 ;
		}
		else if(isset($field['required']) && $field['required'] == 2 && (!isset($field['value']) || $field['value'] == null || $field['value'] == '')) {
			$this->checkResult[] = [
				'type' => "error",
				'msg' => $this->t->_("msg_check_field_mandatory", ['field_name' => $field['name']]),
			];
			$res |= 2;
		}
		//$this->logger->log(__METHOD__ . ". fieldID = " . $field['id'] . ". value = " . $field['value'] . '. required = ' . $field['required']);
		return $res;
	}
	
	protected function checkBasicAmount($field) {
		$res = 0;
		if(isset($field['min']) && $field['value'] != null && $field['value'] < $field['min']) {
			$this->checkResult[] = [
				'type' => "error",
				'msg' => $this->t->_("msg_check_field_min_value", ['field_name' => $field['name']]),
			];
			$res |= 2;
		}
		if(isset($field['max']) && $field['value'] != null && $field['value'] > $field['max']) {
			$this->checkResult[] = [
				'type' => "error",
				'msg' => $this->t->_("msg_check_field_max_value", ['field_name' => $field['name']]),
			];
			$res |= 2;
		}
		return $res;
	}
	
	protected function checkBasicEmail($field) {
		$res = 0;
		if($field['value']!=null && strpos($field['value'], "@") === false) {
			$this->checkResult[] = [
				'type' => "error",
				'msg' => $this->t->_("msg_check_field_email_format", ['field_name' => $field['name']]),
			];
			$res |= 2;
		}
		return $res;
	}
	
	protected function checkBasicText($field) {
		$res = 0;
		if(isset($field['min']) && count($field['value']) < $field['min']) {
			$this->checkResult[] = [
				'type' => "error",
				'msg' => $this->t->_("msg_check_field_text_min", ['field_name' => $field['name'], 'field_min' => $field['min']]),
			];
			$res |= 2;
		}
		if(isset($field['max']) && count($field['value']) > $field['max']) {
			$this->checkResult[] = [
				'type' => "error",
				'msg' => $this->t->_("msg_check_field_text_max", ['field_name' => $field['name'], 'field_max' => $field['max']]),
			];
			$res |= 2;
		}
		return $res;
	}
	
	protected function checkBasicPeriod($field) {
		$res = 0;
		if(isset($field['required'])) {
			$reqMode = $field['required'];
			$val1 = $field["value1"];
			$val2 = $field["value2"];
			//$this->logger->log(__METHOD__ . '. val1=' . $val1 . '. val2=' . $val2);
			if($reqMode == 1 && $val1 == null) {
				$this->checkResult[] = [
					'type' => "error",
					'msg' => $this->t->_("msg_check_field_period_1", ["field_name" => $field['name'], "field_name1" => $field['name1']]),
				];
				$res |= 2;
			}
			else if($reqMode == 2 && $val2 == null) {
				$this->checkResult[] = [
					'type' => "error",
					'msg' => $this->t->_("msg_check_field_period_2", ["field_name" => $field['name'], "field_name2" => $field['name2']]),
				];
				$res |= 2;
			}
			else if($reqMode == 3 && $val1 == null && $val2 == null) {
				$this->checkResult[] = [
					'type' => "error",
					'msg' => $this->t->_("msg_check_field_period_any",  ["field_name" => $field['name']]),
				];
				$res |= 2;
				//$this->logger->log(__METHOD__ . '. QQQ val1=' . $val1 . '. val2=' . $val2);
			}
			else if($reqMode == 4 && ($val1 == null || $val2 == null)) {
				$this->checkResult[] = [
					'type' => "error",
					'msg' => $this->t->_("msg_check_field_period_full",  ["field_name" => $field['name']]),
				];
				$res |= 2;
			}
		
			if($val1 != null && $val2 != null) {
				$d1 = new dateTime ($val1); //(new DateTime('now'))->format("Y-m-d")
				$d2 = new dateTime ($val2);
				if($d1 > $d2) {
					$this->checkResult[] = [
						'type' => "error",
						'msg' => $this->t->_("msg_check_field_period_2lt1",  ["field_name" => $field['name']]),
					];
					$res |= 2;
				}
			}
		}
		
		return $res;
	}
	
	protected function checkBasicLink($field) {
		$res = 0;
		// сперва определяем, что вприниципе присутствет id
		if(isset($field['required'])) {
			if($field['required'] == 1 && $field['value_id'] == null) {
				$this->checkResult[] = [
					'type' => "warning",
					'msg' => $this->t->_("msg_check_field_mandatory", ['field_name' => $field['name']]),
				];
				$res |= 1;
			}
			else if($field['required'] == 2 && $field['value_id'] == null) {
				$this->checkResult[] = [
					'type' => "error",
					'msg' => $this->t->_("msg_check_field_mandatory", ['field_name' => $field['name']]),
				];
				$res |= 2;
			}
		}
		// если id есть, то надо найти запись в БД
		if($field['value_id'] != null) {
			$linkEntityName = $field['linkEntityName'];
			$entity = $linkEntityName::findFirst(["conditions" => "id = ?1", "bind" => [1 => $field['value_id']]]);
			if(!$entity) {
				$this->checkResult[] = [
					'type' => "error",
					'msg' => $this->t->_("msg_check_field_not_found_by_id", ['field_name' => $field['name']]),
				];
				$res |= 2;
			}
		}
		return $res;
	}
	
	protected function checkBasicSelectName($field) {
		$res = 0;
		// сперва определяем, что вприниципе присутствет id
		if(isset($field['required'])) {
			if($field['required'] == 1 && $field['value_id'] == null) {
				$this->checkResult[] = [
					'type' => "warning",
					'msg' => $this->t->_("msg_check_field_mandatory", ['field_name' => $field['name']]),
				];
				$res |= 1;
			}
			else if($field['required'] == 2 && $field['value_id'] == null) {
				$this->checkResult[] = [
					'type' => "error",
					'msg' => $this->t->_("msg_check_field_mandatory", ['field_name' => $field['name']]),
				];
				$res |= 2;
			}
		}
		// если id есть, то надо найти запись в перечне возможных значений
		if($field['value_id'] != null) {
			if(isset($field['values']) && isset($field['values'][$field['value_id']])) {
				$field['value'] = $field['values'][$field['value_id']];
			}
			else {
				$this->checkResult[] = [
					'type' => "error",
					'msg' => $this->t->_("msg_check_field_not_in_list", ['field_name' => $field['name']]),
				];
				$res |= 2;
			}
		}
		//$this->logger->log(__METHOD__ . ". fieldID = " . $field['id'] . ". value_id = " . $field['value_id'] . ". value = " . $field['value'] . '. required = ' . $field['required']);
		return $res;
	}
	
	protected function checkBasicRecaptcha($field) {
		$res = 0;
		// сперва определяем, что вприниципе присутствет id
		if($field['value'] == null || $field['value'] == $this->config['application']['reCaptchaPublicKey']) {
			$this->checkResult[] = [
				'type' => "error",
				'msg' => $this->t->_("msg_check_field_mandatory", ['field_name' => $field['name']]),
			];
			$res |= 2;
		}
		else {
			// выполняем запрос в google
			$url = 'https://www.google.com/recaptcha/api/siteverify';
			// данные для запроса
			$postData = array(
				'secret' => $this->config['application']['reCaptchaSecretKey'],
				'response' => $field['value'],
				//'remoteip' => $this->request->getClientAddress(),
			);
			
			$result = file_get_contents($url, false, stream_context_create(array(
				'http' => array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => http_build_query($postData)
				)
			)));
			
			$resultJSON = json_decode($result);
			
			if($resultJSON->success !== true) {
				$this->checkResult[] = [
					'type' => "error",
					'msg' => $this->t->_("msg_check_field_recaptcha"),
				];
				$res |= 2;
			}
		}
		//$this->logger->log(__METHOD__ . ". fieldID = " . $field['id'] . ". value_id = " . $field['value_id'] . ". value = " . $field['value'] . '. required = ' . $field['required']);
		return $res;
	}

	// операции
	protected function getEntityFormOperations() {
		$exludeOps = $this->exludeOps;
		$controllerNameLC = $this->controllerNameLC;
		$userRoleID = $this->userData['role_id'];
		$acl = $this->acl;
		$t = $this->t;
		
		if($this->operations == null) $this->operations = array();
		
		// редактирование должно быть доступным, если доступно редактирование самой сущности или ее скроллеров
		if(!in_array('edit', $exludeOps)) {
			$editAllowed = false;
			// кнопки "Сохранить" и "Проверить"
			//$controller->logger->log(__METHOD__ . ". userRoleID = " . $userRoleID . ". controllerNameLC = " . $controllerNameLC);
			if($acl->isAllowed($userRoleID, $controllerNameLC, 'edit')) $editAllowed = true;
			// проверяем скроллеры
			else if(isset($controller->scrollers)) {
				foreach($controller->scrollers as $scrollerNameLC => $scroller) {
					//$controller->logger->log(__METHOD__ . ". scrollers: " .  $controllerNameLC . "_" . $scrollerNameLC);
					if($acl->isAllowed($userRoleID, $controllerNameLC . "_" . $scrollerNameLC, 'edit')) {
						$editAllowed = true;
						break;
					}
				}
			}
			if($editAllowed) {
				$this->operations[] = array(
					'id' => 'save',
					'name' => $t->_('button_save'),
				);
				$this->operations[] = array(
					'id' => 'check',
					'name' => $t->_('button_check'),
				);
			}
		}
		
		// кнопка "Удалить"
		if($acl->isAllowed($userRoleID, $controllerNameLC, 'delete') && !in_array('delete', $exludeOps)) {
			$this->operations[] = array(
				'id' => 'delete',
				'name' => $t->_('button_delete'),
			);
		}
	}
}