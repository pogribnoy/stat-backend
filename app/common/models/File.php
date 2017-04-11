<?php
use Phalcon\Mvc\Model;

class File extends Model
{
	/**
	* @var integer
	*/
	public $id;
	
	/**
	* @var string
	*/
	public $name;
		
	/**
	* @var string
	*/
	public $directory;
	
	public function initialize() {
		//$this->hasMany("id", "FileCollection", "file_id");
		$this->hasManyToMany("id", "FileCollection", "file_id", "collection_id", "Organization", "img");
  }
}
