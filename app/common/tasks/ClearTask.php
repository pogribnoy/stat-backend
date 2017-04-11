<?php
use Phalcon\Cli\Task;
use Phalcon\Logger\Adapter\File as FileAdapter;

class ClearTask extends Task {
    public function mainAction() {
        echo __METHOD__ . PHP_EOL;
		
		echo __METHOD__ . ". Delete of unassigned expenses" . PHP_EOL;
		
		$curDate = new DateTime('now');
		$startDate = $curDate->modify('-1 day');
		$textStartDate = $startDate->format("Y-m-d");
		echo __METHOD__ . ". textStartDate = ". $textStartDate . PHP_EOL;
		
		// удаление расходов, не связанных с организацией
		//$phql = "SELECT * FROM Expense WHERE STR_TO_DATE(Expense.created_at, '%d.%m.%Y') < '" . $textStartDate . "'";// Expense.organization_id=38";// IS NULL";// AND Expense.created_at < '" . $textStartDate . "'";
		//echo __METHOD__ . ". phql=" . $phql . PHP_EOL;
		
		//$rows = false;
		//$rows = $this->modelsManager->executeQuery($phql);
		//var_dump($rows);
		//if($rows) {
			$unDeletedCount = 0;
			$deletedCount = 0;
			
			$rows = Expense::find([
				"conditions" => "created_at < ?1", 
				"bind" => [
					1 => $textStartDate,
				]
			]);
			
			foreach($rows as $row) {
				echo __METHOD__ . '. row id: ' . $row->id . PHP_EOL;
				$id = $row->id;
				/*if(!$row->delete()) {
					$unDeletedCount++;
					$dbMessages = '';
					foreach ($row->getMessages() as $message) {
						$dbMessages .= "<li>" . $message . "</li>";
					}
					echo __METHOD__ . ". Unassigned expense with id = " . $id . " can't be deleted: " $dbMessage;
				}*/
				//else 
				$deletedCount++;
			}
			if($deletedCount > 0) echo __METHOD__ . ". Deleted " . $deletedCount . ' of unassigned expenses';
			else echo __METHOD__ . ". Deleted " . $deletedCount . ' of unassigned expenses';
			
		//}
		//else echo __METHOD__ . '. There are no unassigned expenses to delete' . PHP_EOL;
		
		echo __METHOD__ . ". Delete of unassigned expenses complete" . PHP_EOL;
    }
}

