/* Cохраняет данные сущности с ЭФ локально, а затем на сервер. Предварительно проверяет данные
* @container_id - идентификатор контейнера сущности (располагается либо на отдельной странице, либо в модалке)
*/
function entitySave(container_id) {
	var def = $.Deferred();
	if(container_id) {
		// проверяем поля сущности
		$.when(entityCheck(container_id)).done(function (data) {
			// data = {json - ответ сервера, descriptor - описатель сохраняемой сущности}
			// если в результате проверки есть сообщения, то надо их вывести
			if(data.json.checkResult) {
				showChecksModal(data);
				def.reject();
			}
			else {
				// если сообщений нет, то сохраняем сущность
				//return checkedEntitySave(data.descriptor);
				$.when(checkedEntitySave(data.descriptor)).done(function (descriptor) {
					def.resolve(descriptor);
				});
			}
		});
	}
	else {
		console.error("Function: entitySave. Parameter container_id not defined");
		def.reject();
	}
	return def;
}

/* Сохраняет и отправляет сущность
* @container_id - идентификатор контейнера сущности (располагается либо на отдельной странице, либо в модалке)
*/
function entitySend(container_id) {
	if(container_id) {
		var def = $.Deferred();
		// проверяем поля сущности
		$.when(entitySave(container_id)).done(function (descriptor) {
			if(descriptor.local_data.status == 'actual') {
				alert("Сущность отправлена");
			}
			else alert("Сущность не отправлена, т.к. не сохранена на сервер");
		});
	}
	else console.error("Function: entitySend. Parameter container_id not defined");
}

/* Cохраняет данные сущности с ЭФ локально, а затем на сервер, даже если есть предупреждения. Предварительно проверяет данные
* @container_id - идентификатор контейнера сущности (располагается либо на отдельной странице, либо в модалке)
*/
function mandatorySave(container_id) {
	if(container_id) {
		// проверяем поля сущности
		$.when(entityCheck(container_id)).done(function (data) {
			// data = {json - ответ сервера, descriptor - описатель сохраняемой сущности}
			// если в результате проверки нет сообщений или есть предупреждения, то все равно сохраняем
			var hasErrors = false;
			if(!data.json.checkResult || (data.json.checkResult && !checksHasError(data.json.checkResult))) {
				data.descriptor.local_data.mandatorySave = true;
				return checkedEntitySave(data.descriptor);
			}
		});
	}
	else console.error("Function: mandatorySave. Parameter container_id not defined");
}

function checksHasError(checkResult) {
	if(checkResult) {
		var checkResultLength = checkResult.length;
		for(var i=0; i< checkResultLength; i++) {
			//console.log(checkResult[i].type);
			if(checkResult[i].type == "error") return true;
		}
	}
	return false;
}

function checkedEntitySave(descriptor)  {
	// сохраняем на сервер новые записи в скроллерах с типом связи 'n', т.к. они еще не имеют нормального id, также у них нет container_id (т.к. они не отображаются отдельно), а также измененные записи из дочерних скроллеров
	var deferreds = [];
	if(descriptor.scrollers) {
		for (var key in descriptor.scrollers) {
			var scroller = descriptor.scrollers[key];
			if(scroller.relationType == 'n' )	{
				// сохраняем добавленные сущности
				if(scroller.local_data.added_items) {
					var len = scroller.local_data.added_items.length;
					for (var i = 0; i<len; i++) {
						deferreds.push(checkedEntitySave(scroller.local_data.added_items[i]));
					}
				}
				// сохраняем измененные сущности
				var len = scroller.items.length;
				for (var i = 0; i<len; i++) {
					if(scroller.items[i].local_data.status == 'edited') deferreds.push(checkedEntitySave(scroller.items[i]));
				}
			}
		}
	}
	
	var def = $.Deferred();

	//var res = null;
	// привязываем сохранение на сервер сразу после сохранения сущностей дочерних скроллеров
	var res = $.when.apply($, deferreds);
	res.done(function() {
		// текущую сущность сохраняем на сервер, если она отредактирована или связана с чем-то по типу связи 'n' и сейчас открыта на редактирование.
		//if((!descriptor.local_data.relationType || descriptor.local_data.relationType != 'n' || !descriptor.local_data.container_id) && descriptor.local_data.status == 'edited') {
		// если сохраняем сущность, открытую на редактирование, то сохраняем поля формы локально
		if(descriptor.local_data.container_id) entitySaveToLocalFromHTML(descriptor.local_data.container_id);
		// если сохраняем отредактированную сощность, то на сервер ее сохраняем только если она отредактирована и при выполнении одного из следующих условий:
		// 1. сущность связана по типу связи 'n' и не открыта на редактирование (т.е. сохранение инициировано не с ее формы редактирования)
		// 2. сущность связана по типу, отличному от 'n' и открыта на редактирование (т.е. сохранение инициировано с ее формы редактирования)
		var needSave = false
		if(descriptor.local_data.status == 'edited') { 
			if(	(descriptor.local_data.relationType && descriptor.local_data.relationType == 'n' && !descriptor.local_data.container_id) || 
				(!(descriptor.local_data.relationType && descriptor.local_data.relationType == 'n') && descriptor.local_data.container_id)) {
				needSave = true;
				$.when(entitySaveToServer(descriptor)).done(function() {
					descriptor.local_data.status = "actual";
					def.resolve();
				});
			}
		}
		if(!needSave) def.resolve(descriptor);
	});
	
	def.done(function() {
		// если сущность открывалась на редактирование для другого контейнера, то надо его обновить и закрыть модалку
		if(descriptor.local_data.target_container_id) {
			//var container = containers[descriptor.local_data.container_id];
			
			// перерисовываем скроллер
			var tContainerData = containers[descriptor.local_data.target_container_id].data;
			
			// добавляем сущность в скроллер
			var opts = {
				confirmFromServer: ((descriptor.local_data.relationType && descriptor.local_data.relationType == 'n') || !descriptor.local_data.container_id) ? false : true,
				//confirmFromServer: ((descriptor.local_data.relationType && descriptor.local_data.relationType == 'n') || !container_id) ? true : false,
			}
			if(tContainerData.type == 'scroller') addItemToScroller(descriptor, tContainerData, opts);
			
			// перерисовываем грид/скроллер
			renderScroller(tContainerData, false);
			
			hideModal(containers[descriptor.local_data.container_id].parent_container_id);
		}
		else if(descriptor.local_data.container_id && containers[descriptor.local_data.container_id].parent_container_id) hideModal(containers[descriptor.local_data.container_id].parent_container_id);
	});
	
	return def;
}
		
function entityCheckOnly(container_id, item) {
	$.when(entityCheck(container_id)).done(function (data) {
		showChecksModal(data);
	});
}

function showChecksModal(data) {
	var modal_container_id = "modal_checks";
			
	data.descriptor.checkResult = data.json.checkResult;
	
	var tmplLoader = getTemplateByName('ckecks_modal_template');
	tmplLoader.done(function(tmplLoader) {
		
		var html = tmplLoader.tmpl.render({descriptor:data.descriptor, modal_container_id:modal_container_id});

		var modalsJQ = $("#container_modals");
		var mJQ = modalsJQ.find("#" + modal_container_id);
		if(mJQ.length>0) mJQ.replaceWith(html);
		else modalsJQ.append(html);
		containers[modal_container_id] = {jqobj:modalsJQ.find("#" + modal_container_id), data: data.descriptor};
					
		// показываем модалку
		showModal(modal_container_id);
	});
}

function entityCheck(container_id) {
	// описатель сохраняемой сущности
	var descriptor;
	var jqobj;
	var def = $.Deferred();
	if(container_id) {
		descriptor = containers[container_id].data;
		jqobj = containers[container_id].jqobj;
	}
	else return def.resolve();
	// объект jquery контейнера сущности, которую надо проверить
	//var jqobj = containers[container_id].jqobj;
	
	
	// собираем данные формы
	
	// собираем поля формы по идентификаторам в локальной сущности
	if(descriptor.fields) {
		var field, field_jqobj, val, val_id;
		var tmpFields = {};
		var error={messages:[],};
		
		var checkFields = {};
		
		for(field_id in descriptor.fields) {
			val = null, val1 = null, val2 = null;
			val_id = null;
			field = descriptor.fields[field_id];
			
			// все, кроме статичного текста и операций, можно сохранять
			if(field.type != 'label' && field.id != "operations" && field.access && field.access == 'edit') {
				if(field.type=='text' || field.type=='textarea' || field.type=='number' || field.type=='email' || field.type=='password' || field.type=='date' || field.type=='amount') {
					field_jqobj = jqobj.find("#field_"+field.id+"_value");
					if(field_jqobj.length>0) val = field_jqobj.val();
					else val = null;
					if(field.type=='password' && val != null && val != '') val = sha1(val);
				}
				else if(field.type=='period') {
					field_jqobj1 = jqobj.find("#field_"+field.id+"_value1");
					field_jqobj2 = jqobj.find("#field_"+field.id+"_value2");
					if(field_jqobj1.length>0) val1 = field_jqobj1.val();
					else val1 = null;
					if(field_jqobj2.length>0) val2 = field_jqobj2.val();
					else val2 = null;
				}
				else if(field.type=='select') {
					field_jqobj = jqobj.find("#field_"+field.id+"_value");
					if(field_jqobj.val()) {
						val = field_jqobj.find("option:selected").text();
						val_id = field_jqobj.val();
					}
					else {
						val = null;
						val_id = null;
					}
				}
				else if(field.type=='link') {
					//значение сохраняется в сущность при закрытии модалки со скроллером
					val = field.value;
					val_id = field.value_id;
					/*field_jqobj = jqobj.find("#field_"+field.id+"_value");
					if(field_jqobj.length>0) val = field_jqobj.val();
					else val = "";*/
				}
				else if(field.type=='recaptcha') {
					//значение сохраняется в сущность при закрытии модалки со скроллером
					val = field.value;
				}
				
				if(val == '' || val == 'undefined') val = null;
				if(val1 == '' || val1 == 'undefined') val1 = null;
				if(val2 == '' || val2 == 'undefined') val2 = null;
				if(val_id == '' || val_id == 'undefined') val_id = null;
				
				checkFields[field_id] = {
					id: field_id,
					value: val,
					value1: val1,
					value2: val2,
					value_id: val_id,
				};
			}
		}
		checkFields['id'] = {
			id: 'id',
			value: descriptor.fields['id']['value'],
		};
		
		var data = {
			fields: checkFields,
		}
		
		if(descriptor.scrollers) {
			data.scrollers = {};
			for (var key in descriptor.scrollers) {
				var scroller = descriptor.scrollers[key];
				data.scrollers[key] = {};
				// сохраняем добавленные сущности
				if(scroller.local_data.added_items) {
					data.scrollers[key].added_items = [];
					var len = scroller.local_data.added_items.length;
					for (var i = 0; i<len; i++) {
						// TODO. Пока что не проверяем не сохраненные связанные записи (id=-1). Надо их тоже передавать и проверять
						if(scroller.local_data.added_items[i].fields.id.value != "-1") data.scrollers[key].added_items.push(scroller.local_data.added_items[i].fields.id.value);
					}
				}
				// удаляем удаленные сущности
				if(scroller.local_data.deleted_items) {
					data.scrollers[key].deleted_items = [];
					var len = scroller.local_data.deleted_items.length;
					for (var i = 0; i<len; i++) data.scrollers[key].deleted_items.push(scroller.local_data.deleted_items[i].fields.id.value);
				}
			}
		}
		
		//console.log(data);
		//console.log($.toJSON(data));
		
		$.ajax({
			//url: '/' + descriptor.controllerName + '/check?id=' + descriptor.fields.id.value,
			url: '/' + descriptor.controllerName + '/save?id=' + descriptor.fields.id.value + '&check_only=1',
			dataType: 'json',
			//data:  $.toJSON(data),
			data:  JSON.stringify(data),
			method: 'post',
			beforeSend: function() {
				// показываем прогрессбар
			},
			complete: function() {
				// скрываем прогрессбар
			},			
			success: function(json) {
				//console.log("Сущность проверена");
				//console.log(json);
				
				// если нет ошибок
				if(!handleAjaxError(json.error)) {
					def.resolve({json: json, descriptor:descriptor});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				handleAjaxError({
					messages: [{
						title: 'Ошибка обмена данными',
						msg: 'Ошибка обработки запроса на стороне сервера. Обратитесь в службу поддержки',
					}],
				});
				def.reject();
			}
		});
	}
	return def;
}

/* Удаляет сущность с сервера. Вызывается из страницы скроллера или состраницы редактирования сущности
* @container_id - идентификатор контейнера скроллера или сущности (располагается либо на отдельной странице, либо в модалке)
* @id - ID сущности, которую надо удалить, передается при вызове из кода для удаления на сервере
*/
function entityDelete(container_id, id) {
	var scroller;
	// родительский контейнер: скроллер/грид, с элементом которого работаем, или сущность
	var container = containers[container_id];
	// описатель сущности, которую надо удалить
	var entity = entities[container.data.entityNameLC][id];
	
	var def = $.Deferred();
	if(entity.fields.id.value==-1) {
		delete entities[container.data.entityNameLC][id];
		def.resolve();
	}
	else {
		confirmDelete(container, id).done(function (){ 
			$.ajax({
				url: '/' + entity.entityNameLC + '/delete?id=' + id,
				dataType: 'json',
				method: 'get',
				beforeSend: function() {
						// показываем прогрессбар
				},
				complete: function() {
					// скрываем прогрессбар
				},			
				success: function(json) {
					if(!handleAjaxError(json.error)) {
						// удаляем локальную сущность
						//entity.local_data.status = 'deleted';
						//deleteItemFromScroller(scroller);
						
						handleAjaxSuccess(json.success)
						//console.log("С сервера получены полные данные сущности скроллера:");
						console.log(json);
						
						// TODO. Уведомить открытые скролеры и сущности, что сущность удалена
						// если удаляем запись скроллера
						if(container.data.type == 'scroller') {
							//deleteItemFromScroller(entity, container.data, {confirmFromServer:true});
							
							// перерисовываем скроллер
							//renderScroller(container.data, false);
							def.resolve();
						}
						// если удаляем сущность
						else {
							def.resolve();
							// закрываем модалку или переходим к скроллеру сущностей
							if(container.parent_container_id) {
								//var parent_container = containers[container.parent_container_id)
								hideModal(container.parent_container_id);
								
							}
							else if(json.redirectURL) {
								setTimeout(function(){
									document.location = json.redirectURL;
								}, 1000);
							}
						}
					}
					else def.reject();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					handleAjaxError({
						messages: [{
							title: 'Ошибка обмена данными',
							msg: 'Ошибка обработки запроса на стороне сервера. Обратитесь в службу поддержки',
						}],
					});
					def.reject();
				}
			});  
		});
	}
	return def;
}

/* Cохраняет данные формы локально
* @container_id - идентификатор контейнера сущности
*/
function entitySaveToLocalFromHTML(container_id) {
	// описатель сущности, которую надо сохранить
	var descriptor = containers[container_id].data;
	// объект jquery контейнера сущности, которую надо сохранить
	var jqobj = containers[container_id].jqobj;
	
	// собираем поля формы по идентификаторам в локальной сущности
	if(descriptor.fields) {
		var field, field_jqobj, val, val_id;
		var tmpFields = {};
		var error={messages:[],};
		var errorMsg = '';
		
		for(field_id in descriptor.fields) {
			val = null, val1 = null, val2 = null;
			val_id = null;
			field = descriptor.fields[field_id];
			
			//if(!field.required || field.required == "undefined") field.required = null;
			
			// все, кроме статичного текста и операций, можно сохранять, поле д.б. доступно для редактирования
			if(field.type != 'label' && field.id != "operations" && field.access && field.access == 'edit') {
				if(field.type=='text' || field.type=='textarea' || field.type=='number' || field.type=='email' || field.type=='password' || field.type=='date' || field.type=='amount') {
					field_jqobj = jqobj.find("#field_"+field.id+"_value");
					if(field_jqobj.length>0) val = field_jqobj.val();
					else val = null;
					if(field.type=='password' && val != null && val != '') val = sha1(val);
				}
				else if(field.type=='bool') {
					field_jqobj = jqobj.find("#field_"+field.id+"_value");
					if(field_jqobj.length>0 && field_jqobj.is(':checked')) val = 1;
					else val = 0;
				}
				else if(field.type=='period') {
					field_jqobj1 = jqobj.find("#field_"+field.id+"_value1");
					field_jqobj2 = jqobj.find("#field_"+field.id+"_value2");
					if(field_jqobj1.length>0) val1 = field_jqobj1.val();
					else val1 = null;
					if(field_jqobj2.length>0) val2 = field_jqobj2.val();
					else val2 = null;
				}
				else if(field.type=='select') {
					field_jqobj = jqobj.find("#field_"+field.id+"_value");
					if(field_jqobj.val()) {
						val = field_jqobj.find("option:selected").text();
						val_id = field_jqobj.val();
					}
					else {
						val = null;
						val_id = null;
					}
				}
				else if(field.type=='link') {
					// значение сохраняется в сущность при закрытии модалки со скроллером, поэтому здесь можно взять оттуда
					val = field.value;
					val_id = field.value_id;
					/*field_jqobj = jqobj.find("#field_"+field.id+"_value");
					if(field_jqobj.length>0) val = field_jqobj.val();
					else val = "";*/
				}
				else if(field.type=='recaptcha') {
					//значение сохраняется в сущность при закрытии модалки со скроллером
					val = field.value;
				}
				
				if(val === '' || val == 'undefined') val = null;
				if(val1 === '' || val1 == 'undefined') val1 = null;
				if(val2 === '' || val2 == 'undefined') val2 = null;
				if(val_id === '' || val_id == 'undefined') val_id = null;
				
				descriptor.fields[field_id].value = val;
				descriptor.fields[field_id].value1 = val1;
				descriptor.fields[field_id].value2 = val2;
				descriptor.fields[field_id].value_id = val_id;
			}
		}
		
		// обрабатка скроллеров не выполняется, т.к. сущности в скроллере уже в группе "added_items", а сами сущности в состоянии "edited".
		// помечаем сущность, как измененную локально
		if(descriptor.local_data.status == 'actual') descriptor.local_data.status = "edited";
		
		// TODO. Сообщить остальным контейнерам, что сущность обновлена, а в текущем контейнере убрать такое уведомление, т.к. сущность актуальна
		
		return descriptor;
	}
}


/* Cохраняет данные сущности на сервер и возвращает Deffered
* @descriptor - описатель сущности, которую надо сохранить
*/
function entitySaveToServer(descriptor) {
	var def = $.Deferred();	// полный результат сохранения
	var deferreds = [];	// массив результатов, после успешного завершения которых получаем полный результат сохранения
	var entityDef = $.Deferred();	// результат сохранения сущности
	var container = containers[descriptor.local_data.container_id];
	
	// сохраняем сущность на сервер
	var fields = {};
	for (var fieldID in descriptor.fields) {
		var field = descriptor.fields[fieldID];
		if(field.type != "img") {
			fields[fieldID] = {
				id: field['id'],
				value: field['value'],
				value1: field['value1'],
				value2: field['value2'],
				value_id: field['value_id'],
			};
		}
	}
	
	// готовим записи скроллеров
	if(descriptor.scrollers) {
		var scrollers = {};
		for (var fieldID in descriptor.scrollers) {
			var scroller = descriptor.scrollers[fieldID];
			scrollers[fieldID] = {};
			// сохраняем добавленные сущности
			if(scroller.local_data.added_items) {
				scrollers[fieldID].added_items = [];
				var len = scroller.local_data.added_items.length;
				for (var i = 0; i<len; i++) {
					scrollers[fieldID].added_items.push(scroller.local_data.added_items[i].fields.id.value);
				}
			}
			// удаляем удаленные сущности
			if(scroller.local_data.deleted_items) {
				scrollers[fieldID].deleted_items = [];
				var len = scroller.local_data.deleted_items.length;
				for (var i = 0; i<len; i++) scrollers[fieldID].deleted_items.push(scroller.local_data.deleted_items[i].fields.id.value);
			}
		}
	}
	
	var data = {
		fields: fields,
	}
	if(scrollers) data.scrollers = scrollers;
	
	url = '/' + descriptor.controllerName + '/save?id=' + descriptor.fields.id.value;
	if(descriptor.local_data.mandatorySave) url += "&mandatory_save=1";
	
	$.ajax({
		url: url,
		dataType: 'json',
		//data:  $.toJSON(data),
		data:  JSON.stringify(data),
		method: 'post',
		beforeSend: function() {
			// показываем прогрессбар
		},
		complete: function() {
			// скрываем прогрессбар
		},			
		success: function(json) {
			if(true /*!json.checkResult || (json.checkResult && !checksHasError(json.checkResult))*/) {
			
				//console.log("Сущность сохранена на сервер");
				console.log(json);
				if(!descriptor.local_data.relationType || descriptor.local_data.relationType != 'n') handleAjaxSuccess(json.success);
				// если нет ошибок
				if(!handleAjaxError(json.error)) {
					if(json.newID) {
						descriptor.fields.id.value = json.newID;
						entities[descriptor.entityNameLC][json.newID] = entities[descriptor.entityNameLC][descriptor.local_data.eid];
						delete entities[descriptor.entityNameLC][descriptor.local_data.eid];
						descriptor.local_data.eid = json.newID;
					}
					descriptor.local_data.status = 'actual';
					
					// очищаем локальные списки добавленных/удаленных элементов
					if(descriptor.scrollers) {
						for(var key in descriptor.scrollers) {
							var scroller = descriptor.scrollers[key];
							if(scroller.local_data.added_items) {
								var len = scroller.local_data.added_items.length;
								for (var i = len-1; i>=0; i--) {
									scroller.items.unshift(scroller.local_data.added_items[i]);
								}
								delete scroller.local_data.added_items;
							}
							var len = scroller.items.length;
							for (var i = 0; i<len; i++) {
								scroller.items[i].local_data.status = 'actual';
							}
							delete scroller.local_data.deleted_items;
							
							// и перерисовываем скроллеры
							renderScroller(scroller, false);
						}
					}
					
					entityDef.resolve({descriptor: descriptor, container: container});
				}
				else entityDef.reject();
			}
			else {
				showChecksModal({descriptor:descriptor, json:json});
				entityDef.reject();
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			handleAjaxError({
				messages: [{
					title: 'Ошибка обмена данными',
					msg: 'Ошибка обработки запроса на стороне сервера. Обратитесь в службу поддержки',
				}],
			});
			entityDef.reject();
		}
	});
	
	// если сохранение данных на сервер успешно, то сохраняем файлы
	entityDef.done(function(data) {
		var descriptor = data.descriptor;
		
		// загружаем и удаляем файлы
		for (var fieldID in descriptor.fields) {
			var field = descriptor.fields[fieldID];
			if(descriptor.fields[fieldID].type == "img"  && descriptor.fields[fieldID]['access'] == 'edit') {
				if(descriptor.local_data.fields && descriptor.local_data.fields[fieldID]) {
					var localField = descriptor.local_data.fields[fieldID];
					
					deferreds.push(localField.deferredUpload);
					if(localField.deletedFiles) {
						deferreds.push(localField.deferredDelete);
					}
					if(localField.uploader.getQueuedFiles().length>0) {
						localField.uploader.processQueue();
					}
					else localField.deferredUpload.resolve();
				}
			}
		}
		$.when.apply($, deferreds).done(function(){	
			def.resolve();
		});
	});
	
    
	return def;
}

/* Удаляет файл из поля после успешной загрузки
* @descriptor - описатель сущности
* @fieldName - наименование поля сущности, для которого выполняется удаление
*/
function deleteEntityFiles(descriptor, fieldName) {
	var dfiles;
	if(!descriptor.local_data.fields || !descriptor.local_data.fields[fieldName] || !descriptor.local_data.fields[fieldName].deletedFiles || descriptor.local_data.fields[fieldName].deletedFiles.length==0) {
		if(descriptor.local_data.fields[fieldName].deferredDelete) descriptor.local_data.fields[fieldName].deferredDelete.resolve();
		return;
	}
	dfiles = descriptor.local_data.fields[fieldName].deletedFiles;
	
	//var deferreds = [];
	
	//var dfilesLength = dfiles.length;
	var data = {
		files: dfiles,
		parent_entity_id: descriptor.local_data.eid,
		parent_entity_name: descriptor.entityName,
		parent_entity_field: fieldName,
	}

	//for(var i=0; i < dfilesLength; i++) {
		//var dfile = dfiles[i];
	$.when($.ajax({
		url: '/file/delete',
		dataType: 'json',
		//data:  data,//$.toJSON(data),
		data: JSON.stringify(data),
		method: 'post',
		beforeSend: function() {
			// показываем прогрессбар
		},
		complete: function() {
			// скрываем прогрессбар
		},			
		success: function(json) {
			//console.log("Сущность сохранена на сервер");
			console.log(json);
			//handleAjaxSuccess(json.success);
			// если нет ошибок
			if(!handleAjaxError(json.error)) {
				delete descriptor.local_data.fields[fieldName].deletedFiles;
				
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			handleAjaxError({
				messages: [{
					title: 'Ошибка обмена данными',
					msg: 'Ошибка обработки запроса на стороне сервера. Обратитесь в службу поддержки',
				}],
			});
		}
	})).done(function(){
		descriptor.local_data.fields[fieldName].deferredDelete.resolve();
	}).fail(function(){
		descriptor.local_data.fields[fieldName].deferredDelete.reject();
	});
	//}
}

