<?php
class IndexController extends ControllerBase {
	public function initialize() {
		parent::initialize();
		// убираем указание на layout index, чтобы не задваивался вывод
		$this->view->cleanTemplateAfter();
	}

	public function indexAction() {
			$this->view->setVar("page_header", $this->t->_('text_'.$this->controllerName.'_title'));
	}
}
