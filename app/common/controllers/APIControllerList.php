<?php
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\DI;

class APIControllerList extends ControllerBase {
	public function indexAction() {
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		$this->createDescriptor();
		
		return json_encode($this->descriptor);
	}
	
	public function editAction() {
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		$this->createDescriptor();
		return json_encode($this->descriptor);
	}
	
	public function showAction() {
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		$this->createDescriptor();
		return json_encode($this->descriptor);
	}
}