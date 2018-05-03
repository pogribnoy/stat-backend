<?php
class ExpensetypeController extends SimpleReferenceEntity {
	public $entityName  = 'ExpenseType';
	public $tableName  = 'expense_type';
	
	/*protected function deleteEntityLinks($entity) {
		if(!isset($entity)) $entity = $this->entity;
		// ссылки из расходов - блокирующая связь
		$expense = false;
		$expense = Expense::findFirst([
			"conditions" => "expense_type_id = ?1",
			"bind" => array(1 => $entity->id)
		]);
		if($expense) {
			if($this->acl->isAllowed($this->userData['role_id'], 'expenselist', 'index')) $msg = 'Тип расхода назначен одному или более расходу. Перейти к <a class="" href="/expenselist?filter_expense_type_id=' . $entity->id . '">списку расходов</a>';
			else $msg = 'Тип расхода назначен одному или более расходу';
			$this->error['messages'][] = [
				'title' => "Ошибка удаления",
				'msg' => $msg,
				'data' => json_encode($expense),
			];
			return false;
		}
		return true;
	}*/
}
