<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class OrganizationRequestTopic extends Model {
	public $id;
	public $name;
	public $created_at;
	public $deleted_at;
	
	public function initialize() {
		$this->hasMany("id", "OrganizationRequest", "topic_id");
		
		$this->addBehavior(
			new Timestampable([
				'beforeCreate' => [
					'field'  => 'created_at',
					'format' => 'Y-m-d H:i:s',
				]
			])
        );
		
		$this->addBehavior(
			new SoftDelete([
				'field' => 'deleted_at',
				'value' => (new DateTime())->format("Y-m-d H:i:s"),
			])
        );
	}
}
