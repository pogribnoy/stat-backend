$.templates({
	
	operation: '{{if id==="save" tmpl="entity_button_save"}}{{else id==="delete" tmpl="entity_button_delete"}}{{else id==="check" tmpl="entity_button_check"}}{{/if}}\n',
	//checks_operation: '{{if id==="save" tmpl="entity_button_save"}}{{else id==="delete" tmpl="entity_button_delete"}}{{else id==="check" tmpl="entity_button_check"}}{{/if}}\n',
	
	
	// кнопки формы
	entity_button_save: '<button type="button" class="btn btn-success" aria-label="{{:name}}" name="{{:id}}{{:~descriptor.local_data.eid}}" onclick="entitySave(\'{{:~descriptor.local_data.container_id}}\', null);">{{:name}}</button>\n',
	entity_button_delete: '<button type="button" class="btn btn-danger" aria-label="{{:name}}" name="{{:id}}{{:~descriptor.local_data.eid}}" onclick="entityDelete(\'{{:~descriptor.local_data.container_id}}\', \'{{:~descriptor.local_data.eid}}\');">{{:name}}</button>\n',
	entity_button_check: '<button type="button" class="btn" aria-label="{{:name}}" name="{{:id}}{{:~descriptor.local_data.eid}}" onclick="entityCheckOnly(\'{{:~descriptor.local_data.container_id}}\', \'{{:~descriptor.local_data.eid}}\');">{{:name}}</button>\n',
	
	
	//entity_button_back: '<button type="button" class="btn" aria-label="{{:name}}" name="{{:id}}" onclick=""><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>\n',
	//entity_button_scroller: '<button type="button" class="btn btn-primary btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick=""><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>\n',

	
});
