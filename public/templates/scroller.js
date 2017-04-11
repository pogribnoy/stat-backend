$.templates({
	// строк на странице
	page_size: '<select name="page_sizes" onchange="change_page_size(\'{{:~descriptor.local_data.container_id}}\');">{{for ~descriptor.pager.page_sizes tmpl="page_size_option" /}}</select>\n',
	page_size_option: '<option value="{{:#data}}" {{if #data == ~descriptor.filter_values.page_size}}selected="selected"{{/if}}>{{:#data}}</option>\n',
	
	// ячейка строки заголовков колонки
	column_name: '<th>{{:name}}{{if sortable}}{{if id === #parent.parent.parent.data.descriptor.filter_values.sort}}{{if #parent.parent.parent.parent.data.descriptor.filter_values.order === "DESC" && }}{{include tmpl="sort_desc"/}}{{else}}{{include tmpl="sort_asc"/}}{{/if}}{{else}}{{if #parent.parent.parent.parent.data.descriptor.filter_values.order === "DESC" && }}{{include tmpl="sort_desc"/}}{{else}}{{include tmpl="sort_asc"/}}{{/if}}{{/if}}{{/if}}</th>\n',
	
	common_operation: '{{if id=="add" && ~descriptor.add_style && ~descriptor.add_style == \'entity\' tmpl="button_add"}}{{else id=="select"  && ~descriptor.add_style && ~descriptor.add_style == \'scroller\' tmpl="button_select"}}{{/if}}\n',
	filter_operation: '{{if id==="apply" tmpl="button_apply"}}{{else id=="clear" tmpl="button_clear"}}{{/if}}\n',
	item_operation: '{{if id==="delete" tmpl="button_delete"}} {{else id=="edit" tmpl="button_edit"}} {{else id=="show" tmpl="button_show"}} {{/if}}\n',
	
	// общие кнопки скроллера
	button_add: '<button type="button" class="btn btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="row_edit(\'{{:~descriptor.local_data.container_id}}\', null);">{{:name}}</button>\n',	
	button_select: '<button type="button" class="btn btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="link_entity(\'{{:~descriptor.local_data.container_id}}\', \'{{:~descriptor.controllerName}}\', null, \'checkbox\');">{{:name}}</button>\n',	
	
	// групповые операции
	button_group_delete: '<li><a href="#" aria-label="{{:name}}" name="{{:id}}" onclick="group_delete(\'{{:~descriptor.local_data.container_id}}\');">{{:name}}</a></li>',

	// кнопки для строк
	button_edit: '<button type="button" class="btn btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="row_edit(\'{{:~descriptor.local_data.container_id}}\', \'{{:~entity.local_data.eid}}\');"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>',
	button_show: '<button type="button" class="btn btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="row_show(\'{{:~descriptor.local_data.container_id}}\', \'{{:~entity.local_data.eid}}\');"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>',
	button_delete: '<button type="button" class="btn btn-xs" aria-label="{{:name}}" name="{{:id}}{{:~entity.local_data.eid}}" onclick="row_delete(\'{{:~descriptor.local_data.container_id}}\', \'{{:~entity.local_data.eid}}\');"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>\n',
	
	// кнопки фильтра
	button_apply: '<button type="button" class="btn btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="apply_filter(\'{{:~descriptor.local_data.container_id}}\');"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span></button>\n',
	button_clear: '<button type="button" class="btn btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="clear_filter(\'{{:~descriptor.local_data.container_id}}\');"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>\n',
	
	// колонки скроллера
	scroller_row: '{{if ~descriptor.group_operations.length > 0}}{{include tmpl="scroller_data_cell_ckeckbox"/}}{{/if}}{{for ~columns ~entity=#data}}{{if id=="operations"}}{{include tmpl="scroller_data_cell_operations"/}}{{else id=="active"}}{{include tmpl="scroller_data_cell_active"/}}{{else id=="id"}}{{include tmpl="scroller_data_cell_id"/}}{{else}}{{include tmpl="scroller_data_cell"/}}{{/if}}{{/for}}',
	scroller_data_cell_ckeckbox: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}}><input type="{{if ~descriptor.local_data.select_style }}{{:~descriptor.local_data.select_style}}{{else}}checkbox{{/if}}" name="row_{{:~descriptor.local_data.container_id}}" id="row_{{:#data.local_data.eid}}" /></td>',
	scroller_data_cell_id: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}} name="{{:~entity.local_data.eid}}" id="{{:~entity.local_data.eid}}">{{if ~entity.fields[id].value=="-1"}}-{{else}}{{:~entity.fields.id.value}}{{/if}}</td>',
	scroller_data_cell_active: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}} name="{{:id}}" id="{{:~entity.local_data.eid}}"><input type="checkbox" disabled {{if ~entity.fields[id].value=="1"}}checked="checked"{{/if}}/></td>',
	scroller_data_cell_operations: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}}>{{for ~descriptor.item_operations tmpl="item_operation" /}}</td>',
	//scroller_data_cellr: '<td>{{:~utilities.toJSON(~entity.fields[id])}}</td>',
	//scroller_data_cell1: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}} name="{{:id}}" id="{{:id}}">{{:~entity.fields[id].value}}</td>',
	scroller_data_cell: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}} name="{{:id}}" id="{{:id}}">\
		{{if nullSubstitute && nullSubstitute != "undefined" && ~entity.fields[id].value == \'\'}}{{:nullSubstitute}}\
		{{else}}\
			{{if ~entity.fields[id].url}}<a href="{{:~entity.fields[id].url}}">{{/if}}\
				{{if ~entity.fields[id].value1 && ~entity.fields[id].value2}}с {{:~entity.fields[id].value1}} по {{:~entity.fields[id].value2}}\
				{{else ~entity.fields[id].value1 }}с {{:~entity.fields[id].value1}}\
				{{else ~entity.fields[id].value2 }}до {{:~entity.fields[id].value2}}\
				{{else}}{{:~entity.fields[id].value}}{{/if}}\
			{{if ~entity.fields[id].url}}</a>{{/if}}\
		{{/if}}\
	</td>',
	
});

// вспомогательные функции для страниц
$.views.helpers({
  utilities: {
    getPagerHtml: function(descriptor) {
		var total_pages = descriptor.pager.total_pages;
		//var current = descriptor.filter_values.page;
		//console.log("total_pages="+total_pages);
		var html=''
		for(var i=1; i<=total_pages; i++){
			if(i==descriptor.filter_values.page) html += '<li class="active"><span><span aria-hidden="true">'+i.toString()+'</span></span></li>\n';
			else html += '<li><a href="#" onclick="change_page(\'' + descriptor.local_data.container_id + '\', ' + i.toString() + ');">' + i.toString() + '</a></li>\n';
		}
      return html;
    },
	createUDID: function(descriptor) {
      return createUDID(descriptor);
    },
	objectToArray: function(object) {
		if(!object) return [];
		// если передан массив, то его и возвращаем
		else if(object && object.length) return object;
		var res = [];
		for (var key in object) {
			res.push(object[key]);
		}
		return res;
    },
	getContainerByID: function(container_id) {
		return containers[container_id];
    },
	getOjectKeysCount: function(array) {
		return Object.keys(array).length;
	},
	toJSON: function(obj) {
		return JSON.stringify(obj);
	},
	isFieldRequired: function(field) {
		//console.log(field);
		if(field.type == "period" && field.required && field.required > 0) return true;
		else if(field.required && field.required == 2) return true;
		return false;
	},
	checksHasError: function(checkResult) {
		if(checkResult) {
			var checkResultLength = checkResult.length;
			for(var i=0; i< checkResultLength; i++) {
				//console.log(checkResult[i].type);
				if(checkResult[i].type == "error") return true;
			}
		}
		return false;
	},
  }
});