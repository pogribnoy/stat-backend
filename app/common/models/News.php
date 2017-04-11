<?php
use Phalcon\Mvc\Model;
use Phalcon\Db\RawValue;

class News extends Model {
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
	public $description;
	
	/**
	* @var datetime
	*/
	public $publication_date;
	
	/**
	* @var integer
	*/
	public $active;
	
	/**
	* @var integer
	*/
	public $created_by;
	
	/**
	* @var datetime
	*/
	public $created_at;
	
	public function beforeCreate() {
		$this->created_at = new RawValue('now()');
		//$this->active = "1";
	}
	
	public function initialize() {
		$this->belongsTo("created_by", "User", "id");
		
		//Пропуск при всех INSERT/UPDATE операциях
		$this->skipAttributes(array('created_at'));
  }
}
