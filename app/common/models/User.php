<?php
use Phalcon\Mvc\Model;
use Phalcon\Db\RawValue;

class User extends Model {
	/**
	* @var integer
	*/
	public $id;
	
	/**
	* @var string
	*/
	public $password;
	
	/**
	* @var string
	*/
	public $phone;
	
	/**
	* @var string
	*/
	public $email;
	
	/**
	* @var string
	*/
	public $name;
	
	/**
	* @var integer
	*/
	public $points;
	
	/**
	* @var integer
	*/
	public $user_role_id;
	
	/**
	* @var integer
	*/
	public $active;
	
	/**
	* @var datetime
	*/
	public $created_at;
	
	public function beforeCreate() {
		$this->created_at = new RawValue('now()');
		$this->points = 0;
		//$this->active = "1";
	}
	
	public function validation() {
		// валидируем номер-телефона
		// регулярное выражение
		/*$this->validate(new RegexValidator(array(
				'field' => 'phone',
				'pattern' => '/^((((\(\d{3}\))|(\d{3}-))\d{3}-\d{4})|(\+?\d{1,3}((-| |\.)(\(\d{1,4}\)(-| |\.|^)?)?\d{1,8}){1,5}))(( )?(x|ext)\d{1,5}){0,1}$/'
		)));
		// уникальность
		$this->validate(new UniquenessValidator(array(
				'field' => 'phone',
				'message' => 'Номер телефона пользователя уже зарегистрирован в системе'//$this->t->_('phone_exists')
		)));
		
		// валидируем email
		// уникальность
		$this->validate(new EmailValidator(array(
				'field' => 'email',
				'message' => 'Значение поля \'email\' не соответствует допустимому формату'
		)));
		$this->validate(new UniquenessValidator(array(
				'field' => 'email',
				'message' => 'Адрес электронной почты пользователя уже зарегистрирован в системе'
		)));*/
		
		// проверяем пароль на прочность
		
		
		if ($this->validationHasFailed() == true) {
			return false;
		}
	}
	public function initialize() {
		//$this->hasMany("id", "Poll", "created_by");
		//$this->hasMany("id", "News", "created_by");
		//$this->hasMany("id", "Result", "created_by");
		//$this->hasMany("id", "ObjectCategory", "created_by");
		$this->belongsTo("user_role_id", "UserRole", "id");
		//$this->hasMany("id", "UserOrganization", "user_id");
		$this->hasManyToMany("id", "UserOrganization", "user_id", "organization_id", "Organization", "id");
		
		//Пропуск при всех INSERT/UPDATE операциях
		//$this->skipAttributes(array('created_at'));
		//Пропуск только при добавлении
		//$this->skipAttributesOnCreate(array('points'));
  }
}
