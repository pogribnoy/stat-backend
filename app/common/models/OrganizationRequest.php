<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class OrganizationRequest extends Model {
	public $id;
	public $organization_id;
	public $expense_id;
	public $topic_id;
	public $status_id;
	public $request;
	public $response;
	public $response_email;
	public $created_at;
	public $deleted_at;
	
	public function initialize() {
		$this->belongsTo("topic_id", "OrganizationRequestTopic", "id");
		//$this->belongsTo("user_id", "User", "id");
		$this->belongsTo("organization_id", "Organization", "id");
		$this->belongsTo("expense_id", "Expense", "id");
		$this->belongsTo("status_id", "Status", "id");
		
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
