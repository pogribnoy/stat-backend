<?php
use Phalcon\Cli\Task;
use Phalcon\Logger\Adapter\File as FileAdapter;

class GenerateClientTranslatorTask extends Task {
    public function mainAction() {
        //echo __METHOD__ . PHP_EOL . PHP_EOL;
		echo __METHOD__ . ". GenerateClientTranslatorTask" . PHP_EOL;
		
		$templateFileName = __DIR__ . "/templates/translator.js";
		// папки с переводами для сервера		
		$publicTranslateDir = '/var/www/stat-frontend/app/translate/';
		$privateTranslateDir = '/var/www/stat-backend/app/translate/';
		// папки с переводами для клиента
		$publicTargetFileDir = '/var/www/stat-frontend/public/js/locale/';
		$privateTargetFileDir = '/var/www/stat-backend/public/js/locale/';
		
		// Перевод общедоступной части
		echo __METHOD__ . ". templateFileName = " . $templateFileName . PHP_EOL;
		$template = file_get_contents($templateFileName);
		if($template === FALSE) echo __METHOD__ . ". Template file not found (" . $templateFileName . ') ' . PHP_EOL;
		else {
			// Общедоступная часть
			echo __METHOD__ . ". Public translation". PHP_EOL;
			$this->generateTargetTranslarion($publicTranslateDir, $publicTargetFileDir, $template);
			// Служебная часть
			echo __METHOD__ . ". Service translation". PHP_EOL;
			$this->generateTargetTranslarion($privateTranslateDir, $privateTargetFileDir, $template);
		}
    }
	
	private function generateTargetTranslarion($sourceDir, $targetDir, $resultFile) {
		if(FALSE !== ($dir = opendir($sourceDir))) {
			while (FALSE !== ($entry = readdir($dir))) {
				if (is_dir($sourceDir . $entry) && $entry != '.' && $entry != '..') {
					$language = $entry;
					echo __METHOD__ . ". language = " . $language . PHP_EOL;
					$translator = $this->translator->getTranslation($language, $sourceDir);
					//echo __METHOD__ . '. translator: ' . json_encode($translator->messages) . PHP_EOL;
					//$resultFile = substr($template, 0);
					$resultFile = str_replace ('%messages%', json_encode($translator->messages), $resultFile);
					echo __METHOD__ . ". resultFile length = " . strlen($resultFile) . PHP_EOL;
					file_put_contents($targetDir . $language . '.js', $resultFile, /*FILE_APPEND |*/ LOCK_EX);
				}
			}
			closedir($dir);
		}
    }
}

