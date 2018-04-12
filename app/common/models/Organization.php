<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Mvc\Model\Message;

class Organization extends Model{
	public $id;
	public $name;
	public $region_id;
	public $contacts;
	public $email;
	public $img;
	public $created_at;
	public $deleted_at;
		
	public function initialize() {
		$this->belongsTo("region_id", "Region", "id");
		//$this->belongsTo("img", "FileCollection", "id");
		$this->hasManyToMany("img", "FileCollection", "collection_id", "file_id", "File", "id");
		$this->hasManyToMany("id", "UserOrganization", "organization_id", "user_id", "User", "id");
		
		//$this->hasMany("id", "UserOrganization", "organization_id");
		$this->hasMany("id", "Expense", "organization_id");
		$this->hasMany("id", "Audit", "user_id");
		
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
	
	/*public function beforeCreate() {
		if ($this->name ==  "asd") {
			$this->appendMessage(
				new Message('Name is too short')
			);

			return false;
		}
		if (mb_strlen($this->name) > 4) {
			$this->appendMessage(
				new Message('Name is too long')
			);

			return false;
		}
	}
	public function beforeUpdate() {
		if (mb_strlen($this->name) > 4) {
			$this->appendMessage(
				new Message('Name is too long')
			);

			return false;
		}
	}*/
}
