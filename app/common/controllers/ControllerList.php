<?php
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\DI;

class ControllerList extends ControllerBase {
	// наименование сущности
	public $entityName;
	
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
			return json_encode($this->descriptor);
		}
		else {
			// передаем в представление имеющиеся данные
			//$this->view->setVar("page_header", $this->t->_('text_'.$this->controllerName.'_title'));
			$this->view->setVar("descriptor", $this->descriptor);
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
	
	public function filterAction() {
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		
		$this->createDescriptor();
		
		return json_encode($this->descriptor);
	}
	
	public function createDescriptor($controller = null, $add_filters = null, $action = null) {
		if($controller == null) $this->controller = $this;
		else {
			$this->controller = $controller;
			if($action) $this->actionName = $action;
			else $this->actionName = $controller->actionName;
			// данный метод вызван из внешнего контроллера, поэтому надо проинициализировать внутренние сущности
			$this->namespace = __NAMESPACE__;
			//$this->dir = __DIR__;
			$this->controller->t = $this->controller->translator->addTranslation($this->controllerName);
			// фильтр для значений
			$this->filter = new Phalcon\Filter();
			// инициализируем лог
			$this->logger = new FileAdapter(APP_PATH . '/app/logs/' . $this->controllerName /*. "_" . $this->actionName*/ . ".log", array('mode' => 'a'));
			// пример: $this->logger->log("add_filters['page_size']=" . $add_filters["page_size"]);
			$this->viewCacheKey = $this->controllerName . (isset($this->actionName) ? "_" . $this->actionName : "") . ".html";
		}
		
		// читаем настройки
		$this->getSettings();
		
		// разбираем доп. фильтры, передаваемые из других контроллеров
		$this->parseAddFilters($add_filters);
		
		// Разбираем значения параметров фильтра из запроса
		$this->sanitizeGetDataRqFilters();
		
		// формируем список столбцов
		$this->initColumns();
		
		// вспомогательные данные
		$this->fillColumnsWithLists();
		
		$this->fillOperations();
		
		$this->createDescriptorObject();
		
		// строим запрос к БД на выборку данных
		$phql = $this->getPhql();
		//$this->logger->log(json_encode($this->descriptor));
		
		// выбираем данные с фильтром, сортировкой и лимитом
		$rows = false;
		$rows = $this->modelsManager->executeQuery($phql);
		
		// считаем количество записей
		$count = 0;
		if($rows) $count = count($rows);
		
		// наполняем $fields
		$this->fillItemsFromRows($rows);
		
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
		/*$this->settings = Setting::find([
			"code IN ({codes:array})",
			"bind" => ["codes" => ["admin_table_limit", "admin_table_page_sizes"]],
			"limit" => 2
		]);
		//, admin_table_page_sizes
		
		// Обход в foreach
		foreach ($this->settings as $set) {
			if($set->code == 'admin_table_limit') $this->max_page_size = $set->value;
			else if($set->code == 'admin_table_page_sizes') $this->pager['page_sizes'] = json_decode($set->value);
		}*/
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
			if(isset($_REQUEST["add_filter"])) {
				if(isset($_REQUEST["add_filter"]['organization_id'])) $this->add_filter["organization_id"] = $this->filter->sanitize(urldecode($_REQUEST["add_filter"]['organization_id']), "int"); 
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
		if(!isset($this->controller->tools)) $this->controller->tools = DI::getDefault()->getTools();
		//var_dump($this->controller->tools);
		$this->operations = $this->controller->tools->getScrollerOperations($this->controller, $this->entityName, $this->actionName);
	}
	
	/* 
	* Заполняет свойство descriptor данными
	*/
	public function createDescriptorObject() {
		$this->descriptor = array(
			"controllerName" => strtolower($this->controllerName),
			"entity" => strtolower($this->entityName),
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
			"title" => $this->controller->t->_("text_".$this->controllerName."_title"),
			"add_style" => "entity", //scroller
			"edit_style" => 'modal', //url
			"template" => $this->getTmpl(),
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
		if(isset($_REQUEST["sort"])) $this->filter_values["sort"] = $this->filter->sanitize(urldecode($_REQUEST["sort"]), ['trim',"string"]); else $this->filter_values['sort'] = "id";
		if(isset($_REQUEST["order"])) $this->filter_values["order"] = $this->filter->sanitize(urldecode($_REQUEST["order"]), ['trim',"string"]); else $this->filter_values['order'] = "DESC";
		if(isset($_REQUEST["page_size"])) {
			$this->filter_values["page_size"] = $this->filter->sanitize(urldecode($_REQUEST["page_size"]), ['trim',"int"]); 
			if($this->filter_values["page_size"]=="" || !in_array($this->filter_values["page_size"], $this->pager['page_sizes'])) $this->filter_values['page_size'] = $this->max_page_size;
		}
		else $this->filter_values['page_size'] = $this->max_page_size;
		
		if(isset($_REQUEST["filter_id"])) {
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
		
		// фильтры по справочникам
		if(isset($_REQUEST["filter_region"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_region"]), ['trim',"int"]);
			if($val != '') $this->filter_values["region"] =  $val;
		}
		if(isset($_REQUEST["filter_expense_type_id"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_expense_type_id"]), ['trim',"int"]);
			if($val != '') $this->filter_values["expense_type_id"] =  $val;
		}
		if(isset($_REQUEST["filter_expense_status_id"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_expense_status_id"]), ['trim',"int"]);
			if($val != '') $this->filter_values["expense_status_id"] =  $val;
		}
		if(isset($_REQUEST["filter_organization_id"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_organization_id"]), ['trim',"int"]);
			if($val != '') $this->filter_values["organization_id"] =  $val;
		}
		if(isset($_REQUEST["filter_street_type_id"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["filter_street_type_id"]), ['trim',"int"]);
			if($val != '') $this->filter_values["street_type_id"] =  $val;
			else {
				$val = $this->filter->sanitize(urldecode($_REQUEST["filter_street_type_id"]), ['trim',"string"]);
				if($val == '**') $this->filter_values["street_type_id"] = "**";
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
		}
		
		if(isset($_REQUEST["exclude_ids"])) {
			$val = $this->filter->sanitize(urldecode($_REQUEST["exclude_ids"]), "string");
			if($val != '') $this->filter_values["exclude_ids"] =  $val;
		}
	}

	/* 
	* Добавляет текст запроса к БД параметры фильтрации
	* Расширяемый метод
	*/
	public function addFilterValuesToPhql($phql) {
		//$this->logger->log(json_encode($this->filter_values));
		// параметры фильтрации
		if(isset($this->filter_values["id"])) $phql .= " AND <TableName>.id LIKE '%" . $this->filter_values["id"] . "%'";
		// исключаем записи, которые не нужны
		if(isset($this->filter_values["exclude_ids"])) $phql .= " AND <TableName>.id NOT IN (" . $this->filter_values["exclude_ids"] . ")";
		
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
		if(isset($this->filter_values["amount"]) && isset($this->columns['amount'])) {
			if(isset($this->columns['amount']["nullSubstitute"]) && $this->filter_values["amount"] == $this->columns['amount']["nullSubstitute"]) $phql .= " AND (<TableName>.amount IS NULL OR <TableName>.amount = '' OR <TableName>.amount = '" . $this->columns['amount']["nullSubstitute"] . "')";
			else $phql .= " AND <TableName>.amount LIKE '%" . str_replace([".", ",", "-"], "", $this->filter_values["amount"]) . "%'";
		}
		if(isset($this->filter_values["date"]) && isset($this->columns['date'])) $phql .= " AND <TableName>.date LIKE '%" . $this->filter_values["date"] . "%'";
		
		if(isset($this->filter_values["group"]) && isset($this->columns['group'])) $phql .= " AND <TableName>.[group] LIKE '%" . $this->filter_values["group"] . "%'";
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
		//else $this->logger->log($this->filter_values["street_type_id"]);
		
		// фильтры по справочникам
		if(isset($this->filter_values["region"])) $phql .= " AND Region.id = '" . $this->filter_values["region"] . "'";
		if(isset($this->filter_values["organization"])) $phql .= " AND <TableName>.organization_id = '" . $this->filter_values["organization"] . "'";
		if(isset($this->filter_values["expense_type_id"])) $phql .= " AND ExpenseType.id = '" . $this->filter_values["expense_type_id"] . "'";
		if(isset($this->filter_values["expense_status_id"])) $phql .= " AND ExpenseStatus.id = '" . $this->filter_values["expense_status_id"] . "'";
		if(isset($this->filter_values["organization_id"])) $phql .= " AND Organization.id = '" . $this->filter_values["organization_id"] . "'";
		if(isset($this->filter_values["organization_name"])) $phql .= " AND Organization.name LIKE '%" . $this->filter_values["organization_name"] . "%'";
		if(isset($this->filter_values["user_role"])) $phql .= " AND UserRole.id = '" . $this->filter_values["user_role"] . "'";
		if(isset($this->filter_values["created_by_id"])) $phql .= " AND User.id = '" . $this->filter_values["created_by_id"] . "'";
		
		//$this->logger->log(json_encode($phql));
		return $phql;
	}
	
	/* 
	* Добавляет  в текст запроса к БД параметры сортировки и лимита
	*/
	public function addSortLimitToPhql($phql) {
		$filter_values = $this->filter_values;
		$start = ((integer)$filter_values['page'] - 1) * (integer)$filter_values["page_size"] ;
		if($filter_values['sort'] == 'region' || $filter_values['sort'] == 'region_name') $phql .= ' ORDER BY Region.name ' . $filter_values['order'];
		else if($filter_values['sort'] == 'user_role' || $filter_values['sort'] == 'region_name') $phql .= ' ORDER BY UserRole.name ' . $filter_values['order'];
		else if($filter_values['sort'] == 'region_name') $phql .= ' ORDER BY Region.name ' . $filter_values['order'];
		else if($filter_values['sort'] == 'expense_type') $phql .= ' ORDER BY ExpenseType.name ' . $filter_values['order'];
		else if($filter_values['sort'] == 'street_type') $phql .= ' ORDER BY StreetType.name ' . $filter_values['order'];
		//else if($filter_values['sort'] == 'street') $phql .= ' ORDER BY <TableName>.street ' . $filter_values['order'];
		//else if($filter_values['sort'] == 'house') $phql .= ' ORDER BY <TableName>.house ' . $filter_values['order'];
		else $phql .= ' ORDER BY <TableName>.' . $filter_values['sort'] . ' ' . $filter_values['order'];
		$phql .= ' LIMIT '. $start . ', ' . $filter_values["page_size"];
		return $phql;
	}
	
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
}