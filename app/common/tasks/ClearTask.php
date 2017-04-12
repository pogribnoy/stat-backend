<?php
use Phalcon\Cli\Task;
use Phalcon\Logger\Adapter\File as FileAdapter;

class ClearTask extends Task {
    public function mainAction() {
        //echo __METHOD__ . PHP_EOL . PHP_EOL;
		echo __METHOD__ . ". Delete of unassigned expenses" . PHP_EOL;
		
		$curDate = new DateTime('now');
		$startDate = $curDate->modify('-1 day');
		$textStartDate = $startDate->format("Y-m-d");
		echo __METHOD__ . ". textStartDate = ". $textStartDate . PHP_EOL;
		
		// удаление расходов, не связанных с организацией
		$unDeletedCount = 0;
		$deletedCount = 0;
		
		$rows = Expense::find([
			"conditions" => "created_at < ?1 AND organization_id IS NULL",
			"bind" => [
				1 => $textStartDate,
			],
		]);
		
		foreach($rows as $row) {
			//echo __METHOD__ . '. row id: ' . $row->id . PHP_EOL;
			$id = $row->id;
			if(!$row->delete()) {
				$unDeletedCount++;
				$dbMessages = '';
				foreach ($row->getMessages() as $message) {
					$dbMessages .= "<li>" . $message . "</li>";
				}
				echo __METHOD__ . ". Unassigned expense with id = " . $id . " can't be deleted: " $dbMessage;
			}
			else echo __METHOD__ . '. Expense with id=' . $row->id . " has been deleted" . PHP_EOL;
			
			$deletedCount++;
		}
		echo __METHOD__ . ". Deleted " . $deletedCount . ' expenses' . PHP_EOL;
		echo __METHOD__ . ". Delete of unassigned expenses complete" . PHP_EOL . PHP_EOL;
		
		// удаление файлов на диске, на которые нет ссылки в БД
		// файлы организаций
		/*$unDeletedCount = 0;
		$deletedCount = 0;
		$fileName = '';
		$fileDirectoryName = '';
		
		$rows = File::find([
			"conditions" => "name = \'?1\' AND directory = \'?2\'" ,
			"bind" => [
				1 => $fileName,
				2 => $fileDirectoryName,
			]
		]);
		
		foreach($rows as $row) {
			$id = $row->id;
			if(!$row->delete()) {
				$unDeletedCount++;
				$dbMessages = '';
				foreach ($row->getMessages() as $message) {
					$dbMessages .= "<li>" . $message . "</li>";
				}
				echo __METHOD__ . ". File with id = " . $id . " can't be deleted: " $dbMessage;
			}
			else echo __METHOD__ . '. File with id=' . $row->id . " has been deleted" . PHP_EOL;
			
			$deletedCount++;
		}
		echo __METHOD__ . ". Deleted " . $deletedCount . ' files' . PHP_EOL;		
		echo __METHOD__ . ". Delete of unassigned files complete" . PHP_EOL . PHP_EOL;*/
		
    }
}

