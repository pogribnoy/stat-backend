<?php
use Phalcon\Mvc\Model;

class Organization extends Model{
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
	public $region_id;
	/**
	* @var string
	*/
	public $contacts;
	/**
	* @var string
	*/
	public $email;
	/**
	* @var integer
	*/
	public $img;
		
	public function initialize() {
		$this->belongsTo("region_id", "Region", "id");
		//$this->belongsTo("img", "FileCollection", "id");
		$this->hasManyToMany("img", "FileCollection", "collection_id", "file_id", "File", "id");
		$this->hasManyToMany("id", "UserOrganization", "organization_id", "user_id", "User", "id");
		
		//$this->hasMany("id", "UserOrganization", "organization_id");
		$this->hasMany("id", "Expense", "organization_id");
  }
}
