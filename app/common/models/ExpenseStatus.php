<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class ExpenseStatus extends Model{
	public $id;
	public $name;
	public $created_at;
	public $deleted_at;
	
	public function initialize() {
		$this->hasMany("id", "Expense", "expense_status_id");
  }
}
