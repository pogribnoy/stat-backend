<?php
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\DI;

class ControllerList extends ControllerBase {
	// перечень полей для фильтра (уникальные для разных таблиц)
	public $filter_values = array();
	// дополнительные фильтры, устанавливаемые другими контроллерами
	public $add_filter = array();
	
	// столбцы скроллера
	public $columns;
	
	// сущности скроллера
	public $items = [];
	
	// операции, доступные для скроллера
	public $operations;
	
	// настройки, доступные для скроллера
	public $settings;
	
	// информация о поле и направлении сортировки по умолчанию
	public $defaultSort = [
		"column" => "id",
		"order" => "desc",
	];
	
	// количество новых записей, новых по разным критериям, например для вопросов организаций - кол-во вопросов в статусе "Новый"
	public $newCount = 0;
	
	// максимальное количество записей на странице
	public $max_page_size = 100;
	// количество страниц для скроллеров
	public $pager = ["page_sizes" => [30, 50, 100]];
	
	public function initialize() {
		parent::initialize();
	}
	
	public function indexAction() {
		$this->createDescriptor();
		
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			
			// TODO. Удалить, используется только для отладки спиннера
			if(isset($this->filter_values['id'])) sleep(10);
			
			//$this->logger->log(__METHOD__ . '. descriptor = ' . json_encode($this->descriptor));
			return json_encode($this->descriptor);
		}
		else {
			// передаем в представление имеющиеся данные
			$this->view->descriptor = $this->descriptor;
			return null;
		}
	}
	
	public function editAction() {
		$this->createDescriptor();
		
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			return json_encode($this->descriptor);
		}
		else {
			// передаем в представление имеющиеся данные
			//$this->view->setVar("page_header", $this->t->_('text_'.$this->controllerName.'_title'));
			$this->view->setVar("descriptor", $this->descriptor);
		}
	}
	
	public function showAction() {
		$this->createDescriptor();
		
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			return json_encode($this->descriptor);
		}
		else {
			// передаем в представление имеющиеся данные
			//$this->view->setVar("page_header", $this->t->_('text_'.$this->controllerName.'_title'));
			$this->view->setVar("descriptor", $this->descriptor);
		}
	}
	
	/*public function filterAction() {
		//sleep(2);
		
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		
		$this->createDescriptor();
		
		return json_encode($this->descriptor);
	}*/
	
	public function createDescriptor($controller = null, $add_filters = null, $action = null) {
		if($controller == null) $this->controller = $this;
		else {
			// данный метод вызван из внешнего контроллера, поэтому надо проинициализировать внутренние сущности
			$this->controller = $controller;
			$this->controllerNameLC = strtolower($this->controllerName);
			if($action) $this->actionName = $action;
			else $this->actionName = $controller->actionName;
			$this->actionNameLC = strtolower($this->actionName);
			$this->entityNameLC = strtolower($this->entityName);
			
			$this->namespace = __NAMESPACE__;
			//$this->controller->t = $this->controller->translator->addTranslation($this->controllerNameLC);
			// фильтр для значений
			$this->filter = new Phalcon\Filter();
			// инициализируем лог
			$this->logger = new FileAdapter(APP_PATH . '/app/logs/' . $this->controllerNameLC /*. "_" . $this->actionName*/ . ".log", array('mode' => 'a'));
			// пример: $this->logger->log(__METHOD__ . ". add_filters['page_size']=" . $add_filters["page_size"]);
			$this->viewCacheKey = $this->controllerName . (isset($this->actionName) ? "_" . $this->actionName : "") . ".html";
			
			//$this->logger->log(__METHOD__ . ". controllerNameLC=" . $this->controllerNameLC);
		}
		
		// читаем настройки
		$this->getSettings();
		
		// формируем список столбцов
		$this->initColumns();
		
		// разбираем доп. фильтры, передаваемые из других контроллеров
		$this->parseAddFilters($add_filters);
		
		// Разбираем значения параметров фильтра из запроса
		$this->sanitizeGetDataRqFilters();
		
		// вспомогательные данные
		$this->fillColumnsWithLists();
		
		$this->fillOperations();
		
		
		// строим запрос к БД на выборку данных
		$phql = $this->getPhql();
		//$this->logger->log(json_encode($this->descriptor));
		
		// выбираем данные с фильтром, сортировкой и лимитом
		$rows = false;
		$this->logger->log(__METHOD__ . ". phql=" . $phql);
		$rows = $this->modelsManager->executeQuery($phql);
		
		// считаем количество записей
		$count = 0;
		if($rows) $count = count($rows);
		
		// наполняем $fields
		$this->fillItemsFromRows($rows);
		
		$this->createDescriptorObject();
		
		// TODO. Возвращать реальное количество страниц
		$this->descriptor["pager"]["total_pages"] = $count/$this->descriptor["filter_values"]["page_size"]+1;
		//$this->descriptor["pager"]["total_pages"] = 4;
		$this->descriptor["items"] = $this->items;
		$this->descriptor["count"] = $count;
		
		return $this->descriptor;
	}
	
	/* 
	* Заполняет свойство filter_values данными из запроса
	* Расширяемый метод.
	*/
	public function getSettings() {
		$this->max_page_size = $this->config->application->tableMaxPageSize;
		$this->pager['page_sizes'] = json_decode($this->config->application->tablePageSizes);
		// если для админа настроено отдельно максимальное значение, то используем его
		if($this->max_page_size > 0) {
			if(!in_array($this->max_page_size, $this->pager['page_sizes'])) $this->pager['page_sizes'][] = $this->max_page_size;
			sort($this->pager['page_sizes']);
		}
		//$this->logger->log(json_encode($this->settings));
		//$this->logger->log($this->max_page_size);
	}
	
	/* 
	* Сохраняет дополнительные фильтры, передаваемые из других контроллеров
	* Переопределяемый метод.
	*/
	public function parseAddFilters($add_filters) {
		if (isset($add_filters)) {
			// доп. фильтры, передаваемые из других контроллеров
			if(isset($add_filters["user_role_id"])) $this->add_filter["user_role_id"] = $this->filter->sanitize($add_filters["user_role_id"], "int");
			if(isset($add_filters["user_id"])) $this->add_filter["user_id"] = $this->filter->sanitize($add_filters["user_id"], "int");
			if(isset($add_filters["organization_id"])) $this->add_filter["organization_id"] = $this->filter->sanitize($add_filters["organization_id"], "int");
		}
		else {
			// TODO. Если с киента не переданы id, то надо проверять на доступность пользователю данного скроллера
			if(isset($_REQUEST["add_filter"])) {
				if(isset($_REQUEST["add_filter"]['organization_id'])) $this->add_filter["organization_id"] = $this->filter->sanitize(urldecode($_REQUEST["add_filter"]['organization_id']), "int"); 
				if(isset($_REQUEST["add_filter"]['user_role_id'])) $this->add_filter["user_role_id"] = $this->filter->sanitize(urldecode($_REQUEST["add_filter"]['user_role_id']), "int"); 
			}
		}
	}
	
	/* 
	* Предоставляет текст шаблона для рисования сущности, если он есть
	*/
	public function getTmpl() {
		// передаем шаблон, если он есть
		$tmplFileName = APP_PATH . $this->config->application->templatesDir . $this->controllerName . ".phtml";
		if (file_exists($tmplFileName)) return file_get_contents($tmplFileName);
		else return null;
	}
	
	/* 
	* Наполняет массив доступных операций для скроллера
	*/
	public function fillOperations() {
		// добавляем действия, доступные пользователям с разными ролями
		// формируем список действий, который не должен быть доступен
		$exludeOps = $this->getExludeOps();
		
		// получаем действия, доступные пользователю
		$this->operations = $this->getScrollerOperations($this->controller, $this->entityNameLC, $this->actionNameLC);
	}
	
	/* 
	* Заполняет свойство descriptor данными
	*/
	public function createDescriptorObject() {
		$this->descriptor = array(
			"controllerName" => $this->controllerNameLC,
			"controllerNameLC" => $this->controllerNameLC,
			"entityNameLC" => $this->entityNameLC,
			"type" => "scroller",
			"columns" => $this->columns,
			"item_operations" => $this->operations["item_operations"], // действия над строками
			"group_operations" => $this->operations["group_operations"], // групповые действия над строками
			"common_operations" => $this->operations["common_operations"], // действия, не связанные с конкретными строками таблицы (общие для всей таблицы)
			"filter_operations" => $this->operations["filter_operations"], // действия для фильтра
			"filter_values" => $this->filter_values,
			"add_filter" => $this->add_filter,
			//"items" => array(), // после получения данных из БД присваивается значение $this->items
			"pager" => $this->pager,
			"title" => $this->controller->t->_("text_" . $this->controllerNameLC . "_title"),
			"add_style" => "entity", //scroller
			"edit_style" => 'modal', //url
			"template" => $this->getTmpl(),
			"newCount" => $this->newCount,
		);
		if(isset($this->notCollapsible)) $this->descriptor["notCollapsible"] = $this->notCollapsible;
		//$this->logger->log(json_encode($this->descriptor));
	}
	
	/* 
	* Формирует текст запроса к БД
	* Дополняемый метод.
	*/
	public function getPhql() {
		// строим запрос к БД на выборку данных
		$phql = $this->getPhqlSelect();
		
		// добавляем параметры фильтрации
		$phql = $this->addFilterValuesToPhql($phql);
		
		// добавляем условия сортировки и лимита
		$phql = $this->addSortLimitToPhql($phql);
		
		$phql = str_replace("<TableName>", $this->entityName, $phql);
		
		return $phql;
	}
	
		/* 
	* Заполняет свойство filter_values данными из запроса
	* Расширяемый метод.
	*/
	public function sanitizeGetDataRqFilters() {
		if(isset($_REQUEST["page"])) $this->filter_values["page"] = $this->filter->sanitize(urldecode($_REQUEST["page"]), ['trim',"int"]); else $this->filter_values["page"] = 1;
		if(isset($_REQUEST["sort"])) $this->filter_values["sort"] = $this->filter->sanitize(urldecode($_REQUEST["sort"]), ['trim',"string"]); else $this->filter_values['sort'] = $this->defaultSort['column'];
		if(isset($_REQUEST["order"])) $this->filter_values["order"] = $this->filter->sanitize(urldecode($_REQUEST["order"]), ['trim',"string"]); else $this->filter_values['order'] = $this->defaultSort['order'];
		if(isset($_REQUEST["page_size"])) {
			$this->filter_values["page_size"] = $this->filter->sanitize(urldecode($_REQUEST["page_size"]), ['trim',"int"]); 
			if($this->filter_values["page_size"]=="" || !in_array($this->filter_values["page_size"], $this->pager['page_sizes'])) $this->filter_values['page_size'] = $this->max_page_size;
		}
		else $this->filter_values['page_size'] = $this->max_page_size;
		
		if(isset($_REQUEST["exclude_ids"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["exclude_ids"]), ["trim", "string"]);
			if($val != '') $this->filter_values["exclude_ids"] =  $val;
		}
		
		// TODO. Сделать цикл по колонкам и проверять, есть ли переданные значения в фильтре
		foreach($this->columns as $columnID => &$column) {
			// нашли необходимое поле и оно фильтруемое
			if(isset($column['filter'])) {
				$filterID = "filter_" . $columnID;
				//$this->logger->log(__METHOD__ . '. columnID = ' . $columnID);
				// если поле есть в фильтре
				if(isset($_REQUEST[$filterID]) && ($column['filter'] == 'text' || $column['filter'] == 'period')) {
					$filterID = "filter_" . $columnID;
					$val = $this->filter->sanitize(urldecode($_REQUEST[$filterID]), ['trim', "string"]);
					if($val != '') $this->filter_values[$columnID] =  $val;
				}
				else if(isset($_REQUEST[$filterID]) && ($column['filter'] == 'number' || $column['filter'] == 'bool')) {
					$filterID = "filter_" . $columnID;
					$val = $this->filter->sanitize(urldecode($_REQUEST[$filterID]), ['trim', "int"]);
					if($val != '') $this->filter_values[$columnID] =  $val;
				}
				else if(isset($_REQUEST[$filterID]) && $column['filter'] == 'select' && isset($column['filter_style']) && $column['filter_style'] == "id") {
					$val = $this->filter->sanitize(urldecode($_REQUEST[$filterID]), ['trim', "int"]);
					if($val != '') $this->filter_values[$columnID] =  $val;
					else if ($_REQUEST[$filterID] == "**") $this->filter_values[$columnID] = "**";
				}
				else if(isset($_REQUEST[$filterID]) && $column['filter'] == 'select' && isset($column['filter_style']) && $column['filter_style'] == "name") {
					$val = $this->filter->sanitize(urldecode($_REQUEST[$filterID]), "string");
					if($val != '') $this->filter_values[$columnID] =  $val;
					else if ($_REQUEST[$filterID] == "**") $this->filter_values[$columnID] = "**";
				}
				
				if(isset($this->filter_values[$columnID])) $column['filter_value'] = $val;
			}
		}
		
		$this->addNonColumnsFilters();
		
		$this->logger->log(__METHOD__ . '. filter_values = ' . json_encode($this->filter_values));
		
		/*if(isset($_REQUEST["filter_organization"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_organization"]), ['trim',"int"]);
			if($val != '') $this->filter_values["organization"] =  $val;
		}*/
		
		/*if(isset($_REQUEST["filter_id"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_id"]), ['trim',"int"]);
			if($val != '') $this->filter_values["id"] =  $val;
		}
		if(isset($_REQUEST["filter_name"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_name"]), "string");
			if($val != '') $this->filter_values["name"] =  $val;
		}
		if(isset($_REQUEST["filter_active"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_active"]), ['trim',"int"]);
			//$this->logger->log("aasdasd = " . $val);
			if($val != '') $this->filter_values["active"] =  $val;
			//$this->logger->log(json_encode($this->filter_values));
		}
		if(isset($_REQUEST["filter_email"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_email"]), ['trim',"int"]);
			if($val != '') $this->filter_values["email"] =  $val;
		}
		if(isset($_REQUEST["filter_contacts"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_contacts"]), "string");
			if($val != '') $this->filter_values["contacts"] =  $val;
		}
		if(isset($_REQUEST["filter_phone"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_phone"]), "string");
			if($val != '') $this->filter_values["phone"] =  $val;
		}
		if(isset($_REQUEST["filter_code"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_code"]), "string");
			if($val != '') $this->filter_values["code"] =  $val;
		}
		if(isset($_REQUEST["filter_value"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_value"]), "string");
			if($val != '') $this->filter_values["value"] =  $val;
		}
		if(isset($_REQUEST["filter_description"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_description"]), "string");
			if($val != '') $this->filter_values["description"] =  $val;
		}
		if(isset($_REQUEST["filter_organization"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_organization"]), ['trim',"int"]);
			if($val != '') $this->filter_values["organization"] =  $val;
		}
		if(isset($_REQUEST["filter_group"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_group"]), "string");
			if($val != '') $this->filter_values["group"] =  $val;
		}
		if(isset($_REQUEST["filter_controller"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_controller"]), "string");
			if($val != '') $this->filter_values["controller"] =  $val;
		}
		if(isset($_REQUEST["filter_action"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_action"]), "string");
			if($val != '') $this->filter_values["action"] =  $val;
		}
		if(isset($_REQUEST["filter_module"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_module"]), "string");
			if($val != '') $this->filter_values["module"] =  $val;
		}
		if(isset($_REQUEST["filter_amount"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_amount"]), "string");
			if($val != '') $this->filter_values["amount"] =  $val;
			//$this->logger->log(__METHOD__ . ". val = " . json_encode($val));
		}
		if(isset($_REQUEST["filter_date"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_date"]), "string");
			if($val != '') $this->filter_values["date"] =  $val;
		}
		if(isset($_REQUEST["filter_settlement"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_settlement"]), "string");
			if($val != '') $this->filter_values["settlement"] =  $val;
		}
		if(isset($_REQUEST["filter_street"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_street"]), "string");
			if($val != '') $this->filter_values["street"] =  $val;
		}
		if(isset($_REQUEST["filter_house"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_house"]), "string");
			if($val != '') $this->filter_values["house"] =  $val;
		}
		if(isset($_REQUEST["filter_executor"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_executor"]), "string");
			if($val != '') $this->filter_values["executor"] =  $val;
		}
		if(isset($_REQUEST["filter_target_date"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_target_date"]), ['trim',"string"]);
			if($val != '') $this->filter_values["target_date"] =  $val;
		}
		if(isset($_REQUEST["filter_created_at"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_created_at"]), ['trim',"string"]);
			if($val != '') $this->filter_values["created_at"] =  $val;
		}
		
		// фильтры по справочникам
		if(isset($_REQUEST["filter_region"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_region"]), ['trim',"int"]);
			if($val != '') $this->filter_values["region"] =  $val;
		}
		if(isset($_REQUEST["filter_expense_type"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_expense_type"]), ['trim',"int"]);
			if($val != '') $this->filter_values["expense_type"] =  $val;
		}
		if(isset($_REQUEST["filter_expense_status"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_expense_status"]), ['trim',"int"]);
			if($val != '') $this->filter_values["expense_status"] =  $val;
		}
		if(isset($_REQUEST["filter_organization"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_organization"]), ['trim',"int"]);
			if($val != '') $this->filter_values["organization"] =  $val;
		}
		if(isset($_REQUEST["filter_street_type"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_street_type"]), ['trim',"int"]);
			if($val != '') $this->filter_values["street_type"] =  $val;
			else {
				$val = $this->filter->sanitize(urldecode($_REQUEST["filter_street_type"]), ['trim',"string"]);
				if($val == '**') $this->filter_values["street_type"] = "**";
			}
		}
		if(isset($_REQUEST["filter_organization_name"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_organization_name"]), "string");
			if($val != '') $this->filter_values["organization_name"] =  $val;
		}
		if(isset($_REQUEST["filter_user_role"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_user_role"]), ['trim',"int"]);
			if($val != '') $this->filter_values["user_role"] =  $val;
		}
		if(isset($_REQUEST["filter_created_by_id"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_created_by_id"]), ['trim',"int"]);
			if($val != '') $this->filter_values["created_by_id"] =  $val;
		}*/
		
		
	}
	
	protected function addNonColumnsFilters() {}

	/* 
	* Добавляет текст запроса к БД параметры фильтрации
	* Расширяемый метод
	*/
	public function addFilterValuesToPhql($phql) {
		//$this->logger->log(__METHOD__ . '. filter_values = ' . json_encode($this->filter_values));
		//$this->logger->log(__METHOD__ . '. email = ' . json_encode($this->columns['email']));
		
		foreach($this->filter_values as $id => $value) {
			// нашли необходимое поле и оно фильтруемое
			if(isset($this->columns[$id]) && isset($this->columns[$id]['filter'])) {
				$column = $this->columns[$id];
				//$this->logger->log(__METHOD__ . '. id = ' . $id);
				
				$newPhql = $this->addSpecificFilterValuesToPhql($phql, $id);
				//$this->logger->log(__METHOD__ . '. newPhql = ' . $newPhql);
				// сортировка не по значению из связанной таблицы, то ставим стандартную сортировку
				if (!$newPhql) {
					// если код - спецслово СУБД, то его надо заключить в квадратные скобки
					$field = $id;
					if($column['filter'] == 'select' && isset($column['filter_style']) && $column['filter_style'] == "id") $field .= '_id';
					if($id == 'group') $field = '[' . $id . ']';
					
					if(isset($column["nullSubstitute"]) && $value == $column["nullSubstitute"]) $phql .= " AND (<TableName>." . $field . " IS NULL OR <TableName>." . $field . " = '' OR <TableName>." . $field . " = '" . $column["nullSubstitute"] . "')";
					else if ($column['filter'] == 'select' && isset($column['filter_style']) && $column['filter_style'] == "id") $phql .= " AND <TableName>." . $field . " = " . $value;
					else if ($column['filter'] == 'bool') $phql .= " AND <TableName>." . $field . " = " . $value;
					else  $phql .= " AND <TableName>." . $field . " LIKE '%" . $value . "%'";
				}
				else $phql = $newPhql;
			}
			//else $this->logger->log(__METHOD__ . '. id2 = ' . $id);
		}
		// используется на фронте для отбора расходов конкретной организации
		if(isset($this->filter_values["organization"])) $phql .= " AND <TableName>.organization_id = '" . $this->filter_values["organization"] . "'";
		// используется на фронте для сброса конкретного фильтра путем выбора значения ""
		if(isset($this->filter_values["street_type"])) {
			if($this->filter_values["street_type"] == "**") {
				$phql .= " AND (<TableName>.street_type_id IS NULL OR <TableName>.street_type_id = '')";
				//if(isset($this->columns['street_type']["nullSubstitute"])) $this->filter_values["street_type_id"] = $this->columns['street_type']["nullSubstitute"];
			}
			else $phql .= " AND <TableName>.street_type_id = '" . $this->filter_values["street_type"] . "'";
		}
		
		
		
		// исключаем записи, которые не нужны
		// TODO. !!!Необходимо парсить массив и собирать заново, чтобы не столкнуться с SQL-инъекцией
		if(isset($this->filter_values["exclude_ids"])) $phql .= " AND <TableName>.id NOT IN (" . $this->filter_values["exclude_ids"] . ")";
		
		/*if(isset($this->filter_values["id"])) $phql .= " AND <TableName>.id LIKE '%" . $this->filter_values["id"] . "%'";
		if(isset($this->filter_values["name"]) && isset($this->columns['name'])) $phql .= " AND <TableName>.name LIKE '%" . $this->filter_values["name"] . "%'";
		if(isset($this->filter_values["email"]) && isset($this->columns['email'])) $phql .= " AND <TableName>.email LIKE '%" . $this->filter_values["email"] . "%'";
		if(isset($this->filter_values["contacts"]) && isset($this->columns['contacts'])) $phql .= " AND <TableName>.contacts LIKE '%" . $this->filter_values["contacts"] . "%'";
		if(isset($this->filter_values["code"]) && isset($this->columns['code'])) $phql .= " AND <TableName>.code LIKE '%" . $this->filter_values["code"] . "%'";
		if(isset($this->filter_values["value"]) && isset($this->columns['value'])) $phql .= " AND <TableName>.value LIKE '%" . $this->filter_values["value"] . "%'";
		if(isset($this->filter_values["description"]) && isset($this->columns['description'])) $phql .= " AND <TableName>.description LIKE '%" . $this->filter_values["description"] . "%'";
		if(isset($this->filter_values["active"]) && isset($this->columns['active'])) $phql .= " AND <TableName>.active =" . $this->filter_values["active"];
		if(isset($this->filter_values["controller"]) && isset($this->columns['controller'])) $phql .= " AND <TableName>.controller LIKE '%" . $this->filter_values["controller"] . "%'";
		if(isset($this->filter_values["action"]) && isset($this->columns['action'])) $phql .= " AND <TableName>.action LIKE '%" . $this->filter_values["action"] . "%'";
		if(isset($this->filter_values["module"]) && isset($this->columns['module'])) $phql .= " AND <TableName>.module LIKE '%" . $this->filter_values["module"] . "%'";
		if(isset($this->filter_values["group"]) && isset($this->columns['group'])) $phql .= " AND <TableName>.[group] LIKE '%" . $this->filter_values["group"] . "%'";
		if(isset($this->filter_values["date"]) && isset($this->columns['date'])) $phql .= " AND <TableName>.date LIKE '%" . $this->filter_values["date"] . "%'";
		if(isset($this->filter_values["created_at"]) && isset($this->columns['created_at'])) $phql .= " AND <TableName>.created_at LIKE '%" . $this->filter_values["created_at"] . "%'";
		if(isset($this->filter_values["organization"])) $phql .= " AND <TableName>.organization_id = '" . $this->filter_values["organization"] . "'";
		
		if(isset($this->filter_values["amount"]) && isset($this->columns['amount'])) {
			if(isset($this->columns['amount']["nullSubstitute"]) && $this->filter_values["amount"] == $this->columns['amount']["nullSubstitute"]) $phql .= " AND (<TableName>.amount IS NULL OR <TableName>.amount = '' OR <TableName>.amount = '" . $this->columns['amount']["nullSubstitute"] . "')";
			else $phql .= " AND <TableName>.amount LIKE '%" . str_replace([".", ",", "-"], "", $this->filter_values["amount"]) . "%'";
		}
		if(isset($this->filter_values["target_date"]) && isset($this->columns['target_date'])) {
			if(isset($this->columns['street']["nullSubstitute"]) && $this->filter_values["target_date"] == $this->columns['target_date']["nullSubstitute"]) $phql .= " AND (<TableName>.target_date_from IS NULL OR <TableName>.target_date_from = '' OR <TableName>.target_date_from = '" . $this->columns['target_date']["nullSubstitute"] . "' OR (<TableName>.target_date_to IS NULL OR <TableName>.target_date_to = '' OR <TableName>.target_date_to = '" . $this->columns['target_date']["nullSubstitute"] . "'))";
			else $phql .= " AND (<TableName>.target_date_from LIKE '%" . $this->filter_values["target_date"] . "%' OR <TableName>.target_date_to LIKE '%" . $this->filter_values["target_date"] . "%')";
		}
		if(isset($this->filter_values["settlement"]) && isset($this->columns['settlement'])) {
			if(isset($this->columns['settlement']["nullSubstitute"]) && $this->filter_values["settlement"] == $this->columns['settlement']["nullSubstitute"]) $phql .= " AND (<TableName>.settlement IS NULL OR <TableName>.settlement = '' OR <TableName>.settlement = '" . $this->columns['settlement']["nullSubstitute"] . "')";
			else $phql .= " AND <TableName>.settlement LIKE '%" . $this->filter_values["settlement"] . "%'";
		}
		if(isset($this->filter_values["street"]) && isset($this->columns['street'])) {
			if(isset($this->columns['street']["nullSubstitute"]) && $this->filter_values["street"] == $this->columns['street']["nullSubstitute"]) $phql .= " AND (<TableName>.street IS NULL OR <TableName>.street = '' OR <TableName>.street = '" . $this->columns['street']["nullSubstitute"] . "')";
			else $phql .= " AND <TableName>.street LIKE '%" . $this->filter_values["street"] . "%'";
		}
		if(isset($this->filter_values["house"]) && isset($this->columns['house'])) {
			if(isset($this->columns['street']["nullSubstitute"]) && $this->filter_values["house"] == $this->columns['house']["nullSubstitute"]) $phql .= " AND (<TableName>.house IS NULL OR <TableName>.house = '' OR <TableName>.house = '" . $this->columns['house']["nullSubstitute"] . "')";
			else $phql .= " AND <TableName>.house LIKE '%" . $this->filter_values["house"] . "%'";
		}
		if(isset($this->filter_values["executor"]) && isset($this->columns['executor'])) {
			if(isset($this->columns['executor']["nullSubstitute"]) && $this->filter_values["executor"] == $this->columns['street_type']["nullSubstitute"]) $phql .= " AND (<TableName>.executor IS NULL OR <TableName>.executor = '' OR <TableName>.executor = '" . $this->columns['executor']["nullSubstitute"] . "')";
			else $phql .= " AND <TableName>.executor LIKE '%" . $this->filter_values["executor"] . "%'";
		}
		if(isset($this->filter_values["street_type_id"])) {
			if($this->filter_values["street_type_id"] == "**") {
				$phql .= " AND (<TableName>.street_type_id IS NULL OR <TableName>.street_type_id = '')";
				//if(isset($this->columns['street_type']["nullSubstitute"])) $this->filter_values["street_type_id"] = $this->columns['street_type']["nullSubstitute"];
			}
			else $phql .= " AND <TableName>.street_type_id = '" . $this->filter_values["street_type_id"] . "'";
		}
		*/
		//else $this->logger->log($this->filter_values["street_type_id"]);
		
		// фильтры по справочникам
		//if(isset($this->filter_values["region"])) $phql .= " AND Region.id = '" . $this->filter_values["region"] . "'";
		//if(isset($this->filter_values["expense_type_id"])) $phql .= " AND ExpenseType.id = '" . $this->filter_values["expense_type_id"] . "'";
		//if(isset($this->filter_values["expense_status_id"])) $phql .= " AND ExpenseStatus.id = '" . $this->filter_values["expense_status_id"] . "'";
		//if(isset($this->filter_values["organization_id"])) $phql .= " AND Organization.id = '" . $this->filter_values["organization_id"] . "'";
		//if(isset($this->filter_values["organization_name"])) $phql .= " AND Organization.name LIKE '%" . $this->filter_values["organization_name"] . "%'";
		//if(isset($this->filter_values["user_role"])) $phql .= " AND UserRole.id = '" . $this->filter_values["user_role"] . "'";
		//if(isset($this->filter_values["created_by_id"])) $phql .= " AND User.id = '" . $this->filter_values["created_by_id"] . "'";
		
		
		//$this->logger->log(__METHOD__ . '. phql = ' . $phql);
		return $phql;
	}
	
	protected function addSpecificFilterValuesToPhql($phql, $id) { return null; }
	
	/* 
	* Добавляет  в текст запроса к БД параметры сортировки и лимита
	*/
	protected function addSortLimitToPhql($phql) {
		$filter_values = $this->filter_values;
		
		foreach($this->columns as $id => $column) {
			if(isset($column['sortable']) && $filter_values['sort'] == $id) {
				//$this->logger->log(__METHOD__ . '. id = ' . $id);
				// нашли колонку, по которой сортируем
				$newPhql = $this->addSpecificSortLimitToPhql($phql, $id);
				//$this->logger->log(__METHOD__ . '. isSpecific = ' . $isSpecific);
				// сортировка не по значению из связанной таблицы, то ставим стандартную сортировку
				if (!$newPhql) $phql .= ' ORDER BY <TableName>.' . $id . ' ' . $filter_values['order'];
				else $phql = $newPhql;
			}
		}
		
		$start = ((integer)$filter_values['page'] - 1) * (integer)$filter_values["page_size"] ;
		$phql .= ' LIMIT '. $start . ', ' . $filter_values["page_size"];
		return $phql;
	}
	
	protected function addSpecificSortLimitToPhql($phql, $id) { return null; }
	
	/* 
	* Заполняет свойство items данными, полученными после выборки из БД
	*/
	public function fillItemsFromRows($rows) {
		//$this->logger->log(json_encode($rows));
		foreach ($rows as $row) $this->fillFieldsFromRow($row);
	}
	
	/* 
	* Предоставляет массив операций для скроллера, которые надо исключить
	*/
	public function getExludeOps() {
		$exludeOps[] = array();
		return $exludeOps;
	}
	
	/* 
	* Предоставляет базовый текст запроса к БД
	* Переопределяемый метод.
	*/
	public function getPhqlSelect() {}
	
	/* 
	* Заполняет свойство items['fields'] данными, полученными после выборки из БД
	* Переопределяемый метод.
	*/
	public function fillFieldsFromRow($row) {}
	
	/* 
	* Заполняет (инициализирует) свойство colmns
	* Переопределяемый метод.
	*/
	public function initColumns() {}	
	
	/* 
	* Заполняет свойство columns данными списков из связанных таблиц для фильтрации
	* Переопределяемый метод.
	*/
	public function fillColumnsWithLists() {}
	
	public function getScrollerOperations($controller, $entityNameLC, $actionName="show") {
		//$entityName = strtolower($entityName);
		$role_id = $controller->userData['role_id'];
		$acl = $controller->acl;
		$t = $controller->t;
		
		
		$showOp = null;
		$editOp = null;
		$sendOp = null;
		$deleteOp = null;
		$addOp = null;
		
		if($acl->isAllowed($role_id, $entityNameLC, 'show')) {
			$showOp = array(
				'id' => 'show',
				'name' => $t->_('button_show'),
			);
		}
		if($acl->isAllowed($role_id, $entityNameLC, 'edit')) {
			$editOp = array(
				'id' => 'edit',
				'name' => $t->_('button_edit'),
			);
		}
		if($acl->isAllowed($role_id, $entityNameLC, 'delete')) {
			$deleteOp = array(
				'id' => 'delete',
				'name' => $t->_('button_delete'),
			);
		}
		if($acl->isAllowed($role_id, $entityNameLC, 'add')) {
			$addOp = array(
				'id' => 'add',
				'name' => $t->_('button_add'),
			);
		}
		
		$operations = array();
		
		// массив операций на основе разрешений, привязанных к одной сущности
		$operations["item_operations"] = array();
		if($showOp) $operations["item_operations"][] = $showOp;
		
		if($actionName !== "show") {
			if($editOp) $operations["item_operations"][] = $editOp;
			if($sendOp) $operations["item_operations"][] = $sendOp;
			if($deleteOp) $operations["item_operations"][] = $deleteOp;
		}
		
		// массив операций на основе разрешений, не привязанных к одной сущности
		$operations["common_operations"] = array();
		if($addOp && $actionName != "show") {
			// для скроллера
			$operations["common_operations"][] = $addOp;
			// для грида
			$operations["common_operations"][] = array(
				'id' => 'select',
				'name' => $t->_('button_select'),
			);
		}
		
		// массив групповых операций
		$operations["group_operations"] = array();
		if($deleteOp && $actionName != "show") {
			$operations["group_operations"][] = $deleteOp;
		}
		
		// массив операций для фильтра
		$operations["filter_operations"] = [[
			'id' => 'apply',
			'name' => $t->_('button_apply')
		],
		[
			'id' => 'clear',
			'name' => $t->_('button_clear')
		]];
		return $operations;
	}
}