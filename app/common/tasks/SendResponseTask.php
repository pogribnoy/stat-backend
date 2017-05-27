<?php
use Phalcon\Cli\Task;
use Phalcon\Logger\Adapter\File as FileAdapter;

class SendResponseTask extends Task {
    public function mainAction() {
        //echo __METHOD__ . PHP_EOL . PHP_EOL;
		echo __METHOD__ . ". Send responses for organization requests" . PHP_EOL;
		
		$curDate = new DateTime('now');
		echo __METHOD__ . ". Current datetime = ". $curDate->format("Y-m-d H:i:s") . PHP_EOL;
		
		$unDoneCount = 0;
		$sentCount = 0;
		$processedStatusID = $this->config['application']['requestStatus']['processedStatusID'];
		$doneStatusID = $this->config['application']['requestStatus']['doneStatusID'];
		
		$rows = OrganizationRequest::find([
			"conditions" => "status_id = ?1",
			"bind" => [
				1 => $processedStatusID,
			],
		]);
		
		echo __METHOD__ . ". rows count = " . count($rows) . ' ' . PHP_EOL;
		
		foreach($rows as $row) {
			echo __METHOD__ . '. row id: ' . $row->id . PHP_EOL;
			$id = $row->id;
			$expenseTypeName = null;
			$expenseName = null;
			$expenseSettlement = null;
			$expenseStreetTypeName = null;
			$expenseStreet = null;
			$expenseHouse = null;
			$organizationName = null;
			if($row->expense_id != null && $row->expense_id != '') {
				$expense = $row->getExpense();
				$expenseName = $expense->name;
				$expenseSettlement = $expense->settlement;
				if($expense->street_type_id != null && $expense->street_type_id != '') {
					$expenseStreetTypeName = $expense->getStreetType()->name;
				}
				$expenseStreet = $expense->street;
				$expenseHouse = $expense->house;
				if($expense->expense_type_id != null && $expense->expense_type_id != '') {
					$expenseTypeName = $expense->getExpenseType()->name;
				}
			}
			if($row->organization_id != null && $row->organization_id != '') $organizationName = $row->getOrganization()->name;
			
			// отправка письма
			$msg = 'Ваше обращение № ' . $id . ' обработано' . PHP_EOL . 
				($organizationName == null ? '' : 'Муниципалитет: ' . $organizationName . PHP_EOL) . 
				($expenseTypeName == null ? '' : 'Тема: ' . $expenseTypeName . PHP_EOL) . 
				($expenseName == null ? '' : 'Наименование расхода: ' . $expenseName . PHP_EOL) . 
				($expenseSettlement == null ? '' : 'Наименование нас. пункта: ' . $expenseSettlement . PHP_EOL) . 
				($expenseStreet == null ? '' : 'Улица: ' . ($expenseStreetTypeName == null ? '' : mb_strtolower($expenseStreetTypeName) . ' ') . $expenseStreet . PHP_EOL) . 
				($expenseHouse == null ? '' : 'Дом, стр.: ' . $expenseHouse . PHP_EOL) . PHP_EOL .
				
				'Вопрос: ' . $row->request . PHP_EOL . 
				'Ответ: ' . $row->response . PHP_EOL . 
				'Дата: ' . $curDate->format("Y-m-d H:i:s") . PHP_EOL . 
				PHP_EOL . PHP_EOL . 'Письмо сформировано автоматически, не пытайтесь ответить на него.';
			$msgAlt = $msg;
			
			$res = false;
			$res = $this->email->sendEmail(['email' => $row->response_email, 'name' => ''], 'Ваше обращение (id=' . $id . ') обработано', $msg, $msgAlt);
			echo __METHOD__ . '. res = ' . ($res ? 'true' : 'false') . PHP_EOL;
			if(!$res) $unDoneCount++;
			else {
				$row->status_id = $doneStatusID;
				if(!$row->update()) {
					$unDoneCount++;
					$dbMessages = '';
					foreach ($row->getMessages() as $message) {
						$dbMessages .= "<li>" . $message . "</li>";
					}
					echo __METHOD__ . ". Organization request with id = " . $id . " can't be updated: " . $dbMessage;
				}
				else {
					echo __METHOD__ . '. Organization request with id = ' . $row->id . ' has been updated' . PHP_EOL;
					$sentCount++;
				}
			}
		}
		
		echo __METHOD__ . ". Sent " . $sentCount . ' responses' . PHP_EOL;
		echo __METHOD__ . ". Sent of responses complete" . PHP_EOL . PHP_EOL;
		
    }
}

