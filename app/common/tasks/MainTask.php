<?php
use Phalcon\Cli\Task;
use Phalcon\Logger\Adapter\File as FileAdapter;

class MainTask extends Task {
    public function mainAction() {
        echo "This is the default task and the default action" . PHP_EOL;
		
		$logger = new FileAdapter(APP_PATH . "app/logs/task_clear.log", array('mode' => 'a'));
		
		$logger->log(__METHOD__ . '. Completed');
    }
}

