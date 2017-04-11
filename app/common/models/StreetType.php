<?php
use Phalcon\Mvc\Model;

class StreetType extends Model{
	public $id;
	public $name;
	
	public function initialize() {
		$this->hasMany("id", "Organization", "street_type_id");
  }
}
