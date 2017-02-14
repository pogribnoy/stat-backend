<?php
class IndexController extends ControllerBase {
	public function initialize() {
		parent::initialize();
		// ������� �������� �� layout index, ����� �� ����������� �����
		$this->view->cleanTemplateAfter();
	}

	public function indexAction() {
		//$this->view->setVar("page_header", $this->t->_('text_'.$this->controllerName.'_title'));
		
		$phql = "SELECT Organization.id, Organization.name, File.name AS file_name, File.directory AS file_directory FROM Organization JOIN UserOrganization ON UserOrganization.organization_id = Organization.id AND UserOrganization.user_id = " . $this->session->get('auth')['id']/*$this->userData['id']*/ . " LEFT JOIN FileCollection ON FileCollection.collection_id = Organization.img LEFT JOIN File ON File.id = FileCollection.file_id ORDER BY Organization.name DESC";
		
		$rows = false;
		try {
			$rows = $this->modelsManager->executeQuery($phql);
		}
		catch (Exception $exception) {
			$this->logger->log(__METHOD__ . ". Error while execution of PHQL. \n" . $exception->getMessage());
		}
		
		if($rows) {
			$orgs = [];
			foreach($rows as $row) {
				$orgs[] = [
					'id' => $row->id,
					'name' => $row->name,
					'img' => $row->file_directory . $row->file_name,
				];
				
				//$imgs = $row->getFile();
				//if(count($img)>1) $img = $img[0];
				
				//$orgs['img'] = $img->name;
			}
			$this->view->setVar("orgs", $orgs);
		}
	}
}
