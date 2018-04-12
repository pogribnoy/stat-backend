<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class News extends Model {
	public $id;
	public $name;
	public $description;
	public $publication_date;
	public $active;
	public $created_by;
	public $created_at;
	public $deleted_at;
	
	public function initialize() {
		$this->belongsTo("created_by", "User", "id");
				
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
