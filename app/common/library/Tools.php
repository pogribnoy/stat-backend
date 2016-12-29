<?php
use Phalcon\Mvc\User\Component;

class Tools extends Component {
	public function beforeExecuteRoute($dispatcher){
		$this->controllerName = $dispatcher->getControllerName();
		$this->actionName = $dispatcher->getActionName();
	}
	
	public function isAdminRole($role){
		if($role == 1 /*админ*/ || $role == 4 /*новостной редактор*/ || $role == 5 /*оператор*/) return true;
		else return false;
	}
	
	// операции
	public function getEntityFormOperations($role_id, $entity, $acl, $t, $exludeOps = null, $actionName="show", $scrollers = null){
		$operations = array();
		if(!$exludeOps) $exludeOps = array();
		
		// массив операций на основе разрешений
		//if($actionName == "edit" || ($scrollers!=null)) {
			if($acl->isAllowed($role_id, $entity, 'save') && !in_array('save', $exludeOps)) {
				$operations[] = array(
					'id' => 'save',
					'name' => $t->_('button_save'),
				);
			}
		//}
		if($acl->isAllowed($role_id, $entity, 'delete') && !in_array('delete', $exludeOps)) {
			$operations[] = array(
				'id' => 'delete',
				'name' => $t->_('button_delete'),
			);
		}
		/*if($acl->isAllowed($role_id, $entity, 'back') && !in_array('back', $exludeOps)) {
			$operations[] = array(
				'id' => 'back',
				'name' => $t->_('button_back'),
			);
		}*/
		if($acl->isAllowed($role_id, $entity, 'scroller') && !in_array('scroller', $exludeOps)) {
			$operations[] = array(
				'id' => 'scroller',
				'name' => $t->_('button_scroller'),
			);
		}
		
		return $operations;
	}
	
	public function getScrollerOperations($role_id, $entity, $acl, $t, $actionName="show"){
		$operations = array();
		
		// массив операций на основе разрешений, привязанных к одной сущности
		$operations["item_operations"] = array();
		if($acl->isAllowed($role_id, $entity, 'show')) {
			$operations["item_operations"][] = array(
				'id' => 'show',
				'name' => $t->_('button_show'),
			);
		}
		if($actionName !== "show") {
			if($acl->isAllowed($role_id, $entity, 'edit')) {
				$operations["item_operations"][] = array(
					'id' => 'edit',
					'name' => $t->_('button_edit'),
				);
			}
		
			if($acl->isAllowed($role_id, $entity, 'delete')) {
				$operations["item_operations"][] = array(
					'id' => 'delete',
					'name' => $t->_('button_delete'),
				);
			}
		}
		
		// массив операций на основе разрешений, не привязанных к одной сущности
		$operations["common_operations"] = array();
		if($acl->isAllowed($role_id, $entity, 'add') && $actionName != "show") {
			// для скроллера
			$operations["common_operations"][] = array(
				'id' => 'add',
				'name' => $t->_('button_add'),
			);
			// для грида
			$operations["common_operations"][] = array(
				'id' => 'select',
				'name' => $t->_('button_select'),
			);
		}
		
		// массив групповых операций
		$operations["group_operations"] = array();
		if($acl->isAllowed($role_id, $entity, 'delete') && $actionName != "show") {
			$operations["group_operations"][] = array(
				'id' => 'delete',
				'name' => $t->_('button_delete'),
			);
		}

		
		// массив операций для фильтра
		$operations["filter_operations"] = array();
		$operations["filter_operations"][] = array(
			'id' => 'apply',
			'name' => $t->_('button_apply')
		);
		$operations["filter_operations"][] = array(
			'id' => 'clear',
			'name' => $t->_('button_clear')
		);
		
		return $operations;
	}
	
	public function isHasAnyAccess($role_id, $controllerName, $acl){
		if($acl->isAllowed($role_id, $controllerName, 'index') || $acl->isAllowed($role_id, $controllerName, 'show') || $acl->isAllowed($role_id, $controllerName, 'edit') || $acl->isAllowed($role_id, $controllerName, 'add') || $acl->isAllowed($role_id, $controllerName, 'save') || $acl->isAllowed($role_id, $controllerName, 'delete')) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function sendEmail($user, $subject, $msg, $msgAlt) {
		return $this->sendEmail1($user, $subject, $msg, $msgAlt);
	}
	
	public function sendEmail1($user, $subject, $msg, $msgAlt) {
		require_once('/PHPMailer/PHPMailerAutoload.php');

		$mail = new PHPMailer();
		$mail->setLanguage('ru', '/PHPMailer/language');
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->CharSet = 'UTF-8';
		//$mail->CharSet = $this->config["email"]["sharset"];
		//$mail->Encoding = '7bit';
		//ini_set('default_charset', 'UTF-8');

		$mail->SMTPDebug = 3;                               // Enable verbose debug output
		$mail->Debugoutput = 'html';
		
		$mail->Host = $this->config["email"]["SMTP"]["host"];  // Specify main and backup SMTP servers
		$mail->SMTPSecure = $this->config["email"]["SMTP"]["secure"];  // Enable TLS encryption, `ssl` also accepted
		$mail->Port = $this->config["email"]["SMTP"]["port"]; // TCP port to connect to
		$mail->Timeout = 5;
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->SMTPOptions = array (
			'ssl' => array(
				'verify_peer'  => false,
				'verify_peer_name' => false,
				//'verify_depth' => 3,
				'allow_self_signed' => true,
				//'peer_name' => 'smtp.yandex.com',
				//'cafile' => '/etc/ssl/ca_cert.pem',
			)
		);
		$mail->Username = $this->config["email"]["username"]; // SMTP username
		$mail->Password = $this->config["email"]["password"]; // SMTP password
		$mail->XMailer  = 'Rashody Goroda';
		
		//$mail->setFrom($this->config["email"]["from"]);
		//$mail->setFrom($this->config["email"]["from"], 'Сайт "Расходы города"');
		$mail->setFrom($this->config["email"]["from"], 'RashodyGoroda');
		$mail->addAddress($user["email"], $user["name"]);     // Add a recipient
		//$mail->addAddress('ellen@example.com');               // Name is optional
		//$mail->addReplyTo('info@example.com', 'Information');
		//$mail->addCC('cc@example.com');
		//$mail->addBCC('bcc@example.com');

		//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		//$mail->isHTML(true);                                    // Set email format to HTML

		$mail->Subject = "Password reminder";//$subject;
		//$mail->Body    = $msg . PHP_EOL;
		$mail->Body    = "Ваш пароль: 123" . PHP_EOL;
		//$mail->Body    = mb_detect_encoding($msg, mb_detect_order(), true) === 'UTF-8' ? $msg : mb_convert_encoding($msg, 'UTF-8');
		//$mail->Body    = iconv('UTF-16', 'UTF-8', $msg . "\r\n.");
		//$mail->Body    = iconv('UTF-8', 'UTF-8', $msg . "\r\n.");
		//$mail->Body    = $msg . ". ";
		//$mail->Body    = $mail->msgHTML('asd Ваш парол: 123');
		//$mail->Encoding = '7bit';
		//$mail->Body = iconv('UTF-8', 'UTF-16', 'asd Ваш пароль: 123' . PHP_EOL);
		//$mail->Body = iconv('auto', 'UTF-8', 'asd Ваш пароль: 123' . PHP_EOL);
		//$mail->Body    =  'asd Ваш пароль: 123' . PHP_EOL;
		//var_dump($mail->Body);
		//$mail->AltBody = $msgAlt;

		//var_dump($mail);
		if(!$mail->send()) {
			//$this->logger->log('Message could not be sent.');
			//$this->logger->log('Mailer Error: ' . $mail->ErrorInfo);
			return false;
		} 
		//else {
			//$this->logger->log('Message has been sent');
		//}
		
		return true;
	}
	
	public function sendEmail2($user, $subject, $msg, $msgAlt){
		// пример использования
		require_once("SendMailSmtpClass.php"); // подключаем класс
		  
		$mailSMTP = new SendMailSmtpClass('rashodygoroda', 'refaliu', 'tls://smtp.yandex.ru', 'rashodygoroda@yandex.ru', 465);
		// $mailSMTP = new SendMailSmtpClass('логин', 'пароль', 'хост', 'имя отправителя');
		  
		// заголовок письма
		$headers= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
		$headers .= "From: " . $this->config["email"]["username"] . " <" . $this->config["email"]["from"] . ">\r\n"; // от кого письмо
		$result =  $mailSMTP->send($user["email"], 'Тема письма', 'Текст письма', $headers); // отправляем письмо
		// $result =  $mailSMTP->send('Кому письмо', 'Тема письма', 'Текст письма', 'Заголовки письма');
		return $result;
		if($result === true){
			//echo "Письмо успешно отправлено";
			return true;
		}else{
			//echo "Письмо не отправлено. Ошибка: " . $result;
			return false;
		}
	}
	
	public function sendEmail3($user, $subject, $msg, $msgAlt){
		require_once("unisenderApi.php"); //подключаем файл класса
		
		$api_key="5wpc9cdcejdgk7qidpuhgug6esru54sjex3i5bqa"; //API-ключ к вашему кабинету
		$uni=new UniSenderApi($api_key); //создаем экземляр класса, с которым потом будем работать
		
		$rootListID = '8561539';
		//$result = $uni->getLists();
		//var_dump($result);
		//$answer = json_decode($result);
		
		$request = [
			'api_key' => $api_key,
			'email' => $user["email"],
			'sender_name' => $this->config["email"]["username"],
			'sender_email' => $this->config["email"]["from"],
			//'sender_email' => 'pogribnoy@gmail.com',
			'subject' => $subject,
			'body' => $msg,
			'list_id' => $rootListID,
			//'attachments[attach.pdf]' => file_get_contents('/path/to/attach/attach.pdf'),
		];
		
		$result = $uni->sendEmail($request);
		$answer = json_decode($result);
		//var_dump($answer);
		if($answer && isset($answer->result) && isset($answer->result->email_id) && $answer->result->email_id != null && $answer->result->email_id != '') return true;
		return "Не получен идентификатор отправленного письма";
	}
}
