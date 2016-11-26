<?php
use Phalcon\Mvc\User\Component;

class Tools extends Component {
	public function beforeExecuteRoute($dispatcher){
		$this->controllerName = $dispatcher->getControllerName();
		$this->actionName = $dispatcher->getActionName();
	}
	
	public function isAdminRole($role){
		if($role == 1 /*админ*/ || $role == 4 /*новостной редактор*/ || $role == 5 /*оператор*/) return true;
		else return false;
	}
	
	// операции
	public function getEntityFormOperations($role_id, $entity, $acl, $t, $exludeOps = null, $actionName="show"){
		$operations = array();
		if(!$exludeOps) $exludeOps = array();
		
		// массив операций на основе разрешений
		if($actionName == "edit") {
			if($acl->isAllowed($role_id, $entity, 'save') && !in_array('save', $exludeOps)) {
				$operations[] = array(
					'id' => 'save',
					'name' => $t->_('button_save')
				);
			}
		}
		if($acl->isAllowed($role_id, $entity, 'delete') && !in_array('delete', $exludeOps)) {
			$operations[] = array(
				'id' => 'delete',
				'name' => $t->_('button_delete')
			);
		}
		if($acl->isAllowed($role_id, $entity, 'back') && !in_array('back', $exludeOps)) {
			$operations[] = array(
				'id' => 'back',
				'name' => $t->_('button_back')
			);
		}
		if($acl->isAllowed($role_id, $entity, 'scroller') && !in_array('scroller', $exludeOps)) {
			$operations[] = array(
				'id' => 'scroller',
				'name' => $t->_('button_scroller')
			);
		}
		
		return $operations;
	}
	
	public function getScrollerOperations($role_id, $entity, $acl, $t, $actionName="show"){
		$operations = array();
		
		// массив операций на основе разрешений, привязанных к одной сущности
		$operations["item_operations"] = array();
		if($acl->isAllowed($role_id, $entity, 'show')) {
			$operations["item_operations"][] = array(
				'id' => 'show',
				'name' => $t->_('button_show')
			);
		}
		if($actionName != "show") {
			if($acl->isAllowed($role_id, $entity, 'edit')) {
				$operations["item_operations"][] = array(
					'id' => 'edit',
					'name' => $t->_('button_edit')
				);
			}
		
			if($acl->isAllowed($role_id, $entity, 'delete')) {
				$operations["item_operations"][] = array(
					'id' => 'delete',
					'name' => $t->_('button_delete')
				);
			}
		}
		
		// массив операций на основе разрешений, не привязанных к одной сущности
		$operations["common_operations"] = array();
		if($acl->isAllowed($role_id, $entity, 'add') && $actionName != "show") {
			// для скроллера
			$operations["common_operations"][] = array(
				'id' => 'add',
				'name' => $t->_('button_add')
			);
			// для грида
			$operations["common_operations"][] = array(
				'id' => 'select',
				'name' => $t->_('button_select')
			);
		}
		
		// массив операций для фильтра
		$operations["filter_operations"] = array();
		$operations["filter_operations"][] = array(
			'id' => 'apply',
			'name' => $t->_('button_apply')
		);
		$operations["filter_operations"][] = array(
			'id' => 'clear',
			'name' => $t->_('button_clear')
		);
		
		return $operations;
	}
}
