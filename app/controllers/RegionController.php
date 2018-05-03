<?php
class RegionController extends SimpleReferenceEntity {
	public $entityName  = 'Region';
	public $tableName  = 'region';
	
	/*protected function deleteEntityLinks($entity) {
		if(!isset($entity)) $entity = $this->entity;
		// ссылки из организаций - блокирующая связь
		$org = false;
		$org = Organization::findFirst([
			"conditions" => "region_id = ?1",
			"bind" => array(1 => $entity->id)
		]);
		if($org) {
			if($this->acl->isAllowed($this->userData['role_id'], 'expenselist', 'index')) $msg = 'Регион назначен одному или более муниципалитету. Перейти к <a class="" href="/organizationlist?filter_region_id=' . $entity->id . '">списку муниципалитетов</a>';
			else $msg = 'Регион назначен одному или более муниципалитету';
			$this->error['messages'][] = [
				'title' => "Ошибка удаления",
				'msg' => $msg,
				'data' => json_encode($org),
			];
			return false;
		}
		return true;
	}*/
}