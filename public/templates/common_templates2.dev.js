$.views.settings.debugMode(true); // DEBUG
	
$.templates({
	/*** Шаблоны для скроллеров ***/
	// строк на странице
	page_size: '<select name="page_sizes" onchange="change_page_size(\'{{:~descriptor.localData.containerId}}\');">{{for ~descriptor.pager.page_sizes tmpl="page_size_option" /}}</select>\n',
	page_size_option: '<option value="{{:#data}}" {{if #data == ~descriptor.filter_values.page_size}}selected="selected"{{/if}}>{{:#data}}</option>\n',

	// ячейка строки заголовков колонки
	column_name: '<th>{{:name}}{{if sortable}}{{if id === #parent.parent.parent.data.descriptor.filter_values.sort}}{{if #parent.parent.parent.parent.data.descriptor.filter_values.order === "DESC" && }}{{include tmpl="sort_desc"/}}{{else}}{{include tmpl="sort_asc"/}}{{/if}}{{else}}{{if #parent.parent.parent.parent.data.descriptor.filter_values.order === "DESC" && }}{{include tmpl="sort_desc"/}}{{else}}{{include tmpl="sort_asc"/}}{{/if}}{{/if}}{{/if}}</th>\n',

	common_operation: '{{if id=="add" && ~descriptor.add_style && ~descriptor.add_style == \'entity\' tmpl="button_add"}}{{else id=="select"  && ~descriptor.add_style && ~descriptor.add_style == \'scroller\' tmpl="button_select"}}{{/if}}\n',
	filter_operation: '{{if id==="apply" tmpl="button_apply"}}{{else id=="clear" tmpl="button_clear"}}{{/if}}\n',
	item_operation: '{{if id==="delete" tmpl="button_delete"}} {{else id=="edit" tmpl="button_edit"}}{{else id=="show" tmpl="button_show"}} {{else id=="question" tmpl="button_question"}}{{else id=="show" tmpl="button_show"}} {{/if}}\n',
	group_operation: '{{if id==="delete" tmpl="button_group_delete"}} {{else id=="orgEmailGenerate" tmpl="button_group_orgEmailGenerate"}}{{else id=="orgUserGenerate" tmpl="button_group_orgUserGenerate"}} {{/if}}\n',

	// общие кнопки скроллера
	button_add: '<button type="button" class="btn btn-success btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="app.core.scroller.rowAdd(\'{{:~descriptor.localData.containerId}}\');">{{:name}}</button>\n',	
	button_select: '<button type="button" class="btn btn-success btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="app.core.scroller.rowSelect(\'{{:~descriptor.localData.containerId}}\', \'{{:~descriptor.controllerName}}\', null, \'checkbox\');">{{:name}}</button>\n',	

	// групповые операции
	button_group_delete: '<li><a href="#" aria-label="{{:name}}" name="{{:id}}" onclick="group_delete(\'{{:~descriptor.localData.containerId}}\');">{{:name}}</a></li>',
	button_group_orgUserGenerate: '<li><a href="#" aria-label="{{:name}}" name="{{:id}}" onclick="group_delete(\'{{:~descriptor.localData.containerId}}\');">{{:name}}</a></li>',
	//button_group_orgRegistration: '<li class="dropdown-submenu"><ul class="dropdown-menu"><li><a tabindex="-1" href="#">Second level</a></li></ul><a tabindex="-1" href="#" aria-label="{{:name}}" name="{{:id}}">{{:name}}</a></li>',
	//button_group_orgRegistration: '<li class="dropdown"><ul class="dropdown-menu"><li><a href="#" aria-label="{{:name}}" name="{{:id}}">{{:name}}</a></li></ul></li>',
	button_group_orgEmailGenerate: '<li><a href="#" aria-label="{{:name}}" name="{{:id}}" onclick="group_delete(\'{{:~descriptor.localData.containerId}}\');">{{:name}}</a></li>',

	// кнопки для строк
	button_edit: '<button type="button" class="btn btn-success btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="app.core.scroller.rowEdit(\'{{:~descriptor.localData.containerId}}\', \'{{:~entity.localData.eid}}\');" title="{{:title}}"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>',
	button_show: '<button type="button" class="btn btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="app.core.scroller.rowShow(\'{{:~descriptor.localData.containerId}}\', \'{{:~entity.localData.eid}}\');" title="{{:title}}"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>',
	button_delete: '<button type="button" class="btn btn-danger btn-xs" aria-label="{{:name}}" name="{{:id}}{{:~entity.localData.eid}}" onclick="row_delete(\'{{:~descriptor.localData.containerId}}\', \'{{:~entity.localData.eid}}\');" title="{{:title}}"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>\n',
	button_question: '<button type="button" class="btn btn-xs" aria-label="{{:name}}" name="{{:id}}{{:~entity.localData.eid}}" onclick="organizationRequest(\'{{:~descriptor.localData.containerId}}\', \'{{:~entity.localData.eid}}\');" title="{{:title}}"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></button>\n',

	// кнопки фильтра
	button_apply: '<button type="button" class="btn btn-default btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="apply_filter(\'{{:~descriptor.localData.containerId}}\');" title="{{:title}}"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span></button>\n',
	button_clear: '<button type="button" class="btn btn-default btn-xs" aria-label="{{:name}}" name="{{:id}}" onclick="clear_filter(\'{{:~descriptor.localData.containerId}}\');" title="{{:title}}"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>\n',

	// колонки скроллера
	scroller_row: '{{if ~descriptor.group_operations.length > 0 || (~descriptor.type == "scroller" && inModal == 1)}}{{include tmpl="scroller_data_cell_ckeckbox"/}}{{/if}}{{for ~columns ~entity=#data}}{{if !hidden }}{{if id=="operations"}}{{include tmpl="scroller_data_cell_operations"/}}{{else id=="active"}}{{include tmpl="scroller_data_cell_active"/}}{{else id=="id"}}{{include tmpl="scroller_data_cell_id"/}}{{else}}{{include tmpl="scroller_data_cell"/}}{{/if}}{{/if}}{{/for}}',
	scroller_data_cell_ckeckbox: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}}><input type="{{if ~descriptor.localData.select_style }}{{:~descriptor.localData.select_style}}{{else}}checkbox{{/if}}" name="row_{{:~descriptor.localData.containerId}}" id="row_{{:#data.localData.eid}}" /></td>',
	scroller_data_cell_id: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}} name="{{:~entity.localData.eid}}" id="{{:~entity.localData.eid}}">{{if ~entity.fields[id].value=="-1"}}-{{else}}{{:~entity.fields.id.value}}{{/if}}</td>',
	scroller_data_cell_active: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}} name="{{:id}}" id="{{:~entity.localData.eid}}"><input type="checkbox" disabled {{if ~entity.fields[id].value=="1"}}checked="checked"{{/if}}/></td>',
	scroller_data_cell_operations: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}}>{{for ~descriptor.item_operations tmpl="item_operation" /}}</td>',
	//scroller_data_cellr: '<td>{{:~utilities.toJSON(~entity.fields[id])}}</td>',
	//scroller_data_cell1: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}} name="{{:id}}" id="{{:id}}">{{:~entity.fields[id].value}}</td>',
	scroller_data_cell: '<td{{if hideble}} class="hidden-xs hidden-sm hidden-md"{{/if}} name="{{:id}}" id="{{:id}}">' +
		'{{if nullSubstitute && nullSubstitute != "undefined" && ~entity.fields[id].value == \'\'}}{{:nullSubstitute}}' +
		'{{else ~entity.fields[id] }}' +
			'{{if ~entity.fields[id].url}}<a href="{{:~entity.fields[id].url}}">{{/if}}' +
				'{{if ~entity.fields[id].value1 && ~entity.fields[id].value2}}с {{:~entity.fields[id].value1}} по {{:~entity.fields[id].value2}}' +
				'{{else ~entity.fields[id].value1 }}с {{:~entity.fields[id].value1}}' +
				'{{else ~entity.fields[id].value2 }}до {{:~entity.fields[id].value2}}' +
				'{{else}}{{:~entity.fields[id].value}}{{/if}}' +
			'{{if ~entity.fields[id].url}}</a>{{/if}}' +
		'{{/if}}' +
	'</td>',

	expenselist_row: '{{include tmpl="scroller_row" /}}',
	expensetypelist_row: '{{include tmpl="scroller_row" /}}',
	organizationlist_row: '{{include tmpl="scroller_row" /}}',
	resourcelist_row: '{{include tmpl="scroller_row" /}}',
	userlist_row: '{{include tmpl="scroller_row" /}}',
	userrolelist_row: '{{include tmpl="scroller_row" /}}',
	regionlist_row: '{{include tmpl="scroller_row" /}}',
	newslist_row: '{{include tmpl="scroller_row" /}}',
	streettypelist_row: '{{include tmpl="scroller_row" /}}',
	expensestatuslist_row: '{{include tmpl="scroller_row" /}}',
	organizationrequestlist_row: '{{include tmpl="scroller_row" /}}',


	/*** Шаблоны сущностей ***/
	// кнопки формы
	operation: '{{if id==="save" tmpl="entity_button_save"}}' +
		'{{else id==="delete" tmpl="entity_button_delete"}}' +
		'{{else id==="password_print" tmpl="entity_button_password_print"}}' +
		'{{else id==="check" tmpl="entity_button_check"}}{{/if}}\n',
	entity_button_save: '<button type="button" class="btn btn-success" aria-label="{{:name}}" name="{{:id}}{{:~descriptor.localData.eid}}" onclick="entitySave(\'{{:~descriptor.localData.containerId}}\');">{{:name}}</button>\n',
	entity_button_delete: '<button type="button" class="btn btn-danger" aria-label="{{:name}}" name="{{:id}}{{:~descriptor.localData.eid}}" onclick="entityDelete(\'{{:~descriptor.localData.containerId}}\', \'{{:~descriptor.localData.eid}}\');">{{:name}}</button>\n',
	entity_button_check: '<button type="button" class="btn" aria-label="{{:name}}" name="{{:id}}{{:~descriptor.localData.eid}}" onclick="entityCheckOnly(\'{{:~descriptor.localData.containerId}}\', \'{{:~descriptor.localData.eid}}\');">{{:name}}</button>\n',
	entity_button_password_print: '<button type="button" class="btn" aria-label="{{:name}}" name="{{:id}}{{:~descriptor.localData.eid}}" onclick="passwordPrint(\'{{:~descriptor.localData.containerId}}\');">{{:name}}</button>\n',

	// поля порм
	entity_fields_form: '<div class="form-horizontal">{{if descriptor.fields ~fields=~utilities.objectToArray(descriptor.fields) ~descriptor=descriptor}}{{for ~fields}}{{if !(access && access == "hidden") && type }}' +
	'<div class="form-group" name="field_{{:id}}">' +
		'<label for="field_{{:id}}_value" class="col-sm-4 col-md-3 col-lg-offset-0 col-lg-3 control-label">{{:name}}{{if ~utilities.isFieldRequired(#data) == true && access == "edit"}}<span class="text-danger">*</span>{{/if}}</label><div class="col-sm-8 col-md-8 col-lg-7">' +
			'{{if type == "label" }}{{include tmpl = access + "_entity_field_label" /}}' +
			'{{else type == "text"}}{{include tmpl = access + "_entity_field_text" /}}' +
			'{{else type == "textarea" }}{{include tmpl = access + "_entity_field_textarea" /}}' +
			'{{else type == "select" }}{{include tmpl = access + "_entity_field_select" /}}' +
			'{{else type == "email" }}{{include tmpl = access + "_entity_field_text" /}}' +
			'{{else type == "amount"}}{{include tmpl = access + "_entity_field_amount" /}}' +
			'{{else type == "number"}}{{include tmpl = access + "_entity_field_text" /}}' +
			'{{else type == "date"}}{{include tmpl = access + "_entity_field_text" /}}' +
			'{{else type == "period"}}{{include tmpl = access + "_entity_field_period" /}}' +
			'{{else type == "password"}}{{include tmpl = access + "_entity_field_password" /}}' +
			'{{else type == "bool"}}{{include tmpl = access + "_entity_field_bool" /}}' +
			'{{else type == "link"}}{{include tmpl = access + "_entity_field_link" /}}' +
			'{{else type == "img"}}{{include tmpl = access + "_entity_field_img" /}}' +
			'{{else type == "recaptcha"}}{{include tmpl = access + "_entity_field_recaptcha" /}}' +
			'{{/if}}' +
	'</div></div>{{/if}}{{/for}}{{/if}}</div><!-- /.form-horizontal -->',

	edit_entity_field_label: '<p id="field_{{:id}}_value" class="form-control-static">{{if ((id==\'id\' && (value==\'-1\' || value==null)) || (value==null && nullSubstitute)) }}<span class="text-muted">{{:nullSubstitute}}</span>{{else}}{{:value}}{{/if}}</p>',
	show_entity_field_label: '{{include tmpl="edit_entity_field_label"/}}',
	edit_entity_field_text: '<input type="{{:type}}" class="form-control" id="field_{{:id}}_value" placeholder="{{:name}}" value="{{:value}}" {{if max}} maxlength="{{:max}}"{{/if}}>',
	show_entity_field_text: '<p id="field_{{:id}}_value" class="form-control-static">{{if value}}{{:value}}{{else nullSubstitute}}<span class="text-muted">{{:nullSubstitute}}</span>{{/if}}</p>',
	//edit_entity_field_datetime: '<input type="text" class="form-control" id="field_{{:id}}_value" placeholder="гггг-мм-дд чч:мм:сс" value="{{:value}}">',
	//show_entity_field_datetime: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',
	edit_entity_field_period: '<div class="form-group">' +
	'<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6"><div class="input-group">' +
		'<span class="input-group-addon">{{:name1}}&nbsp;&nbsp;</span>' +
		'<input type="date" class="form-control" id="field_{{:id}}_value1" placeholder="" value="{{:value1}}">' +
	'</div></div>' +
	'<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6"><div class="input-group">' +
		'<span class="input-group-addon">{{:name2}}</span>' +
		'<input type="date" class="form-control" id="field_{{:id}}_value2" placeholder="" value="{{:value2}}">' +
	'</div></div></div>',
	show_entity_field_period: '<p id="field_{{:id}}_value" class="form-control-static">{{if value1 && value2}}с {{:value1}} по {{:value2}}{{else value1}}с {{:value1}}{{else value2 }}до {{:value2}}{{else}}{{:value}}{{/if}}</p>',
	edit_entity_field_textarea: '<textarea rows="3" class="form-control" id="field_{{:id}}_value" placeholder="{{:name}}">{{:value}}</textarea>',
	show_entity_field_textarea: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',
	edit_entity_field_password: '<div class="row"><div class="col-lg-6"><div class="input-group">' +
	'<input id="field_{{:id}}_value" type="password" class="form-control" placeholder="Новый пароль" onkeyup="checkPasswordStrength(this, \'field_{{:id}}_value\');"/>' +
	'<span class="input-group-addon" title="Показать/скрыть пароль"  onclick="togglePasswordMask(this, \'field_{{:id}}_value\');"><span class="glyphicon glyphicon-eye-open"></span></span></div><label id="pass_strength_result" style="display:none;"></label></div><div class="col-lg-6"><div class="input-group">' +
	'<input id="password2" type="password" class="form-control" placeholder="Повторите новый пароль" onkeyup="checkPasswordEq(this, \'field_{{:id}}_value\');"/><span class="input-group-addon" title="Показать/скрыть пароль"><span class="glyphicon glyphicon-asterisk"></span></span></div><label id="pass_eq_result" style="display:none;"></label></div></div>',
	show_entity_field_password: '<p id="field_{{:id}}_value" class="form-control-static">**********</p>',
	edit_entity_field_bool: '<input type="checkbox" id="field_{{:id}}_value" {{if value == 1}}checked="checked"{{/if}} value="">',
	show_entity_field_bool: '<input type="checkbox" id="field_{{:id}}_value" {{if value == 1}}checked="checked"{{/if}} value="" disabled>',
	edit_entity_field_select: '<select id="field_{{:id}}_value" class="form-control" style="width:auto;">' +
	'{{if value == null || value==\'\' || value_id == null || value_id==\'\'}}<option disabled selected="selected" value> --- Выберите --- </option>{{/if}}' +
	'{{if nullSubstitute && nullSubstitute != \'undefined\'}}<option {{if value == nullSubstitute}}selected="selected"{{/if}} value="*">{{:nullSubstitute}}</option>{{/if}}' +
	'{{if style == "id" tmpl="edit_entity_field_select_id_style_options"}}' +
	'{{else tmpl="edit_entity_field_select_text_style_options"}}' +
	'{{/if}}' +
	'</select>',
	show_entity_field_select: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',
	// entity_field_select_id_style_options - список заполняется из справочника сущностей и имеет идентификаторы
	// entity_field_select_text_style_options - список заполняется текстовыми значениями и не имеет идентификаторов
	edit_entity_field_select_id_style_options: '{{for values ~value_id=value_id}}<option value="{{:id}}" {{if id==~value_id}}selected="selected"{{/if}}>{{:name}}</option>{{/for}}',
	edit_entity_field_select_text_style_options: '{{for values ~value=value}}<option value="{{:#index}}" {{if #data==~value}}selected="selected"{{/if}}>{{:#data}}</option>{{/for}}',

	edit_entity_field_img: '<div id="field_{{:id}}_{{:~descriptor.fields.id.value}}_preview" class="row dropzone-previews" style="display: flex;"><div id="field_{{:id}}_{{:~descriptor.fields.id.value}}" class="dropzone col-lg-3 bg-success" style="display: flex; align-items: center;"><button class="btn btn-success center-block" id="field_{{:id}}_{{:~descriptor.fields.id.value}}_addbutton"><i class="glyphicon glyphicon-plus"></i></button></div></div>',

	show_entity_field_img: '{{if files && files.length > 0 }}<div class="row">{{for files}}<div class="col-lg-3"><a href="#" class="thumbnail"><img src="{{:url}}" alt="{{if ~descriptor.entityNameLC && ~descriptor.fields && ~descriptor.fields.name}}{{:~descriptor.fields.name.value}}{{/if}}"/></a></div>{{/for}}</div>{{/if}}',

	edit_entity_field_link: '<div class="input-group"><input type="text" class="form-control" id="field_{{:id}}_value" placeholder="" value="{{:value}}" readonly><span class="input-group-btn"><button class="btn btn-default" type="button"  aria-label="{{:name}}"  name="{{:id}}" onclick="link_entity(\'{{:(~descriptor ? ~descriptor.localData.containerId : entity.localData.containerId)}}\', \'{{:controllerName}}\', \'{{:id}}\', \'radio\');"><span class="glyphicon glyphicon-link" aria-hidden="true"></span></button></span></div>',
	show_entity_field_link: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',

	edit_entity_field_amount: '<input type="text" class="form-control" id="field_{{:id}}_value" placeholder="{{:name}}" value="{{:value}}" pattern="^\d*(\.\d{1,2}$)?" size="18">',
	show_entity_field_amount: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',

	edit_entity_field_recaptcha: '<div id="{{:id}}" class="g-recaptcha" data-sitekey="{{:value}}"></div><!--<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>-->',
	show_entity_field_recaptcha: '<div id="{{:id}}" class="g-recaptcha" data-sitekey="{{:value}}"></div>',

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
				else html += '<li><a href="#" onclick="change_page(\'' + descriptor.localData.containerId + '\', ' + i.toString() + ');">' + i.toString() + '</a></li>\n';
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
		/*toJSON: function(obj) {
			return JSON.stringify(obj);
		},*/
		isFieldRequired: function(field) {
			//console.log(field);
			if(field.type == "period" && field.required && field.required > 0) return true;
			else if(field.required && field.required == 2) return true;
			return false;
		},
		getVisibleColumns: function(columns) {
			//console.log(field);
			var count = columns.length;
			for (var columnID in columns) if(columns[columnID].hidden) count--;
			return count;
		},
		checksHasError: checksHasError,
		t: function(code, params) {
			return app.t._(code, params);
		},
		log: function(data) {
			console.log(data);
			
			return (JSON.stringify(data));
		},
		dbg: function(data) {
					
			return "qwe";
		},
	}
});

//alert($);


