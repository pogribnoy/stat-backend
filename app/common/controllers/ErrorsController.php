<?php
class ErrorsController extends ControllerBase {
    public function initialize() {
        parent::initialize();
    }

    public function show404Action() {
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			$data = array(
				'error' => [
					'messages' => [[
						'title' => $this->t->_("text_page_not_found"),
						'msg' => $this->t->_("text_not_found"),
						'code' => '404'
					]]
				],
				'text_home' => $this->t->_("text_home")
			);
			echo json_encode($data);
		}
		else {
			// Передаем строки в представление
			$this->view->setVars(array(
				'text_page_not_found' => '404 ' . $this->t->_("text_page_not_found"),
				'text_not_found' => $this->t->_("text_not_found"),
				'text_home' => $this->t->_("text_home")
			));
		}
		$this->logger->log("Ошибка 404: " . $this->controllerName . " \\ " . $this->actionName);
    }

    public function show401Action($sourceURL) {
		// Getting a request instance
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			$data = array(
				'error' => [
					'messages' => [[
						'title' => $this->t->_("text_page_unauthorized"),
						'msg' => $this->t->_("text_unauthorized"),
						'code' => '401'
					]]
				],
				'text_home' => $this->t->_("text_home")
			);
			$this->logger->log(json_encode($this->acl));
			echo json_encode($data);
		}
		else {
			// Передаем строки в представление
			$this->view->setVars(array(
				'text_page_unauthorized' => '401 ' . $this->t->_("text_page_unauthorized"),
				'text_unauthorized' => $this->t->_("text_unauthorized"),
				'text_home' => $this->t->_("text_home")
			));
		}
		$this->logger->log("Ошибка 401: " . $this->controllerName . " \\ " . $this->actionName . ' | sourceURL=' . $sourceURL);
    }

    public function show500Action() {
		// Getting a request instance
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			$data = array(
				'error' => [
					'messages' => [[
						'title' => $this->t->_("text_page_system_error"),
						'msg' => $this->t->_("text_system_error"),
						'code' => '500'
					]]
				],
				'text_home' => $this->t->_("text_home")
			);
			echo json_encode($data);
		}
		else {
			// Передаем строки в представление
			$this->view->setVars(array(
				'text_page_system_error' => '500 ' . $this->t->_("text_page_system_error"),
				'text_system_error' => $this->t->_("text_system_error"),
				'text_home' => $this->t->_("text_home")
			));
		}
		$this->logger->log("Ошибка 500: " . $this->controllerName . " \\ " . $this->actionName);
    }
}
