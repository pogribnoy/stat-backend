<?php
use Phalcon\Mvc\Model;

class UserOrganization extends Model {
	/**
	* @var integer
	*/
	public $user_id;
	
	/**
	* @var integer
	*/
	public $organization_id;
}
