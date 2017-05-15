<?php
class IndexController extends ControllerBase {
	public function initialize() {
		parent::initialize();
		// ������� �������� �� layout index, ����� �� ����������� �����
		$this->view->cleanTemplateAfter();
	}

	public function indexAction() {
		$userRoleID = $this->userData['role_id'];
		//$userID = $this->controller->userData['id'];
		$userID = $this->session->get('auth')['id'];
		//$this->view->setVar("page_header", $this->t->_('text_'.$this->controllerName.'_title'));
		//$this->logger->log(__METHOD__ . ". auth = " . $this->session->get('auth')['id']);
		$phql = "SELECT Organization.id, Organization.name, Organization.img FROM Organization JOIN UserOrganization AS uo1 ON uo1.organization_id = Organization.id AND uo1.user_id = " . $userID . " ORDER BY Organization.name DESC";
		
		$rows = false;
		try {
			$rows = $this->modelsManager->executeQuery($phql);
		}
		catch (Exception $exception) {
			$this->logger->error(__METHOD__ . $exception->__toString());
		}
		$dbg = [];
		
		if($rows) {
			$orgs = [];
			foreach($rows as $row) {
				$collection = null;
				$file = null;
				$org = [
					'id' => $row->id,
					'name' => $row->name,
					//'img' => $row->file_directory . $row->file_name,
				];
				try {
					// ���� ���� ��������� ��������
					if($row->img) {
						$collection = FileCollection::findFirst([
							'conditions' => "collection_id = ?1",
							'bind' => [1 => $row->img],
						]);
						
						if($collection) {
							//$file = File::findById("41");
							$file = File::findFirst([
								'conditions' => "id = ?1",
								'bind' => [1 => $collection->file_id],
							]);
							if($file) $org['img'] = $file->directory . $file->name;
						}
						$dbg[] = [
							//'img' => $row->img,
							'org' => $row->id,
							'col' => $collection->collection_id . "_" . $collection->file_id,
							'file' => $file->id,
						];
					}
					// �������� ������� � �����������, ���� ������������ ����� � ��� ������
					if($this->acl->isAllowed($userRoleID, 'organization_organizationrequestlist', 'index')) {
						$rows = OrganizationRequest::find([
							'conditions' => "organization_id = ?1 AND status_id = ?2",
							'bind' => [1 => $row->id, 2 => $this->config['application']['requestStatus']['newStatusID']],
						]);
						$org['organizationRequestCount'] = count($rows);
					}
				}
				catch(Exception  $exception) {
					$this->logger->error(__METHOD__ . $exception->__toString());
					$dbg['exception'] = $exception->getMessage();
				}
				$orgs[] = $org;
			}
			$this->view->orgs = $orgs;
		}
		$this->view->dbg = $dbg;
	}
}
