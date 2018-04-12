<?php
class SimpleReferenceEntity extends ControllerEntity {
	public function initFields() {
		$this->fields = [
			'id' => [
				'id' => 'id',
				'name' => $this->t->_("text_entity_property_id"),
				'type' => 'label',
				//'required' => 2,
				'newEntityValue' => '-1',
				'nullSubstitute' => $this::nullSubstitute,
			], 
			'name' => [
				'id' => 'name',
				'name' => $this->t->_("text_entity_property_name"),
				'type' => 'text',
				'required' => 2,
				'newEntityValue' => null,
			],
			'created_at' => [
				'id' => 'created_at',
				'name' => $this->t->_("text_entity_property_created_at"),
				'type' => 'label',
				//'required' => 2,
				'newEntityValue' => null,
				'nullSubstitute' => $this::nullSubstitute,
			],
			'deleted_at' => [
				'id' => 'deleted_at',
				'name' => $this->t->_("text_entity_property_deleted_at"),
				'type' => 'label',
				//'required' => 2,
				'newEntityValue' => null,
				'nullSubstitute' => $this::nullSubstitute,
			],
		];
		// наполняем поля данными
		parent::initFields();
	}
	
	protected function fillModelFieldsFromSaveRq() {
		//$this->entity->id получен ранее при select из БД или будет присвоен при создании записи в БД
		//$this->logger->log('val: ' . json_encode($this->fields)); //DEBUG
		$this->entity->name = $this->fields['name']['value'];
	}
	
	protected function fillFieldsFromRow($row) {
		$this->fields["id"]["value"] = $row->id;
		$this->fields["name"]["value"] = $row->name;
		$this->fields["created_at"]["value"] = $row->created_at;
		$this->fields["deleted_at"]["value"] = $row->deleted_at;
	}
}
