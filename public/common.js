var containers = {};
var entities = {};

$(document).ready(function() {
	$.views.settings.debugMode(true); // DEBUG
	//$.views.settings.debugMode(onErrorHandler);
	
	$.notifyDefaults({
		type: 'danger',// warning, success
		icon: 'glyphicon glyphicon-star',
		icon_type: 'class',
		allow_dismiss: true,
		newest_on_top: true,
		//mouse_over: 'pause',
		delay: 5000,				
		//type: 'minimalist',
		z_index: 2031,
		placement: {
			from: "top",
			align: "center"
		},
	});
	
	if (typeof descriptor !== 'undefined') {
		console.log(descriptor);
		// если разбираем данные сущности
		if (descriptor.type == 'entity') {
			// сохраняем полученные данные
			// здесь только сохранение данных, отрисовки и создания контейнеров нет
			var entity = saveFullServerEntity(descriptor);
			delete descriptor;
			
			// присваиваем идентификаторы скролерам, чтобы при выводе основных данных сущности оставить метки для их размещения
			initEntityScrollers(entity);
			
			// рисуем сущность
			var container_id = renderEntity(entity);
			
			// рисуем  скроллеры сущности
			renderEntityScrollers(entity);
			initEntityScripts(container_id);
		}
		else if(descriptor.type == 'scroller') {
			// присваиваем идентификаторы скролеру
			initScroller(descriptor);
			
			// сохраняем в виде локальных сущностей записи скроллера
			saveScrollerItems(descriptor);
			
			// рисуем  скроллер
			renderScroller(descriptor, true);
		}
	}
	
	/*if ($.fn.select2) {
		$.fn.select2.defaults.set( "theme", "bootstrap");
		$("select.extended").select2();
	}*/
	
	/*$.notify({
		// options
		title: 'Ошибка',
		message: 'sdfsdfsdfsdfsdfsdfs sdf sdf df sdf sdf '
	},{
		// settings
		type: 'danger',// warning, success
		icon: 'glyphicon glyphicon-star',
		allow_dismiss: true,
		newest_on_top: true,
		mouse_over: 'pause',
		delay: 50000,
		z_index: 2031,
		placement: {
			from: "top",
			align: "center"
		},
	});
	*/
	/*
	$.notify({
		icon: 'https://randomuser.me/api/portraits/med/men/77.jpg',
		title: 'Byron Morgan',
		message: 'Momentum reduce child mortality effectiveness incubation empowerment connect.'
	},{
		type: 'minimalist',
		delay: 5000,
		icon_type: 'image',
		template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert">' +
			'<img data-notify="icon" class="img-circle pull-left">' +
			'<span data-notify="title">{1}</span>' +
			'<span data-notify="message">{2}</span>' +
		'</div>'
	});
	*/
});

/* Инициализирует скроллеры сущности
* @descriptor - описатель сущности
*/
function initEntityScrollers(descriptor) {
	// присваиваем идентификаторы скролерам, чтобы на привыводе основных данных сущности оставить метки для их размещения
	for (var key in descriptor.scrollers) {
		initScroller(descriptor.scrollers[key]);
	}
}

/* Инициализирует скроллер (инициализирует local_data, служебный скроллер, сохраняет кастомизированный шаблон)
* @descriptor - описатель скроллера
*/
function initScroller(descriptor) {
	if(!descriptor.local_data) {
		descriptor.local_data = {
			udid: createID()
		}
		createUDID(descriptor);
	}
	
	// охраняем кастомизированный шаблон, если он передан
	saveCustomTemplate(descriptor);
	
	// если скроллер предполагает наполнение через выбор строк в модалке, то создаем служебный скроллер
	/*if((!descriptor.add_style || ( descriptor.add_style && descriptor.add_style != "scroller")) && !descriptor.local_data.serviceScrollerContainerID) {
		//createServiceScroller(descriptor);
	}*/
}

function renderEntity(descriptor) {
	var container_id = descriptor.local_data.container_id;
	// если для сущности еще не был создан контейнер (первая отрисовка)
	if(!containers[container_id]) {
		// создаем контейнер для сущности
		containers[container_id] = {jqobj: $("#entity_form_placeholder"), data: descriptor};
	} 
	
	// отрисовываем основные данные сущности
	var tmpl = $.templates("#"+getTemplateName(descriptor));
	// отрисовываем
	var html = tmpl.render({descriptor:descriptor});
	
	// вставляем отрисованную сущность на место метки
	containers[container_id].jqobj.html(html);
	return container_id
}

function renderEntityScrollers(descriptor) {
	// обрабатываем скроллеры сущности, если они есть
	for (var key in descriptor.scrollers) {
		renderScroller(descriptor.scrollers[key]);
		containers[descriptor.scrollers[key].local_data.container_id].parent_container_id = descriptor.local_data.container_id;
	}
}

function renderScroller(descriptor, isRoot) {
	// обновляем описатель скроллера
	var container_id = descriptor.local_data.container_id;

	var tmpl = $.templates("#"+getTemplateName(descriptor));
	var html = tmpl.render({descriptor:descriptor});
	
	// если для скроллера еще не был создан контейнер (первая отрисовка)
	if(!containers[container_id]) {
		// создаем контейнер для сущности
		if(isRoot) containers[container_id] = {jqobj: $("#scroller_form_placeholder"), data: descriptor};
		else containers[container_id] = {jqobj: $("#placeholder_" + container_id), data: descriptor};
	} 
	
	containers[container_id].jqobj.html(html); 
	
	initScrollerScripts(container_id);
}

function saveScrollerItems(descriptor) {
	if(descriptor.items) {
		var itemsLength = descriptor.items.length;
		for (var i = 0; i<itemsLength; i++) {
			descriptor.items[i] = copyServerEntityDataToEntities(descriptor.items[i], descriptor.entity);
			//if(descriptor.relationType) descriptor.items[i].local_data.relationType = descriptor.relationType;
		}
	}
}

/* Разбирает информацию из объекта descriptor: отдельно поля сущности, отдельно скроллеры. Сущность и поля являются ссылками на объекты json или сторонние массивы, поэтому данные необходимо скопировать в entities
* @descriptor - описатель, полученный с сервера
* @server_entity_name - наименование сущности, полученной с сервера
*/
function saveFullServerEntity(descriptor) {
	// сохраняем кастомизированный шаблон, если он передан
	saveCustomTemplate(descriptor);
		
	var localEntity = copyServerEntityDataToEntities(descriptor);
			
	// раздербаниваем сущности из скроллеров, пришедших с сервера, а также сохраняем описатели самих скроллеров
	if(localEntity.scrollers) {
		for (var key in localEntity.scrollers) {
			var scroller = localEntity.scrollers[key];
			//localEntity.scrollers[scrollerController] = {items:localEntity.full_data.scrollers[key].items, full_data:localEntity.full_data.scrollers[key]};
			if(scroller.items) {
				var len = scroller.items.length;
				for (var i = 0; i<len; i++) {
					scroller.items[i] = copyServerEntityDataToEntities(scroller.items[i], scroller.entity);
					if(scroller.relationType) scroller.items[i].local_data.relationType = scroller.relationType;
				}
			}
		}
	}
	
	return localEntity;
}

/* Разбираем информацию из объекта descriptor
* @descriptor - описатель, полученный с сервера (описатель м.б. полный для полной сущности, или упрощенный - для сущности из скроллера)
* @entityName - наименование сущности, полученной с сервера; название сущности передается, когда определить сущность нельзя из описателя, т.е. при разборе сущности скроллера
*/
/*function copyServerEntityDataToEntities(descriptor, entityName) {
	if(!entityName) entityName = descriptor.entity;
	
	var eid;
	// если получена пустая сущность
	if(descriptor.fields.id.value == "-1") eid = createIDForNewEntities();
	else eid = descriptor.fields.id.value;
	
	// создаем класс в массиве entities
	if(!entities[entityName]) entities[entityName] = {};
	if(!entities[entityName][eid]) entities[entityName][eid] = {};
	
	var entity;
	
	// если локально не редактировали, то обновляем
	if(!entities[entityName][eid].local_data || (entities[entityName][eid].local_data && entities[entityName][eid].local_data.status != 'edited')) {
		// копируем то, что есть в объекте
		copyDescriptor(descriptor, entities[entityName][eid]);
		
		// переносим local_data, если сущность была сохранена локально или создаем новый local_data, если сущность создается
		if(!entities[entityName][eid].local_data) {
			entities[entityName][eid].type = 'entity';
			entities[entityName][eid].entity = entityName;
			entities[entityName][eid].controllerName = entityName;
			entities[entityName][eid].local_data = {
				status: 'actual',
				udid: createID(),
				eid:eid
			};
			createUDID(entities[entityName][eid]);
		}
		else entities[entityName][eid].local_data.status = 'actual';
	}
	
	return entities[entityName][eid];
}*/

function copyServerEntityDataToEntities(descriptor, entityName) {
	if(!entityName) entityName = descriptor.entity;
	
	var eid;
	// если получена пустая сущность
	if(descriptor.fields.id.value == "-1") eid = createIDForNewEntities();
	else eid = descriptor.fields.id.value;
	
	// создаем класс в массиве entities
	if(!entities[entityName]) entities[entityName] = {};
	if(!entities[entityName][eid]) entities[entityName][eid] = {};
	
	var entity;
	
	// если локально не редактировали, то обновляем
	if(!entities[entityName][eid].local_data || (entities[entityName][eid].local_data && entities[entityName][eid].local_data.status != 'edited')) {
		// копируем то, что есть в объектах
		copyDescriptor(descriptor, entities[entityName][eid]);
		
		// переносим local_data, если сущность была сохранена локально или создаем новый local_data, если сущность создается
		if(!entities[entityName][eid].local_data) {
			entities[entityName][eid].type = 'entity';
			entities[entityName][eid].entity = entityName;
			entities[entityName][eid].controllerName = entityName;
			entities[entityName][eid].local_data = {
				status: 'actual',
				udid: createID(),
				eid:eid
			};
			createUDID(entities[entityName][eid]);
		}
		else entities[entityName][eid].local_data.status = 'actual';
	}
	
	return entities[entityName][eid];
}

/* Копирует информацию из одного descriptor в другой
* @fromObj - описатель, который надо скопировать. М.б. записью из скроллера, т.е. дескриптором только со списком полей
* @toObj - описатель, в который надо скопировать. Не обязательный
*/
function copyDescriptor(fromObj, toObj) {
	// если описатель скроллера
	if(fromObj.type == "scroller") {
		if(!toObj) toObj = {};
		
		for (var key in fromObj) {
			// если свойство = null
			if(fromObj[key] == null) toObj[key] = null;
			/*else if(key=="items") {
				
				continue;
			}*/
			else if(key=="local_data") {
				// пропускаем, у каждого объекта свой local_data
				continue;
			}
			// простые свойства (строки и числа)
			// сложные свойства (объекты и массивы), но без перекрестных ссылок на другие объекты из entities и containers
			// если свойство - объект или массив
			else if (typeof fromObj[key] == "object") {
				// если массив
				if($.isArray(fromObj[key])) {
					toObj[key] = [];
					var len = fromObj[key].length;
					for(var i = 0; i < len; i++) toObj[key][i] = deepCopy(fromObj[key][i]);
				} 
				// если объект
				else toObj[key] = deepCopy(fromObj[key]);
			// если свойство - простой тип
			} else {
				toObj[key] = fromObj[key];
			}
			/*
			+add_filter: Object
			+add_style: "scroller"
			+columns: Object
			+common_operations: Array[2]
			+controllerName: "userlist"
			+count: 3
			+edit_style: "modal"
			+entity: "user"
			+filter_operations: Array[2]
			+filter_values: Object
			+item_operations: Array[2]
			items: Array[3]
			local_data: Object
			+pager: Object
			+template: null
			+title: "Пользователи"
			+type: "scroller"
			*/
		}
		return toObj;
	}
	// если описатель сущности
	// fromObj.type = "entity" или fromObj.type нет, т.е. сущность - запись скроллера
	else {
		if(!toObj) toObj = {};
		
		for (var key in fromObj) {
			if(key=="scrollers"){
				//console.log("key='scrollers'");
				if(!toObj.scrollers && fromObj.scrollers) toObj.scrollers = {};
				for (var key in fromObj.scrollers) {
					toObj.scrollers[key] = copyDescriptor(fromObj.scrollers[key]);
				}
				continue;
			}
			else if(key=="local_data") {
				// пропускаем, у каждого объекта свой local_data
				continue;
			}
			
			// если свойство = null
			if(fromObj[key] == null) toObj[key] = null;
			// если свойство - объект или массив
			else if (typeof fromObj[key] == "object") {
				// если массив
				if(fromObj[key].length) {
					if(!toObj[key]) toObj[key] = [];
					var len = fromObj[key].length;
					for(var i = 0; i < len; i++) {
						if(typeof fromObj[key][i] == "object") toObj[key][i] = copyDescriptor(fromObj[key][i]);
						else toObj[key][i] = fromObj[key][i];
					}
				} 
				// если объект
				else toObj[key] = copyDescriptor(fromObj[key]);
			// если свойство - простой тип
			} else {
				toObj[key] = fromObj[key];
			}
		}
		initEntityScrollers(toObj);
	}
	
	return toObj;
}

/* Копирует объекты
* @fromObj - объект, который надо скопировать
* @toObj - объект, в который надо скопировать. Не обязательный
*/
function deepCopy(fromObj, toObj) {
	if(!toObj) toObj = {};
    if (typeof fromObj != "object") {
        return fromObj;
    }
    
    //var copy = fromObj.constructor();
    for (var key in fromObj) {
		//if(key=="items")
			//console.log("key='items'");
		//else if(key=="local_data")
			//continue;
			//console.log("key='local_data'");
		
		// если свойство = null
        if(fromObj[key] == null) toObj[key] = null;
		// если свойство - объект или массив
		else if (typeof fromObj[key] == "object") {
			// если массив
			if($.isArray(fromObj[key])) {
				toObj[key] = [];
				var len = fromObj[key].length;
				for(var i = 0; i < len; i++) toObj[key][i] = deepCopy(fromObj[key][i]);
			} 
			// если объект
			else toObj[key] = deepCopy(fromObj[key]);
		// если свойство - простой тип
        } else {
            toObj[key] = fromObj[key];
        }
    }
    return toObj;
};

/* Создает уникальный локальный числовой идентификатор
* @descriptor - описатель, для которого необходимо создать уникальный идентификатор
*/
function createID() {
	return Math.random() * 1000000000000000000;
}

/* Создает уникальный локальный идентификатор описателя
* @descriptor - описатель, для которого необходимо создать уникальный идентификатор
*/
function createUDID(descriptor) {
	if(descriptor) {
		descriptor.local_data.container_id = descriptor.type + "_" + descriptor.controllerName + "_" + descriptor.local_data.udid;
		return descriptor.local_data.container_id;
	}
	else return "temporary_" + createID();
}

/* Создает уникальный локальный идентификатор для создаваемой сущности, чтобы можно было ее хранить в массиве entities, в самой сущности идентификатор "-1", чтобы сервер понял, что сущность новая и присвоил новый идентификатор
* 
*/
function createIDForNewEntities() {
	return '_' + createID();
}

/* Возвращает идентификатор корневого контейнера, т.е. контейнера, у которого не задано свойство parent_container_id
* @container_id - идентификатор текущего контейнера
*/
function getRootContainerId(container_id){
	if(containers[container_id].parent_container_id){
		return getRootContainerId(containers[container_id].parent_container_id);
	}
	else return container_id;
}

function saveCustomTemplate(descriptor){
	// если с дескриптором передан кастомизированный шаблон и этот шаблон еще не был сохранен, то сохраняем его
	if(descriptor.template) {
		var tmplName = descriptor.controllerName + "_template";
		// проверяем, есть ли такой шаблон локально
		var tmpl= $.templates("#" + tmplName);
		// если специализированного шаблона нет, то добавляем
		if(!tmpl.tmplName) {
			$("body").append(descriptor.template);
		}
	}
}

/* Проверяет, есть ли специализированный шаблон для отрисовки и берет его. Если нет, то берет стандартный
* @descriptor - описатель, для которого необходимо проверить наличие пециализированного шаблона
*/
function getTemplateName(descriptor) {
	if(descriptor) {
		var tmplName = descriptor.controllerName + "_template";
		var tmpl= $.templates("#" + tmplName);
	
		// если специализированного шаблона нет, то берем стандартный
		if(!tmpl.tmplName) {
			tmplName = "entity_template";
			if(descriptor.controllerName.indexOf("list") > -1) tmplName = "scroller_template";
			//else tmplName = "entity_template";
		}
		return tmplName;
	}
	else console.log("Запрошено создание шаблона для несуществующего дескриптора");
	return null;
}

/* Открывает модалку со скроллером для заполнения поля/грида/скроллера
* @container_id - контейнер, в котором размещено поле/грид/скроллер
* @controllerName - контроллер скроллера, если заполнение делается для грида/скроллера
* @field_id - идентификатор поля,если заполнение делается для поля
* @select_style - стиль выбора в модалке ("radio", "checkbox")
*/
function link_entity(container_id, controllerName, field_id, select_style) {
	var rq = {
		page: 1,
		sort: 'id',
		order: 'DESC',
		page_size: 10
	};
	// берем контейнер, в котором лежит ссылочное поле
	var container = containers[container_id];
	var url = '/'+controllerName+'/filter';
	
	/*if(container.data.columns && .active)*/ rq.filter_active = 1;
	
	if(container.data.type == 'scroller'){
		// добавляем ID объектов, которые надо исключить из выборки
		var ids = '';
		var len = container.data.items.length;
		for(var i = 0; i<len; i++) {
			if(i==0) ids += container.data.items[i].fields.id.value;
			else ids += ','+container.data.items[i].fields.id.value;
		}
		if(container.data.local_data.added_items){
			len = container.data.local_data.added_items.length;
			for(var i = 0; i<len; i++) {
				if(i==0 && ids.length==0) ids += container.data.local_data.added_items[i].fields.id.value;
				else ids += ','+container.data.local_data.added_items[i].fields.id.value;
			}
		}
		if(ids.length > 0) rq.exclude_ids = ids;
	}
	
	// готовим структуру  с данными для связи скроллера на диалоге и вызывющего элемента управления
	container.data.local_data.selectTarget = {
		container_id: container.data.local_data.container_id, // контейнер, из которого открывается диалог со скроллером
		field_id: field_id, // идентификатор поля в контейнере, для которого открывается диалог со скроллером
		//scroller_id: controllerName, // идентификатор скроллера в контейнере, для которого открывается диалог со скроллером
		select_style: select_style
	} // данные попадают в результат вызова ajax через bind
	
	console.log(url);
	$.ajax({
		url: url,
		dataType: 'json',
		method: 'post',
		data: rq,
		beforeSend: function() {
			// показываем прогрессбар
		},
		complete: function() {
			// скрываем прогрессбар
		},			
		success: (function(json) {
			// используется замкание и bind, в свойстве this хранится container (в котором лежит ссылочное поле), как контекст
			console.log(json);
			if(!handleAjaxError(json.error)) {
				//var descriptor = json;
				var descriptor = copyDescriptor(json);
				/*
				// инициализируем локальные данные
				descriptor.local_data = {
					udid: createID(),
					select_style: this.data.local_data.selectTarget.select_style
				}
				createUDID(descriptor);
				
				// сохраняем сущности скроллера, при наличии записей в скроллере
				if(descriptor.items) {
					var descriptor_items_length = descriptor.items.length;
					for (var i = 0; i<descriptor_items_length; i++) {
						descriptor.items[i] = copyServerEntityDataToEntities(descriptor.items[i], descriptor.entity);
					}
				}*/
				
				initScroller(descriptor);
				descriptor.local_data.select_style = this.data.local_data.selectTarget.select_style;
				
				// для подобных вещей редактирование и добавление надо делать в виде модалки, чтобы не потерять изменения на текущей странице
				descriptor.edit_style = "modal";
				//descriptor.add_style = "entity";
				
				// сохраняем в виде локальных сущностей записи скроллера
				saveScrollerItems(descriptor);
							
				// рисуем модалку
				var modal_container_id = renderModal(descriptor, this);
				
				// показываем модалку
				showModal(modal_container_id);
			}
		}).bind(container),
		error: function(xhr, ajaxOptions, thrownError) {
			//console.log(thrownError + "\r\n" + (xhr != null ? xhr.statusText + "\r\n" + xhr.responseText + "\r\n" : ""));
			console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			//console.log(thrownError);
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

/* Возвращает ссылку на объект из entities по наименованию сущности и ее id
* @entityName - наименование сущности
* @eid - id сущности в массиве entities
*/
function getEntity(entityName, eid) {
	return entities[entityName][eid];
}

/* Возвращает ссылку на скроллер по наименованию сущности и ее id и названию скроллера
* @entityName - наименование сущности
* @eid - id сущности в массиве entities
*/
function getScroller(entityName, eid, scrollerName) {
	return entities[entityName][eid][scrollers][scrollerName];
}

/* Добавляет item  в скроллер
* @item - описатель добавляемой записи (из entities)
* @tScroller - скроллер, в который добавляется запись
* @opts - параметры
*   @opts.confirmFromServer - вставляется запись, полученная с сервера
*/
function addItemToScroller(item, tScroller, opts) {
	if(!opts) opts = {confirmFromServer:null};
	var entity_id = item.local_data.eid
	var isFound = false;
	var len;
	var i;
	// если сущность числится в удаленных, то переносим ее в добавленные
	if(tScroller.local_data.deleted_items) {
		len = tScroller.local_data.deleted_items.length;
		for(i = 0; i<len; i++) {
			if(tScroller.local_data.deleted_items[i].local_data.eid == entity_id) {
				tScroller.local_data.deleted_items.splice(i,1);
				//tScroller.items.push(entities[sContainer.data.entity][entity_id]);
				tScroller.items.push(item);
				isFound = true;
				break;
			}
		}
	}
	// если сущность числится в отредактированных
	/*if(tScroller.local_data.added_items && !isFound) {
		len = tScroller.local_data.added_items.length;
		for(i = 0; i<len; i++) {
			if(tScroller.local_data.added_items[i].local_data.eid == entity_id) {
				// если актуальная запись полученна с сервера, то переносим ее общий список скроллера
				if(opts.confirmFromServer) {
					tScroller.local_data.added_items.splice(i,1);
					tScroller.local_data.items.push(item);
				}
				// иначе оставляем ее в добавленных
				isFound = true;
				break;
			}
		}
	}*/
	// если сущность числится в добавленных
	if(tScroller.local_data.added_items && !isFound) {
		len = tScroller.local_data.added_items.length;
		for(i = 0; i<len; i++) {
			if(tScroller.local_data.added_items[i].local_data.eid == entity_id) {
				// если актуальная запись полученна с сервера, то переносим ее общий список скроллера
				if(opts.confirmFromServer) {
					tScroller.local_data.added_items.splice(i,1);
					tScroller.items.push(item);
				}
				// иначе оставляем ее в добавленных
				isFound = true;
				break;
			}
		}
	}
	if(tScroller.items && !isFound) {
		len = tScroller.items.length;
		for(i = 0; i<len; i++) {
			if(tScroller.items[i].local_data.eid == entity_id) {
				isFound = true;
				break;
			}
		}
	}

	if(!isFound) {
		if(opts.confirmFromServer) {
			tScroller.items.unshift(item);
		}
		else {
			if(!tScroller.local_data.added_items) tScroller.local_data.added_items = [];
			tScroller.local_data.added_items.push(item);
		}
	}
}

/* Удаляет item  из скроллера
* @item - описатель удаляемой записи (из entities)
* @tScroller - скроллер, из которого удаляется запись
* @opts - параметры
*   @opts.confirmFromServer - удаляется запись, удаленная на сервере
*/
function deleteItemFromScroller(item, tScroller, opts) {
	if(!opts) opts = {confirmFromServer:null};
	var entity_id = item.local_data.eid
	var isFound = false;
	var len;
	var i;
	// если сущность числится в удаленных
	if(tScroller.local_data.deleted_items) {
		len = tScroller.local_data.deleted_items.length;
		for(i = 0; i<len; i++) {
			if(tScroller.local_data.deleted_items[i].local_data.eid == entity_id) {
				// если сущность удалена на сервере, то удаляем и тут
				if(opts.confirmFromServer) tScroller.local_data.deleted_items.splice(i,1);
				isFound = true;
				break;
			}
		}
	}
	
	// если сущность числится в добавленных
	if(tScroller.local_data.added_items && !isFound) {
		len = tScroller.local_data.added_items.length;
		for(i = 0; i<len; i++) {
			if(tScroller.local_data.added_items[i].local_data.eid == entity_id) {
				// не важно, удалена ли сущность на сервере, просто убираем из добавленных
				tScroller.local_data.added_items.splice(i,1);
				isFound = true;
				break;
			}
		}
	}
	// отображаемые записи
	if(tScroller.items && !isFound) {
		len = tScroller.items.length;
		for(i = 0; i<len; i++) {
			if(tScroller.items[i].local_data.eid == entity_id) {
				 tScroller.items.splice(i,1);
				// если сущность удалена локально, то переносим в удаленные
				if(!opts.confirmFromServer) {
					if(!tScroller.local_data.deleted_items) tScroller.local_data.deleted_items = [];
					tScroller.local_data.deleted_items.push(item);
				}
				isFound = true;
				break;
			}
		}
	}
}

function saveCustomTemplate(descriptor){
	// если с дескриптором передан кастомизированный шаблон и этот шаблон еще не был сохранен, то сохраняем его
	if(descriptor.template) {
		var tmplName = descriptor.controllerName + "_template";
		// проверяем, есть ли такой шаблон локально
		var tmpl= $.templates("#" + tmplName);
		// если специализированного шаблона нет, то добавляем
		if(!tmpl.tmplName) {
			$("body").append(descriptor.template);
		}
	}
}

function initEntityScripts(container_id) {
	var container = containers[container_id];
	// инициализируем все поля для загрузки изображений
	var imgs = container.jqobj.find("input[type='file']");
	imgs.each(function(index, element){
		var ctrl = $(this);
		
		//var eid = ctrl.nextAll("#eid").val();
		var entityName = ctrl.nextAll("#entity").val();
		var entityField = ctrl.nextAll("#field").val();
		var minCount = ctrl.nextAll("#min_count").val();
		var maxCount = ctrl.nextAll("#max_count").val();
		var entity = container.data; //entities[entityName][eid];
		if(!minCount) minCount = 0;
		if(!maxCount) maxCount = 1;
		
		var extraData = {
			parent_entity_id: entity.local_data.eid,
			parent_entity_name: entity.entity,
			files:[],
		};
		
		if(entityField && entityField.length>0) {
			extraData.parent_entity_field = entityField;
		}
		else {
			entityField = 'img';
			extraData.parent_entity_field = entityField;
		}
		
		var settings = {
			language: "ru",
			uploadAsync: false,
			uploadUrl: '/file/upload',
			deleteUrl: '/file/delete',
			minFileCount: minCount,
			maxFileCount: maxCount,
			showClose: false,
			showUpload: false,
			overwriteInitial: false,
			initialPreviewAsData: true,
			validateInitialCount: true,
			//allowedFileTypes: ['image', 'html', 'text', 'video', 'audio', 'flash', 'object'],
			allowedFileExtensions: ['jpg', 'gif', 'png'],//, 'txt'],
			//allowedPreviewTypes: ['image', 'html', 'text', 'video', 'audio', 'flash', 'object'],
			dropZoneEnabled: false,
			browseOnZoneClick: true,
			showCaption: false,
			elErrorContainer: '#' + entityName + '_' + entity.local_data.eid + '_' + entityField,
			
			//layoutTemplates: {main2: '{browse}&nbsp;{upload}&nbsp;{cancel}<div class="kv-upload-progress hide"></div><div id="' + entityName + '_' + entity.local_data.eid + '_' + entityField + '"></div>{preview}'},
			layoutTemplates: {main2: '{browse}&nbsp;{upload}&nbsp;{cancel}<div id="' + entityName + '_' + entity.local_data.eid + '_' + entityField + '"></div>{preview}'},
			otherActionButtons: '<button type="button" class="kv-file-delete btn btn-xs btn-default" title="Удалить" {dataKey} hidden="hidden"><i class="glyphicon glyphicon-trash text-danger"></i></button>',
			uploadExtraData: extraData,
			previewSettings: {
				image: {
					width: '200px',
			}},
			fileActionSettings: {showDrag:false},
			showAjaxErrorDetails: false,
		};
		//console.log(settings.elErrorContainer);
		
		if(entity.fields[entityField].files) {
			var files = entity.fields[entityField].files;
			var filesLength = files.length;
			settings.initialPreviewConfig = [];
			settings.initialPreviewCount = filesLength;
			if(filesLength == maxCount) {
				//settings.showUpload = false;
				//settings.showBrowse = false;
			}
			settings.initialPreview = [];
			for(var i = 0; i < filesLength; i++) {
				var file = files[i];
				extraData.file_id = file.id;
				settings.initialPreviewConfig.push({
					caption: file.name, 
					key: file.id, 
					extra: extraData,
				});
				settings.initialPreview.push(file.url);
			}
		}
		
		if(!entity.local_data.fields) entity.local_data.fields = {};
		if(!entity.local_data.fields[entityField]) entity.local_data.fields[entityField] = {};
		entity.local_data.fields[entityField].inputJQ = $(this);
		
		entity.local_data.fields[entityField].inputJQ.fileinput(settings).on('filezoomshow', function(event, params) {
			// устанавливаем контейнер модалок в качестве родительского контейнера модалки
			m = $(params.modal[0]);
			m.detach();
			m.appendTo('#container_modals');
		}).on('filebatchuploadsuccess', function(event, data, previewId, index) {
			var form = data.form, files = data.files, extra = data.extra, response = data.response, reader = data.reader;
			console.log('File batch upload success');
			
			//handleAjaxSuccess(response.success);
			//handleAjaxError(response.error);
			// подаем сигнал, что загрузка завершена
			entity.local_data.fields[entityField].deferredUpload.resolve();
			
			// добавляем данные о файлах в сущность для дальнейшей нормально обработки
			if(data.response.files) {
				var filesLength = data.response.files.length;
				if(!entity.fields[entityField].files) entity.fields[entityField].files = [];
				for(var i = 0; i < filesLength; i++){
					entity.fields[entityField].files.push({
						id: response.files[i].id,
						name: response.files[i].name,
						url: response.files[i].url,
					});
					files[i].db.data.id = response.files[i].id;
					files[i].name = response.files[i].name;
					var footer = files[i].db.parents('.file-thumbnail-footer');
					footer.find('div.file-footer-caption').attr('title', files[i].name);
					footer.find('div.file-footer-caption').text(files[i].name);
					// подменяем кнопки у загруженных файлов
					files[i].rb.after(files[i].db);
					files[i].rb.detach();
					files[i].db.attr('data-key', response.files[i].id);
				}
			}
			//console.log(response);
		}).on('filebatchuploaderror', function(event, data, msg) {
			var form = data.form, files = data.files, extra = data.extra, response = data.response, reader = data.reader;
			console.log('File batch upload error');
			// хоть фалы и не загрузили, но надо подать сигнал, что загрузка завершена
			entity.local_data.fields[entityField].deferredUpload.resolve();
		   // get message
		   //console.log(msg);
		   //handleAjaxSuccess(response.success);
		   //handleAjaxError(response.error);
		}).on('fileloaded', function(event, file, previewId, index, reader) {
			console.log("fileloaded");
			file.key = previewId;
			file.jq = $('#'+previewId);
			file.rb = file.jq.find("button.kv-file-remove");
			//file.rb.attr('data-key', index);
			file.db = file.jq.find("button.kv-file-delete");
			//file.db.attr('data-key', index);
			file.db.detach();
		});
		
		$('.file-preview .file-initial-thumbs button.kv-file-remove').each(function () {
			var rb = $(this);
			var zb = rb.next();
			var db = zb.next();
			db.detach();
			rb.after(db);
			rb.detach();
		});
		
		// добавляем свою кнопку удаления на файлы, агруженные с сервера (файлы помечаются к удалению, но не удаляются)
		$('.file-preview').on('click', 'button.kv-file-delete', function (e) {
			var btn = $(this);
			var fileID = $(this).attr('data-key');
			console.log('My button pressed for key = ' + fileID);
			
			// если при получении с сервера в поле были файлы
			if(entity.fields[entityField].files && entity.fields[entityField].files.length>0) {
				// добавляем массив с пометками, если его нет
				if(!entity.local_data.fields[entityField].deletedFiles) entity.local_data.fields[entityField].deletedFiles = [];
				// помещаем ID файла в массив
				entity.local_data.fields[entityField].deletedFiles.push(fileID);
			}
			
			// убираем изображение из просмотра
			var thumb = btn.parents('.file-preview-frame');
			thumb.fadeOut('slow', function () {
				thumb.remove();
				console.log('Refreshed ' + entity.local_data.fields[entityField].deletedFiles.length);
				if(entity.local_data.fields[entityField].inputJQ.fileinput('getFilesCount')==entity.local_data.fields[entityField].deletedFiles.length) {
					//input.fileinput('refresh');
				}
			});
		});
	});
}

/* Отображает уведомления об ошибках
* @error - поле с ошибками из ответа сервера (json.error) или подготовленный самостоятельно объект
* @container_id - идентификатор контейнера, в котором надо отображать
*/
function handleAjaxError(error, container_id){
	if(error) {
		// контейнер для вывода уведомления
		var container;
		if(container_id) container = containers[container_id];
		
		var len = error.messages.length;
		for(var i = 0; i < len; i++){
			var msg = error.messages[i];
			var title = msg.code ? msg.code + '. ' : '';
			title += msg.title ? msg.title : 'Ошибка'
			$.notify({
				// options
				title: title,
				message: msg.msg
			},{
				// settings
				type: 'danger',// warning, success
				icon: 'glyphicon glyphicon-star',
			});
		}
		return true;
	}
	return false;
}

/* Отображает уведомления об успешных событиях
* @success - поле с сообщениями из ответа сервера (json.success) или подготовленный самостоятельно объект
* @container_id - идентификатор контейнера, в котором надо отображать
*/
function handleAjaxSuccess(success, container_id){
	var container;
	if(container_id) container = containers[container_id];
	
	if(success) {
		var len = success.messages.length;
		var delay = 10000;
		for(var i = 0; i < len; i++, delay+=delay){
			var msg = success.messages[i];
			var title = msg.code ? msg.code + '. ' : '';
			title += msg.title ? msg.title : 'Операция успешна'
			var n = $.notify({
				// options
				title: title,
				message: msg.msg
			},{
				// settings
				type: 'success',// warning, danger
				delay: delay,
			});
			//console.log(n);
		}
		return true;
	}
	return false;
}

function confirmDelete(container, id) {
	var def = $.Deferred();
	
	var selector = "button[name='delete"+id+"']";
	// кнопка удалить относится или к строке в скроллере
	var btn = container.jqobj.find(selector);
	// или к модалке с сущностью
	if(btn.length==0 && container.parent_container_id) btn = containers[container.parent_container_id].jqobj.find(selector);
	// или к форме редактирования на отдельной странице
	if(btn.length==0 && !container.parent_container_id) btn = $(selector);
	if(btn.length==0) console.error("Элемент кнопки не найден для запроса подтверждения");
		
	var options = {
		placement: 'top',
		title: 'Удалить безвозвратно?',
		content: '<button type="button" class="btn" aria-label="Да" name="yes" onclick="">Да </button>&nbsp;<button type="button" class="btn" aria-label="Нет" name="no" onclick="">Нет</button>',
		html: true,
	};
	btn.popover(options);
	btn.popover('show');
	
	var popover = btn.next();
	
	var content = popover.find('div.popover-content');
	content.find("button[name='yes']").on('click', function(indx, element) {
		def.resolve();
	});
	content.find("button").bind('click', function(indx, element) {
		def.reject();
		btn.popover('destroy');
	});
	content.addClass('text-center');
	return def;
}

/* Удаляет сущность
* @container_id - идентифкатор контейнера (скроллера, формы)
* @id - id удаляемой сущности
*/
function entity_delete(container_id, id) {
	// родительский контейнер (скроллер/грид), с элементом которого работаем
	var container = containers[container_id];
	var data = container.data;
	var local_entity = entities[data.entity][id];
	
	// если удаляем из скроллера, для которого предусмотрено добавление через скролер, то удаляем удаляем связь с родительской сущностью (переносим удаленную запись в удаленные),  ареальное удаление произойдет при сохранении сущности
	var isLocal = false;
	if(data.type == 'scroller' && data.add_style == 'scroller') {
		isLocal = true;
		deleteItemFromScroller(local_entity, data);
		// перерисовываем скроллер
		renderScroller(data);
	}
	if(!isLocal) {
		$.ajax({
			url: '/' + local_entity.entity + '/delete?id=' + id,
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
					entities[data.entity][id].local_data.status = 'deleted';
					
					handleAjaxSuccess(json.success)
					//console.log("С сервера получены полные данные сущности скроллера:");
					console.log(json);
					
					// TODO. Уведомить открытые скролеры и сущности, что сущность удалена
					
					if(data.type == 'entity') {
						// закрываем модалку или переходим к скроллеру сущностей
						if(container.parent_container_id) {
							//var parent_container = containers[container.parent_container_id)
							hideModal(container.parent_container_id);
						}
						else if(json.redirectURL){
							document.location = json.redirectURL;
						}
					}
					else if(data.type == 'scroller') {
						deleteItemFromScroller(local_entity, data, {confirmFromServer:1});
						// перерисовываем скроллер
						renderScroller(data);
					}
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
		});
	}
}