<?php
use Phalcon\Mvc\User\Component;

/**
 * Translator
 */
class Translator extends Component {
	
	public function getTranslation($language/*, $functionalityCode*/) {
		//$functionalityCode = strtolower($functionalityCode);
		$language = explode('-', $language)[0];
		
		// Проверка существования файла с переводом общего функционала
		if (file_exists(APP_PATH . "app/translate/".$language."/".$language.".php")) {
			 require APP_PATH . "app/translate/".$language."/".$language.".php";
		} else {
			 // Переключение на язык по умолчанию
			 require APP_PATH . "app/translate/ru/ru.php";
			 $language = 'ru';
		}
			
		// Проверка существования файла с переводом конкретного компонента
		/*if (file_exists(APP_PATH . "app/translate/".$language."/".$functionalityCode.".php")) {
			 require APP_PATH . "app/translate/".$language."/".$functionalityCode.".php";
		} else {
			 // Переключение на язык по умолчанию
			 if (file_exists(APP_PATH . "app/translate/ru/".$functionalityCode.".php")) require APP_PATH . "app/translate/ru/".$functionalityCode.".php";
		}*/
		if(!isset($messages)) $messages = [];
		// TODO. Необходимо сделать вывод ошибки в лог
			
		// Возвращение объекта работы с переводом
		$res = new \Phalcon\Translate\Adapter\NativeArray(array("content" => $messages));
		$res->messages = $messages;
		$res->language = $language;
		return $res;
	}
}
