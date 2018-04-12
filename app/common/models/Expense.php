<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

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
	public $deleted_at;
		
	public function initialize() {
		$this->belongsTo("expense_type_id", "ExpenseType", "id");
		$this->belongsTo("expense_status_id", "ExpenseStatus", "id");
		$this->belongsTo("organization_id", "Organization", "id");
		$this->belongsTo("street_type_id", "StreetType", "id");
		
		$this->addBehavior(
			new Timestampable([
				'beforeCreate' => [
					'field'  => 'created_at',
					'format' => 'Y-m-d H:i:s',
				]
			])
        );
		
		$this->addBehavior(
			new SoftDelete([
				'field' => 'deleted_at',
				'value' => (new DateTime())->format("Y-m-d H:i:s"),
			])
        );
	}
}
