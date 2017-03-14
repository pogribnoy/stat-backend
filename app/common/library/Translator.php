<?php
use Phalcon\Mvc\User\Component;

/**
 * Translator
 */
class Translator extends Component {
	public $language = "ru";
	//$messages = array(); // массив загружается из файлов перевода
	
	public function getTranslation($language, $controller) {
		$controller = strtolower($controller);
		$this->language = $language;
		// Проверка существования файла с переводом общего функционала
		if (file_exists(APP_PATH . "app/translate/".$language."/".$language.".php")) {
			 require APP_PATH . "app/translate/".$language."/".$language.".php";
		} else {
			 // Переключение на язык по умолчанию
			 require APP_PATH . "app/translate/ru/ru.php";
		}
			
		// Проверка существования файла с переводом конкретного компонента
		if (file_exists(APP_PATH . "app/translate/".$language."/".$controller.".php")) {
			 require APP_PATH . "app/translate/".$language."/".$controller.".php";
		} else {
			 // Переключение на язык по умолчанию
			 if (file_exists(APP_PATH . "app/translate/ru/".$controller.".php")) require APP_PATH . "app/translate/ru/".$controller.".php";
		}
		$this->messages = $messages;
		// Возвращение объекта работы с переводом
		return new \Phalcon\Translate\Adapter\NativeArray(array("content" => $messages));
	}
	public function addTranslation($controller) {
		$controller = strtolower($controller);
		// Проверка существования файла с переводом конкретного компонента
		if (file_exists(APP_PATH . "app/translate/".$this->language."/".$controller.".php")) {
			 require APP_PATH . "app/translate/".$this->language."/".$controller.".php";
		} 
		// Переключение на язык по умолчанию
		else if (file_exists(APP_PATH . "/app/translate/ru/".$controller.".php")) {
			require APP_PATH . "/app/translate/ru/".$controller.".php";
		}
		
		$this->messages = array_merge($messages, $this->messages);
		// Возвращение объекта работы с переводом
		return new \Phalcon\Translate\Adapter\NativeArray(array("content" => $this->messages));
	}
}
