<?php
use Phalcon\Mvc\Model;

class FileCollection extends Model {
	/**
	* @var integer
	*/
	public $id;
	
	/**
	* @var integer
	*/
	public $collection_id;
		
	/**
	* @var integer
	*/
	public $file_id;
}
