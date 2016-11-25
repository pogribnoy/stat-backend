<?php
use Phalcon\Mvc\Model;

class Expense extends Model{
	public $id;
	public $name;
	public $amount;
	public $date;
	public $expense_type_id;
	public $organization_id;
		
	public function initialize() {
		$this->belongsTo("expense_type_id", "ExpenseType", "id");
		$this->belongsTo("organization_id", "Organization", "id");
  }
}
