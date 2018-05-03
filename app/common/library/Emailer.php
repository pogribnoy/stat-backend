<?php
use Phalcon\Mvc\User\Component;

class Emailer extends Component {
	
	public function sendEmail($user, $subject, $msg, $msgAlt) {
		//echo __METHOD__ . PHP_EOL;
		require_once(APP_PATH . 'app/common/library/PHPMailer/PHPMailerAutoload.php');

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
		$mail->XMailer  = 'Сайт "Расходы города"';
		
		//$mail->setFrom($this->config["email"]["from"]);
		//$mail->setFrom($this->config["email"]["from"], 'Сайт "Расходы города"');
		$mail->setFrom($this->config["email"]["from"], 'Сайт "Расходы города"');
		$mail->addAddress($user["email"], $user["name"]);     // Add a recipient
		//$mail->addAddress('ellen@example.com');               // Name is optional
		//$mail->addReplyTo('info@example.com', 'Information');
		//$mail->addCC('cc@example.com');
		//$mail->addBCC('bcc@example.com');

		//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		//$mail->isHTML(true);                                    // Set email format to HTML

		$mail->Subject = $subject;
		$mail->Body    = $msg . PHP_EOL;
		//$mail->Body    = "Ваш пароль: 123" . PHP_EOL;
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
