<?php
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Mvc\Controller;
use Phalcon\DI;
use Phalcon\Filter;

class ControllerBase extends Controller {
	// наименование контроллера
	public $controllerName;
	// наименование контроллера в нижнем регистре
	public $controllerNameLC;
	// наименование действия
	public $actionName;
	// наименование действия в нижнем регистре
	public $actionNameLC;
	// наименование сущности
	public $entityName;
	// наименование сущности в нижнем регистре
	public $entityNameLC;
	// доступ к полю на просмотр
	const readonlyAccess = 'show';
	// доступ к полю на редактирование
	const editAccess = 'edit';
	// скрытое поле
	const hiddenAccess = 'hidden';
	// запрос, полученный с клиента
	public $rq;

	
	// фильтр
	public $filter;
	// описатель
	public $descriptor;

	public function initialize() {
		$this->namespace = __NAMESPACE__;
		$this->dir = __DIR__;
		$this->controller = $this;
		// отключаем кеширование представлений
		//$this->view->cache(false);
		// инициализируем лог
		$this->logger = new FileAdapter(APP_PATH . '/app/logs/' . $this->controllerNameLC /*. "_" . $this->actionName*/ . ".log", array('mode' => 'a'));
		//$this->logger->log("asd".$this->logger);
		// данные пользователя
		$this->userData = $this->security->getUserData();
		// лист доступа
		$this->acl = $this->security->getAcl();
		
		if(!$this->request->isAjax()) {
			// передаем данные, не зависящие от пути, в представление
			$this->view->setVar("controller", $this);
		}
		
		// устанавливаем макет по умолчанию
		//$this->view->cleanTemplateAfter();
		$this->view->setTemplateAfter('index');
		
		$this->filter = new Filter();
		$this->viewCacheKey = $this->controllerName . "_" . $this->actionName . ".html";
	}
	
	public function beforeExecuteRoute($dispatcher){
		$this->controllerName = $dispatcher->getControllerName();
		$this->controllerNameLC = strtolower($this->controllerName);
		$this->actionName = $dispatcher->getActionName();
		$this->actionNameLC = strtolower($this->actionName);
		$this->entityNameLC = strtolower($this->entityName);
		
		//var_dump(__METHOD__ . ". actionName: " . json_encode($this->actionName));
		//$this->dispatcher = $dispatcher;
		
		// загружаем перевод
		// Получение оптимального языка из браузера
		$this->language = $this->request->getBestLanguage();
		
		$this->translator = DI::getDefault()->getTranslator();
		$this->t = $this->translator->getTranslation($this->language, $this->controllerNameLC);
		
		if(!$this->request->isAjax()) {
			// передаем данные, зависящие от пути, в представление
			// перевод
			$this->view->t = $this->t;
			// контроллер и действие
			$this->view->controllerName = $this->controllerNameLC;
			$this->view->actionName = $this->actionNameLC;
			$this->view->page_header = $this->t->_('text_' . $this->controllerNameLC . '_title');
		}
	}

	protected function forward($uri) {
		$uriParts = explode('/', $uri);
		if(count($uriParts)>2) {
			return $this->dispatcher->forward(
				array(
					'controller' => $uriParts[0],
					'action' => $uriParts[1],
					'params' => array_slice($uriParts, 2)
				)
			);
		}
		else if(count($uriParts)>1) {
			return $this->dispatcher->forward(
				array(
					'controller' => $uriParts[0],
					'action' => $uriParts[1]
				)
			);
		}
		else {		
			return $this->dispatcher->forward(
				array(
					'controller' => $uriParts[0],
					'action' => 'index'
				)
			);
		}
	}
	
	protected function getFieldAccess($fieldID) {
		$userRoleID = $this->userData['role_id'];
		//$this->logger->log(__METHOD__ . ". actionNameLC = " . $this->actionNameLC . ". resource = " . $this->controllerNameLC . "_field_" . $fieldID . ".  role_id=adminRoleID:" . ($userRoleID == $this->config->application->adminRoleID));
		if($this->actionNameLC == 'edit' || $this->actionNameLC == 'save') {
			//$this->logger->log(__METHOD__ . ". actionNameLC = " . $this->actionNameLC);
			if($this->acl->isAllowed($userRoleID, $this->controllerNameLC . "_field_" . $fieldID, 'edit')) { 
				//$this->logger->log(__METHOD__ . ". asd1 = "); 
				return $this::editAccess; 
			}
			else if($this->acl->isAllowed($userRoleID, $this->controllerNameLC . "_field_*", 'edit')) { 
				//$this->logger->log(__METHOD__ . ". asd2 = "); 
				return $this::editAccess; 
			}
			else if($userRoleID == $this->config->application->adminRoleID && ($this->acl->isAllowed($this->config->application->adminRoleID, $this->controllerNameLC . "_field_" . $fieldID, 'edit') || $this->acl->isAllowed($this->config->application->adminRoleID, $this->controllerNameLC . "_field_*", 'edit'))) { 
				//$this->logger->log(__METHOD__ . ". asd3 = "); 
				return $this::editAccess; 
			}
			$this->logger->log(__METHOD__ . ". asd4 = ");
		}
		return $this::readonlyAccess;
	}
	protected function isFieldAccessibleForUser($field) {
		//$this->logger->log(__METHOD__ . ". resource = " . $this->controllerNameLC . "_field_" . $field['id'] . ". access = " . $field['access']);
		if(isset($field["access"]) && $field["access"] == $this::editAccess) return true;
		return false;
	}
}
