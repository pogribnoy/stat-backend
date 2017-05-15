<?php
use Phalcon\Mvc\Model;
use Phalcon\Db\RawValue;

class OrganizationRequest extends Model {
	/**
	* @var integer
	*/
	public $id;
	
	/**
	* @var integer
	*/
	public $organization_id;
	
	/**
	* @var integer
	*/
	public $expense_id;
	
	/**
	* @var integer
	*/
	public $topic_id;
	
	/**
	* @var integer
	*/
	public $status_id;
	
	/**
	* @var string
	*/
	public $request;
	
	/**
	* @var string
	*/
	public $response;
	
	/**
	* @var string
	*/
	public $response_email;
	
	/**
	* @var datetime
	*/
	public $created_at;
	
	public function beforeCreate() {
		$this->created_at = new RawValue('now()');
	}
	public function initialize() {
		$this->belongsTo("topic_id", "OrganizationRequestTopic", "id");
		//$this->belongsTo("user_id", "User", "id");
		$this->belongsTo("organization_id", "Organization", "id");
		$this->belongsTo("expense_id", "Expense", "id");
		$this->belongsTo("status_id", "Status", "id");
	}
}
