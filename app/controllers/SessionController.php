<?php
class SessionController extends ControllerBase {
    public function initialize() {
		parent::initialize();
		$this->view->disable();
    }
	
    private function _registerSession(User $user) {
        $this->session->set('auth', array(
            'id' => $user->id,
            'name' => $user->name
        ));
    }
	
    public function startAction() {
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		
		$request = $this->request;
		$this->logger->log('requestURI = ' . json_encode($request->getURI ()));
		if (!$request->isAjax()) {
			$this->logger->log("Not AJAX login");
			$data = array(
				'error' => [
					'messages' => [[
						'title' => 'Ошибка авторизации',
						'msg' => "Ожидается AJAX-запрос",
						'code' => '001'
					]],
				]
			);
		}
		else {
			if(isset($_REQUEST["email"])) $email = $this->filter->sanitize(urldecode($_REQUEST["email"]), ['trim', "string"]); else $email = "";
			if(isset($_REQUEST["phone"])) $phone = $this->filter->sanitize(urldecode($_REQUEST["phone"]), ['trim', "string"]); else $phone = "";
			if(isset($_REQUEST["password"])) $password = $this->filter->sanitize(urldecode($_REQUEST["password"]), ['trim', "string"]); else $password = "";
			
			$auth = $this->session->get('auth');
			$this->logger->log("auth = " . json_encode($auth));
			$this->logger->log("REQUEST = " . json_encode($request->get()));
			if(isset($auth)) {
				$data = array(
					'success' => [
						'messages' => [[
							'title' => 'Успешная авторизация',
							'msg' => "",
							'code' => '001'
						]],
						'redirect' => '/index/index'
					]
				);
			}
			else {
				$user = false;
				$user = User::findFirst(array(
					"(email = :email: OR phone = :phone:) AND password = :password: AND active = 1",
					'bind' => array('email' => $email, 'phone' => $phone, 'password' => $password)//sha1($password))
				));
				if($user != false) {
					$this->_registerSession($user);
					//$role = $user->user_role_id;
					$this->logger->log("Вошел пользователь " . $user->id . '(' . $user->name . ')');
					
					$data = array(
						'success' => [
							'messages' => [[
								'title' => 'Успешная авторизация',
								'msg' => "",
								'code' => '001'
							]],
							'redirect' => '/index/index'
						]
					);
				}
				else {
					$this->logger->log("Пользователь (email=" . $email . ", phone=" . $phone . ", password=" . $password . ") НЕ найден");
					$data = array(
						'error' => [
							'messages' => [[
								'title' => 'Ошибка авторизации',
								'msg' => "Пользователь с указанным паролем не найден",
								'code' => '001'
							]],
						],
					);
				}
			}
		}
		return json_encode($data);
    }
	
    public function endAction() {
		$auth = $this->session->get('auth');
		if ($auth){
			// роль надо подтягивать из БД
			$user = User::findFirst(array(
				"conditions" => "id = ?1",
				"bind" => array(1 => $auth['id'])
			));
			if($user) {
				$role = $user->user_role_id;
				$this->logger->log("Вышел пользователь " . $user->id . '(' . $user->name . ')');
			}
			$this->session->remove('auth');
		}
		
		if ($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
			$this->logger->log("Not AJAX delogin");
			$data = array(
				'success' => [
					'messages' => [[
						'title' => 'Успешная деавторизация',
						'msg' => "",
						'code' => '001'
					]],
					'redirect' => '/login/index'
				]
			);
			return json_encode($data);
		}
		else $this->response->redirect("/login/index", true, 301)->sendHeaders();
    }
}
