<?php
class Organizationrequesttopiccontroller extends SimpleReferenceEntity {
	public $entityName  = 'OrganizationRequestTopic';
	public $tableName  = 'organization_request_topic';
	
	/*protected function deleteEntityLinks($entity) {
		if(!isset($entity)) $entity = $this->entity;
		// ссылки из расходов - блокирующая связь
		$expenses = false;
		$expenses = Expense::find([
			"conditions" => "expense_type_id = ?1",
			"bind" => array(1 => $entity->id)
		]);
		if($expenses && count($expenses)>0) {
			if($this->acl->isAllowed($this->userData['role_id'], 'OrganizationRequestTopicList', 'index')) $msg = 'Тема обращения назначена одному или более обращениям. Перейти к <a class="" href="/OrganizationRequestList?filter_topic_id=' . $entity->id . '">списку запросов</a>';
			else $msg = 'Тема обращения назначена одному или более расходу';
			$this->error['messages'][] = [
				'title' => "Ошибка удаления",
				'msg' => $msg,
				'data' => json_encode($expenses),
			];
			return false;
		}
		return true;
	}*/
}
