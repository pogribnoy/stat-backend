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
	
	// шаблон по умолчанию
	public $templateName = "scroller_template";
	
	// контроллер (сущность), в котором размещается данный контроллер
	public $parentController = null;
	
	public function indexAction() {
		$this->createDescriptor();
		
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			
			// TODO. Удалить, используется только для отладки спиннера
			//if(isset($this->filter_values['id'])) sleep(10);
			
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
			$this->view->descriptor = $this->descriptor;
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
			$this->parentController = $controller;
			
			$this->namespace = __NAMESPACE__;
			
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
		//$this->logger->log(__METHOD__ . ". phql=" . $phql);
		$rows = $this->modelsManager->executeQuery($phql);
		
		
		// наполняем $fields
		$this->fillItemsFromRows($rows);
		
		$this->createDescriptorObject();
		
		// считаем количество записей
		$count = 0;
		if($rows) $count = count($rows);
		
		$this->descriptor["pager"]["total_pages"] = intdiv($count, $this->descriptor["filter_values"]["page_size"]) + 1 + ((integer)$this->descriptor["filter_values"]['page'] - 1);
		//$this->descriptor["pager"]["total_pages"] = 1;
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
			if(!in_array($this->max_page_size, $this->pager['page_sizes'])) $this->pager['page_sizes'][] = (int)$this->max_page_size;
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
		if (isset($add_filters) && $add_filters != null) {
			// доп. фильтры, передаваемые из других контроллеров
			if(isset($add_filters["user_role_id"])) $this->add_filter["user_role_id"] = $this->filter->sanitize($add_filters["user_role_id"], ["trim", "int"]);
			if(isset($add_filters["user_id"])) $this->add_filter["user_id"] = $this->filter->sanitize($add_filters["user_id"], ["trim", "int"]);
			if(isset($add_filters["organization_id"])) $this->add_filter["organization_id"] = $this->filter->sanitize($add_filters["organization_id"], ["trim", "int"]);
		}
		else {
			// TODO. Если с киента не переданы id, то надо проверять на доступность пользователю данного скроллера
			if(isset($_REQUEST["add_filter"])) {
				if(isset($_REQUEST["add_filter"]['user_role_id'])) $this->add_filter["user_role_id"] = $this->filter->sanitize(urldecode($_REQUEST["add_filter"]['user_role_id']), ["trim", "int"]); 
				if(isset($_REQUEST["add_filter"]["user_id"])) $this->add_filter["user_id"] = $this->filter->sanitize($_REQUEST["add_filter"]["user_id"], ["trim", "int"]);
				if(isset($_REQUEST["add_filter"]['organization_id'])) $this->add_filter["organization_id"] = $this->filter->sanitize(urldecode($_REQUEST["add_filter"]['organization_id']), ["trim", "int"]); 
			}
		}
	}
	
	/* 
	* Наполняет массив доступных операций для скроллера
	*/
	public function fillOperations() {
		// добавляем действия, доступные пользователям с разными ролями
		// формируем список действий, который не должен быть доступен
		$exludeOps = $this->getExludeOps();
		
		// получаем действия, доступные пользователю
		$this->getScrollerOperations();
	}
	
	/* 
	* Заполняет свойство descriptor данными
	*/
	public function createDescriptorObject() {
		$columns = [];
		foreach($this->columns as $columnID => $column) {
			$publicColumn = [
				'id' => $column['id'],
				'name' => $column['name'],
			];
			if(isset($column['filter'])) $publicColumn['filter'] = $column['filter'];
			if(isset($column['filter_style'])) $publicColumn['filter_style'] = $column['filter_style'];
			if(isset($column['filter_values'])) $publicColumn['filter_values'] = $column['filter_values'];
			if(isset($column['filter_value'])) $publicColumn['filter_value'] = $column['filter_value'];
			if(isset($column['sortable'])) $publicColumn['sortable'] = $column['sortable'];
			if(isset($column['nullSubstitute'])) $publicColumn['nullSubstitute'] = $column['nullSubstitute'];
			if(isset($column['hideble'])) $publicColumn['hideble'] = $column['hideble'];
			if(isset($column['hidden'])) $publicColumn['hidden'] = $column['hidden'];
			
			$columns[$columnID] = $publicColumn;
		}
		
		$this->descriptor = array(
			"controllerName" => $this->controllerNameLC,
			"controllerNameLC" => $this->controllerNameLC,
			"entityNameLC" => $this->entityNameLC,
			"type" => "scroller",
			//"columns" => $this->columns,
			"columns" => $columns,
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
			//"template" => $this->getTmpl(),
			"newCount" => $this->newCount,
			"newCountTitleNonZero" => $this->controller->t->_("text_" . $this->controllerNameLC . "_newCountTitleNonZero"),
			"newCountTitleZero" => $this->controller->t->_("text_" . $this->controllerNameLC . "_newCountTitleZero"),
		);
		if(isset($this->templateName)) $this->descriptor['templateName'] = $this->templateName;
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
		$phql = $this->addSortToPhql($phql);
		$phql = $this->addLimitToPhql($phql);
		
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
				if(isset($_REQUEST[$filterID]) && ($column['filter'] == 'text' || $column['filter'] == 'period' || $column['filter'] == 'email')) {
					$filterID = "filter_" . $columnID;
					$val = $this->filter->sanitize(urldecode($_REQUEST[$filterID]), ['trim', "string"]);
					if($val != '') $this->filter_values[$columnID] =  $val;
					//$this->logger->log(__METHOD__ . '. columnID = ' . $columnID . ' val=' . $val);
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
				
				if(isset($this->filter_values[$columnID])) $column['filter_value'] = $this->filter_values[$columnID];
				//$this->logger->log(__METHOD__ . '. columnID = ' . $columnID . ' filter_values=' . json_encode($this->filter_values));
			}
		}
		
		$this->addNonColumnsFilters();
		
		//$this->logger->log(__METHOD__ . '. filter_values = ' . json_encode($this->filter_values));
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
					// прибавление _id сделано тут, т.к. потом выполняется проверка на "служебные слова"
					if($column['filter'] == 'select' && isset($column['filter_style']) && $column['filter_style'] == "id") $field .= '_id';
					if($field == 'group') $field = '[' . $field . ']';
					
					if(isset($column["nullSubstitute"]) && $value == '**') $phql .= " AND (<TableName>." . $field . " IS NULL OR <TableName>." . $field . " = '' OR <TableName>." . $field . " = '" . $column["nullSubstitute"] . "')";
					else if ($column['filter'] == 'select' && isset($column['filter_style']) && $column['filter_style'] == "id") {
						//if($column['filter'])
						$phql .= " AND <TableName>." . $field . " = " . $value;
					}
					else if ($column['filter'] == 'bool') $phql .= " AND <TableName>." . $field . " = " . $value;
					else  {
					$this->logger->log(__METHOD__ . '. field = ' . $field . ' filterLinkEntityName=' . $column['filterLinkEntityName']);
						if(isset($column['filterLinkEntityName'])) $phql .= " AND " . $column['filterLinkEntityName'] . "." . $field . " LIKE '%" . $value . "%'";
						else $phql .= " AND <TableName>." . $field . " LIKE '%" . $value . "%'";
					}
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
		
		//$this->logger->log(__METHOD__ . '. phql = ' . $phql);
		return $phql;
	}
	
	/* 
	* Добавляет  в текст запроса к БД нестандартные параметры сортировки и лимита. Например, вопрос организации связан с типом улицы через расход, в стандартном варианте Система не знает о промежуточной зависимости
	*/
	protected function addSpecificFilterValuesToPhql($phql, $id) { return null; }
	
	/* 
	* Добавляет  в текст запроса к БД параметры сортировки и лимита
	*/
	protected function addSortToPhql($phql) {
	//protected function addSortLimitToPhql($phql) {
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
		
		//$start = ((integer)$filter_values['page'] - 1) * (integer)$filter_values["page_size"] ;
		//$phql .= ' LIMIT '. $start . ', ' . $filter_values["page_size"];
		return $phql;
	}
	
	protected function addLimitToPhql($phql) {
		$filter_values = $this->filter_values;
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
	public function getPhqlSelect() {
		return "SELECT <TableName>.* FROM <TableName> WHERE 1=1";//<TableName>.deleted_at IS NULL";
	}
	
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
	public function fillColumnsWithLists() {
		$userRoleID = $this->controller->userData['role_id'];
		$cacheKey = $this->controllerNameLC . "_" . $this->actionNameLC . "_filter_lists_" . $userRoleID . ".php";
		$cachedData = $this->dataCache->get($cacheKey);
		
		if ($cachedData === null) {
			$cachedData = [];
			foreach($this->columns as $columnID => &$column) {	
			
				if(isset($column['filter']) && $column['filter'] == 'select' && isset($column['filter_style']) && $column['filter_style'] == 'id' && isset($column['filterLinkEntityName']) && $column['filterLinkEntityName'] != null) {
					$filterLinkEntityName = $column['filterLinkEntityName'];
					$filterLinkEntityFieldID = 'name';
					
					if(isset($column['filterLinkEntityFieldID']) && $column['filterLinkEntityFieldID'] != null) $filterLinkEntityFieldID = $column['filterLinkEntityFieldID'];
					
					$isCodedValues = 0;
					$search = "_code";
					$val = $filterLinkEntityFieldID;
					// если это справочник с кодами вместо наименования, то наименования надо брать из модуля переводчика
					if(strcmp(substr($val, strlen($val) - strlen($search)), $search) == 0) $isCodedValues = 1;
					//$this->logger->log(__METHOD__ . '. substr=' . substr($val, strlen($val) - strlen($search)));
						
					$params = [
						'conditions' => $filterLinkEntityName . '.deleted_at IS NULL',
						'order' => $filterLinkEntityFieldID . ' ASC'
					];
					if(isset($column['filterFillConditions']) && $column['filterFillConditions'] != null && is_object($column['filterFillConditions'])) $params['conditions'] .= ' AND ' . $column['filterFillConditions']();
					
					$rows = $filterLinkEntityName::find($params);
					
					//$this->logger->log('rows: ' . json_encode($rows));// DEBUG
					$filterValues = array();
					foreach ($rows as $row) {
						// наполняем массив
						if($isCodedValues == 1) $name = $this->controller->t->_($row->$filterLinkEntityFieldID);
						else $name = $row->$filterLinkEntityFieldID;
						//$this->logger->log(__METHOD__ . '. filterLinkEntityFieldID=' . $row->$filterLinkEntityFieldID);
						//$this->logger->log(__METHOD__ . '. isCodedValues=' . $isCodedValues);
						
						$filterValues[] = array(
							'id' => $row->id,
							"name" => $name,
						);
					}
					//$this->data['asd'] = json_encode($rows);
					$column['filter_values'] = $filterValues;
					
					// Сохраняем в кэше
					$cachedData[$columnID] = [
						'filter_values' => $filterValues,
					];
				}
			}
			$this->dataCache->save($cacheKey, $cachedData);
		}
		else {
			//$this->logger->log(__METHOD__ . '. cachedData=' . json_encode());
			foreach($cachedData as $columnID => $column) {
				$this->columns[$columnID]['filter_values'] = $column['filter_values'];
			}
		}
	}
	
	public function getScrollerOperations() {
		//$entityName = strtolower($entityName);
		$userRoleID = $this->controller->userData['role_id'];
		$acl = $this->controller->acl;
		$t = $this->controller->t;
		
		$showOp = null;
		$editOp = null;
		$sendOp = null;
		$deleteOp = null;
		$addOp = null;
		
		if($acl->isAllowed($userRoleID, $this->entityNameLC, 'show')) {
			$showOp = $this->createButtonDescriptor('show');
		}
		if($acl->isAllowed($userRoleID, $this->entityNameLC, 'edit')) {
			$editOp = $this->createButtonDescriptor('edit');
		}
		if($acl->isAllowed($userRoleID, $this->entityNameLC, 'delete')) {
			$deleteOp = $this->createButtonDescriptor('delete');
		}
		/*if($acl->isAllowed($userRoleID, $this->entityNameLC, 'add')) {
			$addOp = $this->createButtonDescriptor('add');
		}*/
		//$this->logger->log(__METHOD__ . ". parentController=" . $this->parentController);
		if($this->parentController != null && $this->parentController->actionNameLC != "show") {
			$this->logger->log(__METHOD__ . ". parentController_controllerNameLC=" . $this->parentController->controllerNameLC . ", controllerNameLC=" . $this->controllerNameLC . ", actionNameLC=" . $this->parentController->actionNameLC);
			$resource = $this->parentController->controllerNameLC . "_" . $this->controllerNameLC;
			if($acl->isAllowed($userRoleID, $resource, 'add')) {
				if($this->parentController->scrollers[$this->controllerNameLC]["addStyle"] == 'scroller') $addOp = $this->createButtonDescriptor('select');
				else if($acl->isAllowed($userRoleID, $this->entityNameLC, 'add')) $addOp = $this->createButtonDescriptor('add');
			}
		}
		//else if($acl->isAllowed($userRoleID, $this->entityNameLC, 'add')) {
		else if($this->isAllowedResource($userRoleID, $this->entityNameLC, 'add')) {
			$addOp = $this->createButtonDescriptor('add');
		}
		
		$this->operations = array();
		
		// массив операций на основе разрешений, привязанных к одной сущности
		$this->operations["item_operations"] = array();
		if($showOp) $this->operations["item_operations"][] = $showOp;
		
		if($this->actionNameLC !== "show") {
			if($editOp) $this->operations["item_operations"][] = $editOp;
			if($sendOp) $this->operations["item_operations"][] = $sendOp;
			if($deleteOp) $this->operations["item_operations"][] = $deleteOp;
		}
		
		// массив операций на основе разрешений, не привязанных к конкретной сущности
		$this->operations["common_operations"] = array();
		// добавление записи
		//$this->logger->log(__METHOD__ . ". entityNameLC=" . $this->entityNameLC . ", parentController=" . json_encode($this->parentController) . ", actionNameLC=" . $this->actionNameLC . ", addOp=" . json_encode($addOp));
		if($addOp && ($this->actionNameLC != "show")) {
			//$this->logger->log(__METHOD__ . ". if=" . json_encode($addOp && ($this->actionNameLC != "show")));
			$this->operations["common_operations"][] = $addOp;
			//$this->operations["common_operations"][] = $this->createButtonDescriptor('select');
		}
		//$this->logger->log(__METHOD__ . ". common_operations=" . json_encode($this->operations["common_operations"]));
		
		// массив групповых операций
		$this->operations["group_operations"] = array();
		if($deleteOp && $this->actionNameLC != "show") {
			$this->operations["group_operations"][] = $deleteOp;
		}
		
		// массив операций для фильтра
		$this->operations["filter_operations"] = [
			$this->createButtonDescriptor('apply'),
			$this->createButtonDescriptor('clear'),
		];
	}
	
	public function getFieldValueFromModel($modelFieldValue, $field) {
		return ($modelFieldValue && !(isset($field["nullSubstitute"]) && $modelFieldValue == $field["nullSubstitute"])) ? $modelFieldValue : '';
	}
}