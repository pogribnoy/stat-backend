<?php
use Phalcon\Mvc\Model;
use Phalcon\Db\RawValue;

class OrganizationRequestTopic extends Model {
	/**
	* @var integer
	*/
	public $id;
	
	/**
	* @var string
	*/
	public $name;
	
	/**
	* @var datetime
	*/
	public $created_at;
	
	public function beforeCreate() {
		$this->created_at = new RawValue('now()');
	}
	public function initialize() {
		$this->hasMany("id", "OrganizationRequest", "topic_id");
	}
}
