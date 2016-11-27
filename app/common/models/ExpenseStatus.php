<?php
use Phalcon\Mvc\Model;

class ExpenseStatus extends Model{
	public $id;
	public $name;
	
	public function initialize() {
		$this->hasMany("id", "Expense", "expense_status_id");
  }
}
