<?php
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Mvc\Controller;
use Phalcon\DI;
use Phalcon\Filter;

class ControllerBase extends Controller {
	// фильтр
	public $filter;
	public $descriptor;

	public function initialize() {
		$this->namespace = __NAMESPACE__;
		$this->dir = __DIR__;
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
		$this->actionName = $dispatcher->getActionName();
		//$this->dispatcher = $dispatcher;
		
		// загружаем перевод
		// Получение оптимального языка из браузера
		$this->language = $this->request->getBestLanguage();
		
		$this->translator = DI::getDefault()->getTranslator();
		$this->t = $this->translator->getTranslation($this->language, $this->controllerName);
		
		if(!$this->request->isAjax()) {
			// передаем данные, зависящие от пути, в представление
			// перевод
			$this->view->setVar("t", $this->t);
			// контроллер и действие
			$this->view->setVar("controllerName", $this->controllerName);
			$this->view->setVar("actionName", $this->actionName);
			$this->view->setVar('page_header', $this->t->_('text_' . $this->controllerName . '_title'));
		}
	}

	/*protected function forward($uri) {
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
	}*/
}
