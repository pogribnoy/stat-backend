<?php
use Phalcon\Mvc\Model as Model;
use Phalcon\Db\RawValue;

class UserRole extends Model {
	/**
  * @var integer
  */
	public $id;
	
	/**
  * @var string
  */
	public $name;
		
	/**
  * @var integer
  */
	public $active;
	
	/**
  * @var datetime
  */
	public $created_at;
	
	public function beforeCreate() {
		$this->created_at = new RawValue('now()');
		//$this->active = "1";
	}
	public function initialize() {
		$this->hasMany("id", "UserRoleResource", "user_role_id");
		$this->hasMany("id", "User", "user_role_id");
  }
}
