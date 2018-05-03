<?php
class TasksController extends ControllerBase {
	
	private $tmpCrontabFile = APP_PATH . "app/common/tasks/templates/crontab.txt";
	
	public $tasks = [
		// очистка непривязанных расходов
		"clear" => [
			"nameCode" => "name_tasks_index_clear",
			"schedule" => "30 0 * * *",
			"logFile" => APP_PATH . "app/common/tasks/clear.log",
			"disabled" => 0,
			"command" => " php " . APP_PATH . "app/common/tasks/cli.php clear",
		],
		// бекап БД
		"backup" => [
			"nameCode" => "name_tasks_index_backup",
			"schedule" => "30 0 * * *",
			"logFile" => APP_PATH . "backups/backup.log",
			"disabled" => 0,
			"command" => " /var/www/stat-backend/backups/backup.sh",
		],
		// бекап файлов
		"backup_files" => [
			"nameCode" => "name_tasks_index_backup_files",
			"schedule" => "30 0 * * *",
			"logFile" => APP_PATH . "backups/backup_files.log",
			"disabled" => 0,
			"command" => " /var/www/stat-backend/backups/backup_files.sh",
		],
		// отправка ответов
		"send_response" => [
			"nameCode" => "name_tasks_index_response_sent",
			"schedule" => "30 0 * * *",
			"logFile" => APP_PATH . "app/common/tasks/send_response.log",
			"disabled" => 0,
			"command" => " php " . APP_PATH . "app/common/tasks/cli.php send_response",
		],
		// генерация sitemap.xml
		"generate_sitemap" => [
			"nameCode" => "name_tasks_index_generate_sitemap",
			"schedule" => "30 0 * * *",
			"logFile" => APP_PATH . "app/common/tasks/generate_sitemap.log",
			"disabled" => 0,
			"command" => " php " . APP_PATH . "app/common/tasks/cli.php generate_sitemap",
		],
		// минификация JS
		"minify_js" => [
			"nameCode" => "name_tasks_index_minify_js",
			"schedule" => "*/1 * * * *",
			"logFile" => "/var/www/stat-backend/minificator/minify_js.log",
			"disabled" => 0,
			"command" => " /var/www/stat-backend/minificator/minify_js.sh",
		],
		// генератор клиентских переводов
		"generate_client_translator" => [
			"nameCode" => "name_tasks_index_generate_client_translator",
			"schedule" => "*/1 * * * *",
			"logFile" => APP_PATH . "app/common/tasks/generate_client_translator.log",
			"disabled" => 0,
			"command" => " php " . APP_PATH . "app/common/tasks/cli.php generate_client_translator",
		],
		// минификация HTTP
		"minify_html" => [
			"nameCode" => "name_tasks_index_minify_html",
			"schedule" => "*/1 * * * *",
			"logFile" => "/var/www/stat-backend/minificator/minify_html.log",
			"disabled" => 0,
			"command" => " /var/www/stat-backend/minificator/minify_html.sh",
		],
	];
	
	public function initialize() {
		parent::initialize();
		// убираем указание на layout index, чтобы не задваивался вывод
		//$this->view->cleanTemplateAfter();
	}

	public function indexAction() {
		//$this->view->setVar("page_header", $this->t->_('text_'.$this->controllerName.'_title'));
			
		// необходимо открыть crontab, найти строку, в которой содержится "ClearTask", и взять из нее расписание
		
		$crontab = shell_exec('crontab -l');
		//$crontab = file_get_contents('/etc/crontab');
		$lines = explode("\n", $crontab);
		
		$debug = "";
		
		//file_put_contents('/tmp/crontab.txt', $crontab . '* * * * * NEW_CRON' . PHP_EOL);
		//echo exec('crontab /tmp/crontab.txt');
		$parts = array();
		
		foreach($this->tasks as $code => &$task) {
			//$task["name"] = $this->t->_($this->controllerName . '_task_' . $task["code"] . "name");
			$isFound = false;
			foreach($lines as $id => $line) {
				$cmdPos = stripos($line, $task["command"]);
				if($cmdPos !== FALSE) {
					$isFound = true;
					
					$task["schedule"] = trim(substr($line, 0, $cmdPos));
					//$debug .= "|" . strlen($line);
					
					break;
				}
			}
			if(!$isFound) $task["disabled"] = 1;
			else if(substr($line, 0, 1) == '#') {
				$task["disabled"] = 1;
				$task["schedule"] = trim(substr($task["schedule"], 1, mb_strlen($task["schedule"])));
			}
			else $task["schedule"] = trim($task["schedule"]);
		}
		
		$this->view->tasks = $this->tasks;
		$this->view->crontab = $crontab;
		$this->view->debug = $debug;
		$this->view->parts = $parts;
	}
	
	public function saveAction() {
		$error = ['messages' => []];
		$success = ['messages' => []];
		
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');
		}
		$rq = $this->request->getJsonRawBody();
		$this->logger->log(__METHOD__ . ". rq = " . json_encode($rq));
		
		// необходимо открыть crontab, найти строку, в которой содержится "ClearTask", и изменить в ней расписание
		$crontab = shell_exec('crontab -l');
		//$crontab = file_get_contents('/etc/crontab');
		//$this->logger->log(__METHOD__ . ". crontab = " . $crontab);
		$lines = explode("\n", trim($crontab));
		//$this->logger->log(__METHOD__ . ". lines count = " . count($lines));
		$newLines = [];
		foreach($this->tasks as $code => &$task) {
			if(isset($rq->$code) && isset($rq->$code->schedule) && isset($rq->$code->disabled)) {
				$newSchedule = $rq->$code->schedule;
				
				$isFound = false;
				$cmdStartPos = 0;
				foreach($lines as $id => &$line) {
					// попутно убираем пустые строки из crontab
					if (preg_replace('/\s/', '', $line) == "") unset($lines[$id]);
					//$this->logger->log(__METHOD__ . ". trim count = " . count(preg_replace('/\s/', '', $line)));
					
					$cmdPos = stripos($line, $task["command"]);
					if($cmdPos !== FALSE) {
						$isFound = true;
						// строка найдена, необходимо из нее взять расписание
						
						
						//$this->logger->log(__METHOD__ . ". cmdStartPos = " . $cmdStartPos);
						
						//$task["schedule"] = substr ($line, 0, $cmdStartPos-1);
						
						$line = substr_replace($line, $newSchedule, 0, $cmdPos);
						if($rq->$code->disabled == 1) $line = "# " . $line;
						
						$this->logger->log(__METHOD__ . ". newSchedule = " . $newSchedule);
						$this->logger->log(__METHOD__ . ". line = " . $line);
						
						break;
					}
				}
				if(!$isFound) {
					// добавляем строку вконце
					if($rq->$code->disabled == 1) $newLines[] = "# " . $newSchedule . $task["command"] . " > " . $task["logFile"] . PHP_EOL;
					else $newLines[] = $newSchedule . $task["command"] . " > " . $task["logFile"] . PHP_EOL;
					
					$this->logger->log(__METHOD__ . ". New line = " . $newSchedule . ' php ' . APP_PATH . "/app/tasks/cli.php " . $code);
				}
			}
			else {
				$error['messages'][] =[
					'title' => 'Ошибка',
					'msg' => "Ошибка сохранения данных",
				];
				$this->logger->log(__METHOD__ . ". Task with code = " . $code . " not found in request");
			}
		}
		
		$data = [];
		if(count($error['messages']) > 0) {
			$data['error'] = $error;
		}
		else {
			$newCrontab = implode(PHP_EOL, $lines);
			$newCrontab .= PHP_EOL;
			
			$this->logger->log(__METHOD__ . ". newLines count = " . count($newLines));
			if($newLines) $newCrontab .= implode(PHP_EOL, $newLines);
			
			//$this->logger->log(__METHOD__ . ". newCrontab = " . $newCrontab);
			
			//file_put_contents("/etc/crontab", $newCrontab);
			file_put_contents( $this->tmpCrontabFile, $newCrontab, /*FILE_APPEND |*/ LOCK_EX);
			shell_exec("crontab " . $this->tmpCrontabFile);
			//shell_exec('echo "' . $newCrontab. '" | crontab -');
			
			
			$success['messages'][] =[
				'title' => 'Успех',
				'msg' => "Данные сохранены",
			];
			$data['success'] = $success;
			$data['debug'] = $newCrontab;
			$data['crontab'] = $newCrontab;
		}
		
		if($this->request->isAjax()) return json_encode($data);
		
		$this->view->setVar("data", $data);
	}
}
