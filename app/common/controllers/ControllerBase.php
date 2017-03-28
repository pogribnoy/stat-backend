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
	// наименование действия в нижнем регистре
	public $actionName;
	// наименование действия в нижнем регистре
	public $actionNameLC;

	
	// фильтр
	public $filter;
	public $descriptor;

	public function initialize() {
		$this->namespace = __NAMESPACE__;
		$this->dir = __DIR__;
		$this->controller = $this;
		// отключаем кеширование представлений
		//$this->view->cache(false);
		// инициализируем лог
		$this->logger = new FileAdapter(APP_PATH . '/app/logs/' . $this->controllerName /*. "_" . $this->actionName*/ . ".log", array('mode' => 'a'));
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
}
