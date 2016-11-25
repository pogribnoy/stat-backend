<?php
class IndexController extends ControllerBase {
	public function initialize() {
		parent::initialize();
		// ������� �������� �� layout index, ����� �� ����������� �����
		$this->view->cleanTemplateAfter();
	}

	public function indexAction() {
			$this->view->setVar("page_header", $this->t->_('text_'.$this->controllerName.'_title'));
	}
}
