<?php
use Phalcon\Mvc\Model;

class Setting extends Model {
	/**
	* @var integer
	*/
	public $id;
	
	/**
	* @var string
	*/
	public $code;
	
	/**
	* @var string
	*/
	public $value;
}
