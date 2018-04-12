<?php
use Phalcon\Mvc\Model as Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class UserRole extends Model {
	public $id;
	public $name;
	public $active;
	public $created_at;
	public $deleted_at;
	
	public function initialize() {
		$this->hasMany("id", "UserRoleResource", "user_role_id");
		$this->hasMany("id", "User", "user_role_id");
		
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
