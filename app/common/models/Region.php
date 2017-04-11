<?php
use Phalcon\Mvc\Model;

class Region extends Model{
	public $id;
	public $name;
	
	public function initialize() {
		$this->hasMany("id", "Organization", "region_id");
  }
}
