<?php
use Phalcon\Mvc\Model;

class RequestStatus extends Model{
	public $id;
	public $name_code;
	
	public function initialize() {
		$this->hasMany("id", "OrganizationRequest", "status_id");
  }
}
