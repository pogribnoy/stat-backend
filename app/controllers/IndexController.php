<?php
class IndexController extends ControllerBase {
	public function initialize() {
		parent::initialize();
		// убираем указание на layout index, чтобы не задваивался вывод, т.к. layout для текущего контроллера и так - index
		$this->view->cleanTemplateAfter();
	}

	public function indexAction() {
		$cacheKey = $this->controllerNameLC . "_" . $this->actionNameLC . "_user_organizations.php";
		$cachedData = $this->dataCache->get($cacheKey);
		
		$dbg = [];
		if ($cachedData === null) {
		
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
			
			$orgs = [];
			if($rows) {
				foreach($rows as $row) {
					$collection = null;
					$file = null;
					$org = [
						'id' => $row->id,
						'name' => $row->name,
						//'img' => $row->file_directory . $row->file_name,
					];
					try {
						// если есть коллекция картинок
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
							/*$dbg[] = [
								//'img' => $row->img,
								'org' => $row->id,
								'col' => $collection->collection_id . "_" . $collection->file_id,
								'file' => $file->id,
							];*/
						}
						// выбираем вопросы к организации, если пользователь имеет к ним доступ
						if($userRoleID == $this->config->application->adminRoleID || $this->acl->isAllowed($userRoleID, 'organization_organizationrequestlist', 'index')) {
							$rows = OrganizationRequest::find([
								'conditions' => "organization_id = ?1 AND status_id = ?2",
								'bind' => [1 => $row->id, 2 => $this->config['application']['requestStatus']['newStatusID']],
							]);
							$org['organizationRequestCount'] = count($rows);
							$org['organizationRequestCountTitleNonZero'] = $this->t->_("text_index_organizationrequestlist_newCountTitleNonZero");
							$org['organizationRequestCountTitleZero'] = $this->t->_("text_index_organizationrequestlist_newCountTitleZero");
						}
					}
					catch(Exception  $exception) {
						$this->logger->error(__METHOD__ . $exception->__toString());
						//$dbg['exception'] = $exception->getMessage();
					}
					$orgs[] = $org;
				}
				$this->view->orgs = $orgs;
			}
			$cachedData = $orgs;
			$this->dataCache->save($cacheKey, $cachedData);
		}
		else $this->view->orgs = $cachedData;
		
		
		/*$c = 'counter';
		if (!isset($this->persistent->$c)) $this->persistent->$c = 0;
		else $this->persistent->$c += 1;
		$dbg['counter'] = $this->persistent->$c;*/
		$this->view->dbg = $dbg;
	}
}
