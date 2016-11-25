$.templates({
//expenselist_row: '<td name="id" id="{{:fields.id.value}}">{{:fields.id.value}}</td><td>{{:fields.name.value}}</td><td>{{:fields.expense_type.value}}</td><td>{{for ~descriptor.item_operations tmpl="item_operation" ~entity=#data /}}</td>',
expenselist_row: '{{include tmpl="scroller_row" /}}',

//expensetypelist_row: '<td name="id" id="{{:fields.id.value}}">{{:fields.id.value}}</td><td>{{:fields.name.value}}</td><td>{{for ~descriptor.item_operations tmpl="item_operation" ~entity=#data /}}</td>',
expensetypelist_row: '{{include tmpl="scroller_row" /}}',

//organizationlist_row: '<td name="id" id="{{:fields.id.value}}">{{:fields.id.value}}</td><td>{{:fields.name.value}}</td><td>{{:fields.region.value}}</td><td>{{:fields.contacts.value}}</td><td>{{:fields.email.value}}</td><td>{{for ~descriptor.item_operations tmpl="item_operation" ~entity=#data /}}</td>',
organizationlist_row: '{{include tmpl="scroller_row" /}}',

//resourcelist_row: '<td name="id" id="{{:fields.id.value}}">{{:fields.id.value}}</td><td>{{:fields.group.value}}</td><td>{{:fields.controller.value}}</td><td>{{:fields.action.value}}</td><td>{{:fields.module.value}}</td><td>{{:fields.description.value}}</td><td>{{for ~descriptor.item_operations tmpl="item_operation" ~entity=#data /}}</td>',
resourcelist_row: '{{include tmpl="scroller_row" /}}',

//settinglist_row: '<td name="id" id="{{:fields.id.value}}">{{:fields.id.value}}</td><td>{{:fields.code.value}}</td><td>{{:fields.value.value}}</td><td>{{:fields.description.value}}</td><td>{{for ~descriptor.item_operations tmpl="item_operation" ~entity=#data /}}</td>',
settinglist_row: '{{include tmpl="scroller_row" /}}',

//userlist_row: '<td name="id" id="{{:fields.id.value}}">{{:fields.id.value}}</td><td><input type="checkbox" disabled {{if fields.active.value==1}}checked="checked"{{/if}}/></td><td>{{:fields.phone.value}}</td><td>{{:fields.email.value}}</td><td>{{:fields.name.value}}</td><td>{{:fields.user_role.value}}</td><td>{{for ~descriptor.item_operations tmpl="item_operation" ~entity=#data /}}</td>',
userlist_row: '{{include tmpl="scroller_row" /}}',

//userrolelist_row: '<td name="id" id="{{:fields.id.value}}">{{:fields.id.value}}</td><td><input type="checkbox" disabled {{if fields.active.value==1}}checked="checked"{{/if}}/></td><td>{{:fields.name.value}}</td><td>{{for ~descriptor.item_operations tmpl="item_operation" ~entity=#data /}}</td>',
userrolelist_row: '{{include tmpl="scroller_row" /}}',

regionlist_row: '{{include tmpl="scroller_row" /}}',
newslist_row: '{{include tmpl="scroller_row" /}}',
streettypelist_row: '{{include tmpl="scroller_row" /}}',

edit_entity_field_label: '<p id="field_{{:id}}_value" class="form-control-static">{{if (id==\'id\' && (value==\'-1\' || value==null)) }}-{{else}}{{:value}}{{/if}}</p>',
show_entity_field_label: '<p id="field_{{:id}}_value" class="form-control-static">{{if (id==\'id\' && (value==\'-1\' || value==null)) }}-{{else}}{{:value}}{{/if}}</p>',
edit_entity_field_text: '<input type="{{:type}}" class="form-control" id="field_{{:id}}_value" placeholder="{{:name}}" value="{{:value}}">',
show_entity_field_text: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',
edit_entity_field_textarea: '<textarea rows="3" class="form-control" id="field_{{:id}}_value" placeholder="{{:name}}">{{:value}}</textarea>',
show_entity_field_textarea: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',
edit_entity_field_password: '<p id="field_{{:id}}_value" class="form-control-static">**********</p>',
edit_entity_field_bool: '<input type="checkbox" id="field_{{:id}}_value" {{if value == 1}}checked="checked"{{/if}} value="" disabled>',
edit_entity_field_select: '<select id="field_{{:id}}_value" class="form-control" style="width:auto;">{{if (nullable && nullable==1) }}<option value="*" {{if value_id==\'\'}}selected="selected"{{/if}}></option>{{:value_id}}{{/if}}\
{{if style == "id" tmpl="edit_entity_field_select_id_style_options"}}\
{{else tmpl="edit_entity_field_select_text_style_options"}}{{/if}}</select>',
show_entity_field_select: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',
// entity_field_select_id_style_options - список заполняется из справочника сущностей и имеет идентификаторы
// entity_field_select_text_style_options - список заполняется текстовыми значениями и не имеет идентификаторов
edit_entity_field_select_id_style_options: '{{for values ~value_id=value_id}}<option value="{{:id}}" {{if id==~value_id}}selected="selected"{{/if}}>{{:name}}</option>{{/for}}',
edit_entity_field_select_text_style_options: '{{for values ~value=value}}<option value="{{:#index}}" {{if #data==~value}}selected="selected"{{/if}}>{{:#data}}</option>{{/for}}',
edit_entity_field_img: '<input id="field_{{:id}}" class="file" type="file" {{if max_count && max_count > 1}} name="file[]" multiple="true"{{else}}name="file"{{/if}}{{if min_count && min_count > 0}} mandatory{{/if}}> \
<input hidden="" id="eid" value="{{:~descriptor.local_data.eid}}"> \
<input hidden="" id="entity" value="{{:~descriptor.entity}}"> \
<input hidden="" id="field" value="{{:id}}"> \
<input hidden="" id="min_count" value="{{:min_count}}"> \
<input hidden="" id="max_count" value="{{:max_count}}">',
show_entity_field_img: '<input id="field_{{:id}}" class="file" type="file" {{if max_count && max_count > 1}} name="file[]" multiple="true"{{else}}name="file"{{/if}}{{if min_count && min_count > 0}} mandatory{{/if}}> \
<input hidden="" id="eid" value="{{:~descriptor.local_data.eid}}"> \
<input hidden="" id="entity" value="{{:~descriptor.entity}}"> \
<input hidden="" id="field" value="{{:id}}"> \
<input hidden="" id="min_count" value="{{:min_count}}"> \
<input hidden="" id="max_count" value="{{:max_count}}">',

edit_entity_field_link: '<div class="input-group"><input type="text" class="form-control" id="field_{{:id}}_value" placeholder="" value="{{:value}}" readonly><span class="input-group-btn"><button class="btn btn-default" type="button"  aria-label="{{:name}}"  name="{{:id}}" onclick="link_entity(\'{{:(~descriptor ? ~descriptor.local_data.container_id : container_id)}}\', \'{{:controllerName}}\', \'{{:id}}\', \'radio\');"><span class="glyphicon glyphicon-link" aria-hidden="true"></span></button></span></div>',
show_entity_field_link: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',

edit_entity_field_amount: '<input type="text" class="form-control" id="field_{{:id}}_value" placeholder="{{:name}}" value="{{:value}}" pattern="^\d*(\.\d{1,2}$)?" size="18">',
show_entity_field_amount: '<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>',

});
