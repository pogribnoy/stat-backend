<?php
class LoginController extends ControllerBase {
	public function initialize() {
		parent::initialize();
		
		// устанавливаем макет по умолчанию
		$this->view->cleanTemplateAfter();
	}

	public function indexAction() {
		$this->view->setVar("page_header", $this->t->_("text_site_full_name"));
		$auth = $this->session->get('auth');
	}
}
