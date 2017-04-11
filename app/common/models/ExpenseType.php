<?php
use Phalcon\Mvc\Model;

class ExpenseType extends Model{
	public $id;
	public $name;
	
	public function initialize() {
		$this->hasMany("id", "Expense", "expense_type_id");
  }
}
