<?php
class SimpleReferenceList extends ControllerList {
	public function initColumns() {
		$this->columns = [
			'id' => [
				'id' => 'id',
				'name' => $this->controller->t->_("text_entity_property_id"),
				'filter' => 'number',
				"sortable" => "DESC",
			],
			'name' => [
				'id' => 'name',
				'name' => $this->controller->t->_("text_entity_property_name"),
				'filter' => 'text',
				"sortable" => "DESC",
			],
			'created_at' => [
				'id' => 'created_at',
				'name' => $this->controller->t->_("text_entity_property_created_at"),
				'filter' => 'text',
				"sortable" => "DESC",
			],
			'deleted_at' => [
				'id' => 'deleted_at',
				'name' => $this->controller->t->_("text_entity_property_deleted_at"),
				'filter' => 'text',
				"sortable" => "DESC",
			],
			'operations' => [
				'id' => 'operations',
				'name' => $this->controller->t->_("text_entity_property_actions"),
			],
		];
	}
	
	public function fillFieldsFromRow($row) {
		$item = [
			"fields" => [
				"id" => [
					'id' => 'id',
					'value' => $row->id,
				],
				"name" => [
					'id' => 'name',
					'value' =>  $row->name,
				],
				"created_at" => [
					'id' => 'created_at',
					'value' =>  $row->created_at,
				],
				"deleted_at" => [
					'id' => 'deleted_at',
					'value' =>  $row->deleted_at,
				],
			],
		];
		$this->items[] = $item;
	}
}
