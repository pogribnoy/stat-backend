<?php
use Phalcon\Mvc\Model as Model;

class UserRoleResource extends Model {
	/**
	* @var integer
	*/
	public $user_role_id;
	
	/**
	* @var integer
	*/
	public $resource_id;
	
	public function initialize() {
		$this->belongsTo("user_role_id", "UserRole", "id");
		$this->belongsTo("resource_id", "Resource", "id");
	}
}
