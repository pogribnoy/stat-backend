<?php
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Model;
use Phalcon\Logger\Adapter\File as FileAdapter;

class AuditPlugin extends Plugin {
	// типы событий
	const userLogin = 'userLogin';
	const entitySave = 'entitySave';
	const entityDelete = 'entityDelete';
	
	public function __construct() {
		$this->logger = new FileAdapter(APP_PATH . "app/logs/audit.log", array('mode' => 'a'));
	}
	
	public function audit($eventType, $data = null) {
		//$data = [$msg = null, $newEntity = null, $oldEntity = null];
		//$this->logger->log(__METHOD__ . '. isset newEntity = ' . isset($data['newEntity']));
		$newEntityName = null;
		$newEntityID = null;
		$newEntity = null;
		if($data != null && isset($data['newEntity']) && $data['newEntity'] != null) {
			$newEntityName = get_class($data['newEntity']);
			//$this->logger->log(__METHOD__ . '. newEntityName = ' . $newEntityName . ', data = ' . json_encode($data));
			if($newEntityName == FALSE) $newEntityName = null;
			$newEntity = $data['newEntity'];
			$newEntityID = $newEntity->id;
		}
		
		$oldEntityName = null;
		$oldEntityID = null;
		$oldEntity = null;
		if($data != null && isset($data['oldEntity']) && $data['oldEntity'] != null) {
			$oldEntityName = get_class($data['oldEntity']);
			//$this->logger->log(__METHOD__ . '. oldEntityName = ' . $oldEntityName . ', data = ' . json_encode($data));
			if($oldEntityName == FALSE) $oldEntityName = null;
			$oldEntity = $data['oldEntity'];
			$oldEntityID = $oldEntity->id;
		}
		
		$parentEntityName = null;
		$parentEntityID = null;
		$parentEntity = null;
		if($data != null && isset($data['parentEntity']) && $data['parentEntity'] != null) {
			$parentEntityName = get_class($data['parentEntity']);
			//$this->logger->log(__METHOD__ . '. parentEntityName = ' . $parentEntityName . ', data = ' . json_encode($data));
			if($parentEntityName == FALSE) $parentEntityName = null;
			$parentEntity = $data['parentEntity'];
			$parentEntityID = $parentEntity->id;
		}
		
		$auth = $this->session->get('auth');
		$login = null;
		$userID = null;
		if(isset($auth['login'])) {
			$login = $auth['login'];
			$user = User::findFirst([
				'conditions' => "login = '" . $login . "'"
			]);
			if($user) $userID = $user->id;
		}
		
		
		$audit = new Audit();
		$audit->event = $eventType;
		$audit->session_id = $this->session->getId();
		if($userID) $audit->user_id = $userID;
		
		//$this->logger->log(__METHOD__ . '. newEntityName = ' . $newEntityName);
		
		if($data != null && isset($data['msg']) && strlen($data['msg']) > 0) $audit->message = $data['msg'] . PHP_EOL;
		else $audit->message = '';
		if($newEntityName != null) $audit->message .= 'newEntityName=' . $newEntityName . PHP_EOL;
		if($newEntityID != null) $audit->message .= 'newEntityID=' . $newEntityID . PHP_EOL;
		if($oldEntityName != null) $audit->message .= 'oldEntityName=' . $oldEntityName . PHP_EOL;
		if($oldEntityID != null) $audit->message .= 'oldEntityID=' . $oldEntityID . PHP_EOL;
		if($parentEntityName != null) $audit->message .= 'parentEntityName=' . $parentEntityName . PHP_EOL;
		if($parentEntityID != null) $audit->message .= 'parentEntityID=' . $parentEntityID . PHP_EOL;
		if($newEntity != null) $audit->message .= 'New entity data=' . PHP_EOL . json_encode($newEntity);
		if($oldEntity != null) $audit->message .= 'Old entity data=' . PHP_EOL . json_encode($oldEntity);

		
		//$this->logger->log(__METHOD__ . '. audit = ' . json_encode($audit));
		
		if($audit->create() === false) {
			$dbMessages = '';
			foreach ($audit->getMessages() as $message) {
				$dbMessages .= PHP_EOL . $message;
			}
			
			$this->logger->error(__METHOD__ . '. Error while create record in _audit_ table. audit = ' . json_encode($audit) . 'dbMessages: ' . $dbMessages);
		}
		else $this->logger->log(__METHOD__ . '. Audit event = ' . $eventType /*json_encode($audit)*/);
	}
}
