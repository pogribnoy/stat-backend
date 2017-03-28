<?php
use Phalcon\Mvc\User\Component;

/**
 * Translator
 */
class Translator extends Component {
	public $language = "ru";
	//public $messages;
	//$messages = array(); // массив загружается из файлов перевода
	
	public function getTranslation($language, $functionalityCode) {
		$functionalityCode = strtolower($functionalityCode);
		$this->language = $language;
		// Проверка существования файла с переводом общего функционала
		if (file_exists(APP_PATH . "app/translate/".$language."/".$language.".php")) {
			 require APP_PATH . "app/translate/".$language."/".$language.".php";
		} else {
			 // Переключение на язык по умолчанию
			 require APP_PATH . "app/translate/ru/ru.php";
		}
			
		// Проверка существования файла с переводом конкретного компонента
		if (file_exists(APP_PATH . "app/translate/".$language."/".$functionalityCode.".php")) {
			 require APP_PATH . "app/translate/".$language."/".$functionalityCode.".php";
		} else {
			 // Переключение на язык по умолчанию
			 if (file_exists(APP_PATH . "app/translate/ru/".$functionalityCode.".php")) require APP_PATH . "app/translate/ru/".$functionalityCode.".php";
		}
		if(isset($messages)) $this->messages = $messages;
		else $this->messages = array();
		// TODO. Необходимо сделать вывод ошибки в лог
			
		// Возвращение объекта работы с переводом
		return new \Phalcon\Translate\Adapter\NativeArray(array("content" => $this->messages));
	}
	public function addTranslation($functionalityCode) {
		$functionalityCode = strtolower($functionalityCode);
		// Проверка существования файла с переводом конкретного компонента
		if (file_exists(APP_PATH . "app/translate/".$this->language."/".$functionalityCode.".php")) {
			 require APP_PATH . "app/translate/".$this->language."/".$functionalityCode.".php";
		} 
		// Переключение на язык по умолчанию
		else if (file_exists(APP_PATH . "/app/translate/ru/".$functionalityCode.".php")) {
			require APP_PATH . "/app/translate/ru/".$functionalityCode.".php";
		}
		
		if(isset($messages)) $this->messages = array_merge($messages, $this->messages);
		// TODO. Необходимо сделать вывод ошибки в лог
		
		// Возвращение объекта работы с переводом
		return new \Phalcon\Translate\Adapter\NativeArray(array("content" => $this->messages));
	}
}
