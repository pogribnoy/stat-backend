<?php
use Phalcon\Mvc\User\Component;

/**
 * Translator
 */
class Translator extends Component {
	
	public function getTranslation($language, $dir = null) {
		if($dir == null) $dir = APP_PATH . "app/translate/";
		//$functionalityCode = strtolower($functionalityCode);
		$language = explode('-', $language)[0];
		
		// Проверка существования файла с переводом общего функционала
		if (file_exists($dir . $language . "/" . $language . ".php")) {
			 require $dir . $language . "/" . $language . ".php";
		} else {
			 // Переключение на язык по умолчанию
			 $language = 'ru';
			 require $dir . $language . "/" . $language . ".php";
		}	
		
		if(!isset($messages)) $messages = [];
		// TODO. Необходимо сделать вывод ошибки в лог
			
		// Возвращение объекта работы с переводом
		$res = new \Phalcon\Translate\Adapter\NativeArray(array("content" => $messages));
		$res->messages = $messages;
		$res->language = $language;
		return $res;
	}
}
