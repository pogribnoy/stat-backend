<?php
use Phalcon\Mvc\Model;
use Phalcon\Db\RawValue;

class Resource extends Model {
	/**
	* @var integer
	*/
	public $id;
	
	/**
	* @var string
	*/
	public $group;
	
	/**
	* @var string
	*/
	public $controller;
	
	/**
	* @var string
	*/
	public $action;
	
	/**
	* @var string
	*/
	public $module;
	
	/**
	* @var string
	*/
	public $description;	
	
	/**
	* @var datetime
	*/
	public $created_at;
	
	public function beforeCreate() {
		$this->created_at = new RawValue('now()');
	}
	public function initialize() {
		$this->hasMany("id", "UserRoleResource", "resource_id");
  }
}
