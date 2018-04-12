<?php
use Phalcon\Mvc\Model;
use Phalcon\Db\RawValue;

class Audit extends Model{
	public $id;
	public $event;
	public $user_id;
	public $session_id;
	public $message;	
	/**
	* @var datetime
	*/
	public $created_at;
	
	public function beforeCreate() {
		//$this->created_at = new RawValue('now()');
	}
		
	public function initialize() {
		$this->belongsTo("user_id", "User", "id");
		$this->belongsTo("organization_id", "Organization", "id");
		
		//Пропуск при всех INSERT/UPDATE операциях
		//$this->skipAttributes(array('created_at'));
		/*$this->skipAttributesOnCreate([
			'created_at',
		]);

        // Skips only when updating
        $this->skipAttributesOnUpdate([
			'created_at',
		]);*/
		//$this->skipAttributes(array('created_at'));
  }
}
