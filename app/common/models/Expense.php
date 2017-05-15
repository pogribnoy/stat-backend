<?php
use Phalcon\Mvc\Model;

class Expense extends Model{
	public $id;
	public $name;
	public $amount;
	public $expense_type_id;
	public $expense_status_id;
	public $organization_id;
	public $street_type_id;
	public $street;
	public $house;
	public $executor;
	public $settlement;
	public $target_date_from;
	public $target_date_to;
	public $created_at;
		
	public function initialize() {
		$this->belongsTo("expense_type_id", "ExpenseType", "id");
		$this->belongsTo("expense_status_id", "ExpenseStatus", "id");
		$this->belongsTo("organization_id", "Organization", "id");
		$this->belongsTo("street_type_id", "StreetType", "id");
  }
}
