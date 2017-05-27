<?php
use Common\Model;
use Phalcon\Acl;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Logger\Adapter\File as FileAdapter;

//use Phalcon\Logger;
//use Resource;

/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class SecurityPlugin extends Plugin {
	public function __construct() {
		$this->logger = new FileAdapter(APP_PATH . "app/logs/secure.log", array('mode' => 'a'));
	}

	public $user = null;

	/**
	 * Returns an existing or new access control list
	 *
	 * @returns AclList
	 */
	public function getAcl() {
		//var_dump($this->loader);
		
		// Создание регистратора с поддержкой записи
		$aclName = "acl_" . $this->config['application']['module'];
		//if (!isset($this->persistent->$aclName) && $this->config->application->cacheACL == 1) {
		//if (!isset($this->persistent->acl)) {
			$roles = array();
			$resources = array();
			
			$acl = new AclList();
			$acl->setDefaultAction(Acl::DENY);
			
			$rs = Resource::find();
			//$urrs = $ur->getUserRoleResource();
			//var_dump($rs);
			if($rs) {
				foreach($rs as $r){
					$resources[$r->id] = array(
						'controller' => $r->controller,
						'action' => $r->action, 
						'group' => $r->group, 
						'module' => $r->module
					);
					$acl->addResource(new Acl\Resource($r->controller), $r->action);
				}
			}
			
			//$user_roles = UserRole::find(array('active = 1'));
			$user_roles = UserRole::find();	// выбираем все, т.к. иначе с неактивной ролью не будет видно даже страниц ошибок (404 или 401) и авторизации
			if($user_roles) {
				//$this->logger->log(json_encode($user_roles));
				//Регистрируем роли
				foreach($user_roles as $ur){
					$role = new Acl\Role($ur->id);
					$acl->addRole($role);
					// роль "Суперпользователь" имеет доступ ко всему, не зависимо от активности
					//var_dump($ur);
					if($ur->id == $this->config->application->adminRoleID) {
						//$this->logger->log('Superuser. Module: ' . $this->config->application->module);
						foreach($resources as $r) {
							if ($r['module'] === $this->config->application->module) $acl->allow($ur->id, $r['controller'], $r['action']);
						}
					}
					// роль "Гость" имеет доступ ко всему базовому, не зависимо от активности
					else if($ur->id == 2) {
						//$this->logger->log('Superuser. Module: ' . $this->config->application->module);
						foreach($resources as $r) {
							if ($r['module'] === $this->config->application->module && $r['group'] == 'base') $acl->allow($ur->id, $r['controller'], $r['action']);
						}
					}
					else if($ur->active == 1){
						//если роль активна, то добавляем все ее ресурсы
						
						// ДОСТУП ВСЕМ КО ВСЕМУ (раскомментировать, для отключения контроля)
						//foreach($resources as $r) $acl->allow($ur->id, $r['controller'], $r['action']);
						
						// добавляем общие ресурсы
						foreach($resources as $r) {
							if ($r['module'] === $this->config->application->module && $r['group'] == 'base') $acl->allow($ur->id, $r['controller'], $r['action']);
						}
						
						// Назначение допусков
						//$this->logger->log(json_encode($ur));
						$urrss = $ur->getUserRoleResource();//getResource();
						//$this->logger->log(json_encode($urrss));
						if($urrss) {
							foreach($urrss as $urrs){
								if(!isset($resources[$urrs->resource_id])) {
									$this->logger->log('В таблице user_role_resource присутствует неудаленная связь role_id = ' . $ur->id . ', resource_id = ' . $urrs->resource_id);
								}
								else {
									if($resources[$urrs->resource_id]['module'] === $this->config->application->module) $acl->allow($ur->id, $resources[$urrs->resource_id]['controller'], $resources[$urrs->resource_id]['action']);
								}
							}
						}
					}
					else { //если роль НЕ активна, то добавляем только базовые ресурсы, не зависимо от модуля
						foreach($resources as $r) if($r['group'] == 'base') $acl->allow($ur->id, $r['controller'], $r['action']);
					}
				}

				//The acl is stored in session, APC would be useful here too
				$this->persistent->$aclName = $acl;
				//$this->persistent->acl = $acl;
			}
		//}
		return $this->persistent->$aclName;
		//return $this->persistent->acl;
	}
	

	/**
	 * This action is executed before execute any action in the application
	 *
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function beforeDispatch(Event $event, Dispatcher $dispatcher) {
		$userData = $this->getUserData();
		$this->logger->log(__METHOD__ . ". ControllerClass = " . $dispatcher->getControllerClass() . ', Action class = ' . $dispatcher->getActionName());
		//$this->logger->log(__METHOD__ . ". sessionID = " . var_dump($this->session->getId()));
		
		//$this->logger->log(__METHOD__ . ". session = " . json_encode($this->session->getOptions()));
		//var_dump($this->session);
		ini_set('session.gc_maxlifetime', $this->config->application->sessionTimeout);
		//$t2 = ini_get('session.gc_maxlifetime');
		
		// проверяем время жизни сессии
		$auth = $this->session->get('auth');
		//$this->logger->log(__METHOD__ . ". Auth = " . json_encode($auth));
		if($auth) {
			$curDateTime = new DateTime('now');
			$sessionLastUpdateTime = isset($auth['sessionLastUpdate']) ? $auth['sessionLastUpdate'] : $curDateTime;
			$timeout = $curDateTime->diff($sessionLastUpdateTime);
			$sessionSeconds = $timeout->days*86400 + $timeout->h*3600 + $timeout->i*60 + $timeout->s;
			$this->logger->log(__METHOD__ . ". Session inactivity = " . $sessionSeconds . " (max: " . $this->config['application']['sessionTimeout'] . ")");
			if($sessionSeconds > $this->config['application']['sessionTimeout']) {
				$this->logger->log(__METHOD__ . ". User (" . ( $this->user!=null && isset($this->user['id']) ? "id=" . $this->user['id'] . ", name=" . $this->user['name'] : "guest") .  "). Session timeout = " . $sessionSeconds . ". Redirect to _/session/end_");
				if ($this->request->isAjax()) {
					$this->view->disable();
					$this->response->setContentType('application/json', 'UTF-8');
					$data = array(
						'error' => [
							'messages' => [[
								'title' => 'Ошибка доступа',
								'msg' => "Время жизни сессии истекло",
								'code' => '001'
							]],
							'redirect' => '/session/end'
						]
					);
					$this->response->setJsonContent(json_encode($data));
				} else {
					$dispatcher->forward(array(
						'controller' => 'session',
						'action'     => 'end',
					));
				}
			}
			$auth['sessionLastUpdate'] = $curDateTime;
			$this->session->set('auth', $auth);
		}
		
		$role = $userData['role_id'];
		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();
		
		$acl = $this->getAcl();

		$allowed = $acl->isAllowed($role, $controller, $action);
		
		$this->logger->log(__METHOD__ . ". Access attempt. user: " . ( $this->user!=null && isset($this->user['id']) ? $this->user['id'] . ' (' . $this->user['name'] . '), ' : 'guest, ') . "role_id: " . $role . ", resource: " . $controller . " \ " . $action . ", URL: " . json_encode($this->request->getURI()) . ", RESULT: " . ($allowed == Acl::ALLOW ? '1' : '0'));
		
		if ($allowed != Acl::ALLOW) {
			// админка, гость
			if($role == $this->config['application']['guestRoleID'] && $this->config['application']['module'] === "backend") {
				if ($this->request->isAjax()) {
					$this->logger->log(__METHOD__ . ". AJAX. Redirect to _/login/index_");
					$this->view->disable();
					$this->response->setContentType('application/json', 'UTF-8');
					$data = array(
						'error' => [
							'messages' => [[
								'title' => 'Ошибка доступа',
								'msg' => "Не права доступа",
								'code' => '001'
							]],
							'redirect' => '/login/index'
						]
					);
					$this->response->setJsonContent(json_encode($data));
				}
				else { 
					$this->logger->log(__METHOD__ . ". HTTP-request");
					if($acl->isAllowed($role, 'login', 'index')) {
						$this->logger->log(__METHOD__ . ". /login/index is accessable. User redirected to /login/index");
						//$this->logger->log(__METHOD__ . ". request = " . var_dump($this->request));
						return $this->response->redirect("/login/index", true, 301)->sendHeaders();
					}
					else {
						$this->logger->log(__METHOD__ . ". /login/index is NOT accessable. Forward to /errors/show401");
						$dispatcher->forward(array(
							'controller' => 'errors',
							'action'     => 'show401',
						));
						return $this->response->redirect("/login/index", true, 301)->sendHeaders();
					}
					//$this->response->redirect("/login/index", true, 301)->sendHeaders();
				}
			}
			// общедоступная часть, не важно, какая роль
			else if($this->config['application']['module'] === "frontend") {
				if ($this->request->isAjax()) {
					$this->logger->log(__METHOD__ . ". AJAX. Redirect to _/index/index_");
					$this->view->disable();
					$this->response->setContentType('application/json', 'UTF-8');
					$data = array(
						'error' => [
							'messages' => [[
								'title' => 'Ошибка доступа',
								'msg' => "Не права доступа",
								'code' => '001'
							]],
							'redirect' => '/index/index'
						]
					);
					$this->response->setJsonContent(json_encode($data));
				}
				else { 
					$this->logger->log(__METHOD__ . ". HTTP-request");
					if($acl->isAllowed($role, 'index', 'index'))	{
						$this->logger->log(__METHOD__ . ". /index/index is accessable. User redirected to /index/index");
						return $this->response->redirect("/index/index", true, 301)->sendHeaders();
					}
					else {
						$this->logger->log(__METHOD__ . ". /index/index is NOT accessable. Forward to /errors/show401");
						$dispatcher->forward(array(
							'controller' => 'errors',
							'action'     => 'show401',
						));
					}
					//$this->response->redirect("/login/index", true, 301)->sendHeaders();
				}
				//$this->logger->log(__METHOD__ . ". NOT allowed. Guest. Redirect to login");
				/*$dispatcher->forward([
					'controller' => 'login',
					'action'     => 'index',
				]);*/
				//$response = new Phalcon\Http\Response();
				//$this->response->redirect('/login/index', true, 301)->sendHeaders();
			}
			// остальные: авторизованный пользователь, в админке
			else {
				if ($this->request->isAjax()) {
					$this->logger->log(__METHOD__ . ". AJAX. Redirect to _/errors/show401_");
					$this->view->disable();
					$this->response->setContentType('application/json', 'UTF-8');
					$data = array(
						'error' => [
							'messages' => [[
								'title' => 'Ошибка доступа',
								'msg' => "Не права доступа",
								'code' => '001'
							]],
							'redirect' => '/errors/show401',
						]
					);
					$this->response->setJsonContent(json_encode($data));
				}
				else { 
					$dispatcher->forward(array(
						'controller' => 'errors',
						'action'     => 'show401',
					));
				}
			}
			//return true;
		}
		//$this->logger->log(__METHOD__ . ". SequrityPlugin returns true");
		return true;
	}
	
	public function getUserData() {
		if($this->user != null) return $this->user;
		else {
			$this->user = array();
			$this->user['role_id'] = $this->config['application']['guestRoleID'];	// по умолчанию - гость
			
			$auth = $this->session->get('auth');
			if ($auth) {
				// роль надо подтягивать из БД
				$u = User::findFirst(array(
					"conditions" => "id = ?1",
					"bind" => array(1 => $auth['id'])
				));
				
				if($u) {
					$this->user['id'] = $u->id;
					$this->user['role_id'] = $u->user_role_id;
					$this->user['name'] = $u->name;
					$this->user['email'] = $u->email;
					
				}
			}
			return $this->user;
		}
	}
}
