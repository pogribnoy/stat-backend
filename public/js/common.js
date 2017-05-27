var app = {};
var containers = {};
var entities = {};
//var dbg = 0;

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
	
	if (typeof descriptor != 'undefined') {
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
			var renderData = renderEntity(entity);
			renderData.dfd.done(function(data){
				var container_id = renderData.container_id;
				
				// рисуем  скроллеры сущности
				renderEntityScrollers(entity);
				initEntityScripts(container_id);
			});
		}
		else if(descriptor.type == 'scroller') {
			// присваиваем идентификаторы скролеру
			initScroller(descriptor);
			
			// сохраняем в виде локальных сущностей записи скроллера
			saveScrollerItems(descriptor);
			
			// рисуем  скроллер
			var renderData = renderScroller(descriptor, true);
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
	if(typeof Dropzone != 'undefined') {
		Dropzone.autoDiscover = false;
		/*Dropzone.options.field_img2 = {
		paramName: "file", // The name that will be used to transfer the file
		maxFilesize: 2, // MB
		clickable: true,
		accept: function(file, done) {
			if (file.name == "justinbieber.jpg") {
				done("Naha, you don't.");
			}
			else { done(); }
		  }
		};*/
	}
	
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
	
	return {
		dfd: $.when(getTemplate(descriptor)).done(function(data) {
		
			// отрисовываем основные данные сущности
			var html = data.tmpl.render({descriptor:descriptor});
			
			// вставляем отрисованную сущность на место метки
			containers[container_id].jqobj.html(html);
		}),
		container_id: container_id,
	}
}

function renderEntityScrollers(descriptor) {
	// обрабатываем скроллеры сущности, если они есть
	var dfd, key;
	
	for (key in descriptor.scrollers) {
		dfd = renderScroller(descriptor.scrollers[key], false);
		
		dfd.done(function(data){
			containers[data.containerID].parent_container_id = descriptor.local_data.container_id;
		});
	}
}

function renderScroller(descriptor, isRoot) {
	// обновляем описатель скроллера
	var container_id = descriptor.local_data.container_id,
		deferred = $.Deferred();
	
	/*return {
		dfd: $.when(getTemplate(descriptor)).done(function(data) {
			
			var html = data.tmpl.render({descriptor:descriptor});
			
			// если для скроллера еще не был создан контейнер (первая отрисовка)
			if(!containers[container_id]) {
				// создаем контейнер для сущности
				if(isRoot) containers[container_id] = {jqobj: $("#scroller_form_placeholder"), data: descriptor};
				else containers[container_id] = {jqobj: $("#placeholder_" + container_id), data: descriptor};
			} 
			
			containers[container_id].jqobj.html(html); 
			
			initScrollerScripts(container_id);
		}),
		container_id: container_id,
	}*/
	
	$.when(getTemplate(descriptor)).done(function(data) {
			
		var html = data.tmpl.render({descriptor:descriptor});
		
		// если для скроллера еще не был создан контейнер (первая отрисовка)
		if(!containers[container_id]) {
			// создаем контейнер для сущности
			if(isRoot) containers[container_id] = {jqobj: $("#scroller_form_placeholder"), data: descriptor};
			else containers[container_id] = {jqobj: $("#placeholder_" + container_id), data: descriptor};
		} 
		
		containers[container_id].jqobj.html(html); 
		
		initScrollerScripts(container_id);
		deferred.resolve({dfd: deferred, containerID: container_id});
	});
	
	return deferred.promise();
}

function saveScrollerItems(descriptor) {
	if(descriptor.items) {
		var itemsLength = descriptor.items.length;
		for (var i = 0; i<itemsLength; i++) {
			descriptor.items[i] = copyServerEntityDataToEntities(descriptor.items[i], descriptor.entityNameLC);
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
					scroller.items[i] = copyServerEntityDataToEntities(scroller.items[i], scroller.entityNameLC);
					if(scroller.relationType) scroller.items[i].local_data.relationType = scroller.relationType;
				}
			}
		}
	}
	
	return localEntity;
}

/* Разбираем информацию из объекта descriptor
* @descriptor - описатель, полученный с сервера (описатель м.б. полный для полной сущности, или упрощенный - для сущности из скроллера)
* @entityNameLC - наименование сущности, полученной с сервера; название сущности передается, когда определить сущность нельзя из описателя, т.е. при разборе сущности скроллера
*/
/*function copyServerEntityDataToEntities(descriptor, entityNameLC) {
	if(!entityNameLC) entityNameLC = descriptor.entityNameLC;
	
	var eid;
	// если получена пустая сущность
	if(descriptor.fields.id.value == "-1") eid = createIDForNewEntities();
	else eid = descriptor.fields.id.value;
	
	// создаем класс в массиве entities
	if(!entities[entityNameLC]) entities[entityNameLC] = {};
	if(!entities[entityNameLC][eid]) entities[entityNameLC][eid] = {};
	
	var entity;
	
	// если локально не редактировали, то обновляем
	if(!entities[entityNameLC][eid].local_data || (entities[entityNameLC][eid].local_data && entities[entityNameLC][eid].local_data.status != 'edited')) {
		// копируем то, что есть в объекте
		copyDescriptor(descriptor, entities[entityNameLC][eid]);
		
		// переносим local_data, если сущность была сохранена локально или создаем новый local_data, если сущность создается
		if(!entities[entityNameLC][eid].local_data) {
			entities[entityNameLC][eid].type = 'entity';
			entities[entityNameLC][eid].entityNameLC = entityNameLC;
			entities[entityNameLC][eid].controllerName = entityNameLC;
			entities[entityNameLC][eid].local_data = {
				status: 'actual',
				udid: createID(),
				eid:eid
			};
			createUDID(entities[entityNameLC][eid]);
		}
		else entities[entityNameLC][eid].local_data.status = 'actual';
	}
	
	return entities[entityNameLC][eid];
}*/

function copyServerEntityDataToEntities(descriptor, entityNameLC) {
	if(!entityNameLC) entityNameLC = descriptor.entityNameLC;
	
	var eid;
	// если получена пустая сущность
	if(descriptor.fields.id.value == "-1") eid = createIDForNewEntities();
	else eid = descriptor.fields.id.value;
	
	// создаем класс в массиве entities
	if(!entities[entityNameLC]) entities[entityNameLC] = {};
	if(!entities[entityNameLC][eid]) entities[entityNameLC][eid] = {};
	
	// если локально не редактировали, то обновляем
	if(!entities[entityNameLC][eid].local_data || (entities[entityNameLC][eid].local_data && entities[entityNameLC][eid].local_data.status != 'edited')) {
		// копируем то, что есть в объектах
		copyDescriptor(descriptor, entities[entityNameLC][eid]);
		
		// переносим local_data, если сущность была сохранена локально или создаем новый local_data, если сущность создается
		if(!entities[entityNameLC][eid].local_data) {
			entities[entityNameLC][eid].type = 'entity';
			entities[entityNameLC][eid].entityNameLC = entityNameLC;
			entities[entityNameLC][eid].controllerName = descriptor.controllerName;
			entities[entityNameLC][eid].local_data = {
				status: 'actual',
				udid: createID(),
				eid:eid
			};
			createUDID(entities[entityNameLC][eid]);
		}
		else entities[entityNameLC][eid].local_data.status = 'actual';
	}
	
	return entities[entityNameLC][eid];
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
				if(1+fromObj[key].length >= 1) {
					/*if(!toObj[key])*/ toObj[key] = [];
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
/*function getTemplateName(descriptor) {
	if(descriptor) {
		var tmplName = descriptor.entityNameLC + "_template";
		var tmpl= $.templates("#" + tmplName);
	
		// если специализированного шаблона нет, то берем стандартный
		if(!$.templates[tmplName]) {
			if(descriptor.controllerName.indexOf("list") > -1) tmplName = "1scroller_template";
			else tmplName = "entity_template";
		}
		return tmplName;
	}
	else console.log("Запрошено создание шаблона для несуществующего дескриптора");
	return null;
}*/

function getTemplateByName(tmplName) {
	var deferred = $.Deferred();
	if(!$.templates[tmplName]) {
			// получаем шаблон с сервера
		$.get('../../templates/' + tmplName + '.html', function (data, textStatus, jqXHR) {
			//console.log(data);
			$.templates(tmplName, data);
		}).done(function() {
			deferred.resolve({tmplName: tmplName, tmpl: $.templates[tmplName]});
		}).fail(function() {
			deferred.reject();
		});
	}
	else deferred.resolve({tmplName: tmplName, tmpl: $.templates[tmplName]});
	
	return deferred.promise();
}

function getTemplate(descriptor) {
	var deferred = $.Deferred();
	if(descriptor) {
		var tmplName;
		// если предусмотрен специфичный шаблон
		if(descriptor.templateName) {
			// проверка на существование шаблонов в виде блоков скриптов
			var tmpl = $.templates("#" + descriptor.templateName);
			
			if($.templates[descriptor.templateName]) deferred.resolve({tmplName: descriptor.templateName, tmpl: $.templates[descriptor.templateName]});
			else if(tmpl.tmplName) deferred.resolve({tmplName: descriptor.templateName, tmpl: $.templates("#" + descriptor.templateName)});
			else {
				// получаем шаблон с сервера
				$.get('../../templates/' + descriptor.templateName + '.html', function (data, textStatus, jqXHR) {
					//console.log(data);
					$.templates(descriptor.templateName, data);
				}).done(function() {
					deferred.resolve({tmplName: descriptor.templateName, tmpl: $.templates[descriptor.templateName]});
				}).fail(function() {
					deferred.reject();
				});
			}
		}
		else {
			// если специализированного шаблона нет, то берем стандартный
			if(descriptor.controllerNameLC.indexOf("list") > -1) tmplName = "scroller_template";
			else tmplName = "entity_template";
			
			// проверка на существование шаблонов в виде блоков скриптов
			var tmpl = $.templates("#" + tmplName);
			
			// стандартный шаблон еще не был загружен
			if($.templates[tmplName]) deferred.resolve({tmplName: tmplName, tmpl: $.templates[tmplName]});
			else if(tmpl.tmplName) deferred.resolve({tmplName: tmplName, tmpl: $.templates("#" + tmplName)});
			else {
				// получаем шаблон с сервера
				$.get('../../templates/' + tmplName + '.html', function (data, textStatus, jqXHR) {
					//console.log(data);
					$.templates(tmplName, data);
				}).done(function() {
					deferred.resolve({tmplName: tmplName, tmpl: $.templates[tmplName]});
				}).fail(function() {
					deferred.reject();
				});
			}
		}
	}
	else deferred.reject();
	
	return deferred.promise();
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
	var url = '/'+controllerName+'/index';
	
	// берем предустановленные фильтры
	rq.filter_active = 1;
	for (var key in container.data.columns) {
		var field = container.data.columns[key];
		if(field.filter_value && field.filter_value != null && field.filter_value !='') {
			rq['filter_' + field.id] = encodeURIComponent(field.filter_value);
		}
	}
	// доп. фильтры, используются при фильтрации скроллеров в сущностях для передачи id сущности
	/*if(container.data.add_filter) {
		rq.add_filter = {};
		for (var key in container.data.add_filter) rq.add_filter[key] = container.data.add_filter[key];
	}*/
	
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
				
				initScroller(descriptor);
				descriptor.local_data.select_style = this.data.local_data.selectTarget.select_style;
				
				// для подобных вещей редактирование и добавление надо делать в виде модалки, чтобы не потерять изменения на текущей странице
				descriptor.edit_style = "modal";
				//descriptor.add_style = "entity";
				
				// сохраняем в виде локальных сущностей записи скроллера
				saveScrollerItems(descriptor);
							
				// рисуем модалку
				var renderData = renderModal(descriptor, this);
				renderData.dfd.done(function(data){
					// показываем модалку
					showModal(renderData.container_id);
				});
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
* @entityNameLC - наименование сущности
* @eid - id сущности в массиве entities
*/
function getEntity(entityNameLC, eid) {
	return entities[entityNameLC][eid];
}

/* Возвращает ссылку на скроллер по наименованию сущности и ее id и названию скроллера
* @entityNameLC - наименование сущности
* @eid - id сущности в массиве entities
*/
function getScroller(entityNameLC, eid, scrollerName) {
	return entities[entityNameLC][eid][scrollers][scrollerName];
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

	//  не нашли нигде
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
	var entity = container.data;
	
	for(var fieldID in entity.fields) {
		var field = entity.fields[fieldID];
		// инициализируем все поля для загрузки изображений
		if(field.type == 'img') {
			//var ctrl = document.getElementById("#field_"+fieldID+"_"+entity.fields.id.value);
			// если форма открыта на редактирование и элемент доступен для редактирования
			if(entity.actionName == 'edit' && field['access'] == 'edit') {
				// создаем контейнер для хранения локальных изменений
				
				if(!entity.local_data.fields) entity.local_data.fields = {};
				if(!entity.local_data.fields[fieldID]) entity.local_data.fields[fieldID] = {};
				//сразу создаем отложенное уведомление о завершении загрузки и помещаем уведомление в сущность
				entity.local_data.fields[fieldID].deferredUpload = $.Deferred();
				entity.local_data.fields[fieldID].deferredDelete = $.Deferred();
				
				// после загрузки всех файлов текущего поля сущности запускаем отложенное удаление файлов этого поля
				entity.local_data.fields[fieldID].deferredUpload.done(function(){
					deleteEntityFiles(entity, fieldID);
				});
				
				var opts = {
					action: '/file/upload',
					url: '/file/upload',
					maxFiles: field.max_count ? field.max_count : 1,
					acceptedFiles: "image/*", // ".jpeg,.jpg,.png,.gif",
					thumbnailWidth: 500, // pixels
					thumbnailHeight: 300,
					previewsContainer: "#field_" + fieldID + "_" + entity.fields.id.value + "_preview",
					maxFilesize: 2, // Mb
					//clickable: true,
					clickable: "#field_" + fieldID + "_" + entity.fields.id.value + ", " + "#field_" + fieldID + "_" + entity.fields.id.value + "_addbutton",
					//clickable: "#addBtn123",
					//addRemoveLinks: true,
					autoProcessQueue: false,
					uploadMultiple: true,
					dictDefaultMessage: "Перетащите файлы в данную область или кликните для выбора файлов",
					dictFallbackMessage: "Ваш браузер не поддерживает загрузку файлов методом перетаскивания",
					dictFallbackText: "Пожалуйста, используйте стандартную форму для загрузки файлов, как в старые добрые времена",
					dictInvalidFileType: "Не поддерживаемый тип файла",
					dictFileTooBig: "Файл имеет размер: {{filesize}}. Максимально допустимый размер: {{maxFilesize}}",
					dictRemoveFile: "Удалить",
					dictMaxFilesExceeded: "Выбрано максимальное количество фалов",
					previewTemplate: '<div class="dz-preview dz-file-preview col-lg-3"><p><img data-dz-thumbnail class="img-thumbnail center-block"/></p><p class="text-center"><button data-dz-remove class="btn btn-danger delete btn-xs"><i class="glyphicon glyphicon-trash"></i><span> Удалить</span></button></p><div class="bg-danger"><span data-dz-errormessage></span></div></div>',
				};
				
				
				var myDropzone = new Dropzone("#field_"+fieldID+"_"+entity.fields.id.value, opts);
				
				myDropzone.on("processing", function(file) {
					this.options.params.parent_entity_id = entity.local_data.eid;
					this.options.params.parent_entity_name = entity.entityName;
					this.options.params.parent_entity_field = fieldID;
				});
				myDropzone.on("removedfile", function(file) {
					var serverFlesCount=0;
					var deletedFilesCount=0;
					var dropzoneFilesCount=this.files.length;
					
					console.log('removedfile: id = ' + file.id);
					
					// если при получении с сервера в поле были файлы
					if(entity.fields[fieldID].files && entity.fields[fieldID].files.length>0) {
						// добавляем массив с пометками, если его нет
						if(!entity.local_data.fields[fieldID].deletedFiles) entity.local_data.fields[fieldID].deletedFiles = [];
						// помещаем ID файла в массив для удаления
						// если удаляем серверный файл
						if(file.id) entity.local_data.fields[fieldID].deletedFiles.push(file.id);
						deletedFilesCount = entity.local_data.fields[fieldID].deletedFiles.length;
					}
					
					if(field.files) serverFlesCount = field.files.length;
					
					var finalFilesCount = serverFlesCount - deletedFilesCount + dropzoneFilesCount;
					// убираем кнопку "Добавить", если удален файл и количество оставшихся файлов стало меньше разрешенного
					var clickableElementsLength = this.clickableElements.length;
					//this.options.maxFiles++;
					if(clickableElementsLength>0 && finalFilesCount < this.options.maxFiles) {
						for(var i = 0; i < clickableElementsLength; i++) this.clickableElements[i].style.display = 'flex';
					}
					else {
						for(var i = 0; i < clickableElementsLength; i++) this.clickableElements[0].style.display = 'none';
					}
				});
				myDropzone.on("maxfilesreached", function(file) {
					if(this.clickableElements.length>0) this.clickableElements[0].style.display = 'none';
				});
				myDropzone.on("completemultiple", function(files) {
					var response = null;
					var filesLength = files.length;
					if(filesLength > 0 && files[0].xhr){
						response = JSON.parse(files[0].xhr.response);
						console.log(response);
					
						if(!response.error) {
							// добавляем данные о файлах в сущность для дальнейшей нормально обработки
							if(!entity.fields[fieldID].files) entity.fields[fieldID].files = [];
							for(var i = 0; i < filesLength; i++) {
								var file = files[i];
								entity.fields[fieldID].files.push({
									id: file.id,
									name: file.name,
									url: file.url,
								});
							}
						}
						else console.log(response.error);
					}
					if(this.getQueuedFiles().length==0) entity.local_data.fields[fieldID].deferredUpload.resolve();
				});
				
				//myDropzone.previewsContainer.innerHTML += '<div id="addBtn123" class="dz-preview col-lg-3 bg-success" style="display: flex; align-items: center;"><span data-dz-name class="center-block">Добавить файл</span></div>';
				//myDropzone.previewsContainer.innerHTML += '<div id="addBtn123" class="dz-preview dz-clickable col-lg-3 bg-success" style="display: flex; align-items: center;"><button class="btn btn-success center-block"><i class="glyphicon glyphicon-plus"></i><span></span></button></div>';
				//myDropzone.clickableElements.push(document.getElementById('addBtn123'));
				
				if(entity.fields[fieldID].files) {
					var files = entity.fields[fieldID].files;
					var filesLength = files.length;
					for(var i = 0; i < filesLength; i++) {
						var file = files[i];
						var mockFile = {id: file.id, name: file.name, size: null, url: file.url};
						myDropzone.emit("addedfile", mockFile);
						myDropzone.createThumbnailFromUrl(mockFile, file.url);
						myDropzone.emit("complete", mockFile);
					}
					/*myDropzone.options.maxFiles = myDropzone.options.maxFiles - filesLength;
					if(myDropzone.clickableElements.length>0 && myDropzone.options.maxFiles <= 0) {
						myDropzone.clickableElements[0].style.display = 'none';
						myDropzone.options.maxFiles = 0;
					}*/
					if(myDropzone.clickableElements.length>0 && filesLength >= myDropzone.options.maxFiles) {
						myDropzone.clickableElements[0].style.display = 'none';
						//myDropzone.options.maxFiles = 0;
					}
					entity.local_data.fields[fieldID].deletedFiles = [];
				}
				
				if(!entity.local_data.fields) entity.local_data.fields = {};
				if(!entity.local_data.fields[fieldID]) entity.local_data.fields[fieldID] = {};
				entity.local_data.fields[fieldID].uploader = myDropzone;
			}
		}
		
		// для полей с суммой разрешаем ввод только цифр и "."
		else if(field.type == 'amount') {
			var jqField = container.jqobj.find('#field_'+fieldID+'_value');
			jqField.keypress(function(e) {
				var val = $(this).val();
				var pos = val.indexOf('.');
				
				if (e.which !== 0 && ((e.which < 48 && e.which !== 46) || e.which > 57)) {
					//alert("Charcter was typed. It was: " + String.fromCharCode(e.which) + " code: "+ e.which);
					e.preventDefault();
				}
				// если точка уже есть в значении, то еще одну не надо давать вводить
				else if(e.which === 46) {
					if(val.indexOf('.') > -1) e.preventDefault();
				}
				if(pos != -1 && (val.length-pos)>2){ // проверяем, сколько знаков после запятой, если больше 1го то
					val = val.slice(0, -1); // удаляем лишнее
				}
			}).blur(function() {
				var val = $(this).val();
				var pos = val.indexOf('.');
				if(pos != -1 && (val.length-pos)>3){ // проверяем, сколько знаков после запятой, если больше 1го то
					val = val.slice(0, -(val.length-pos-3)); // удаляем лишнее
				}
				//str = str.replace(/\s/g,'');
				val = val.replace(/\s/g,'');
				$(this).val(val);
				//if(/^\d{1,15}.?\d{0,2}$/.test(val)==null) {};
			});
		}
		
		// для полей recaptcha
		else if(field.type == 'recaptcha') {
			var verifyCallback = function(response) {
				console.log('recaptcha response = ' + response);
				field.value = response;
			};
			var expiredCallback = function(response) {
				console.log('recaptcha expired');
				//grecaptcha.reset(field.value_id);
				field.value = null;
			};
			var widgetId1;
			
			// Renders the HTML element with id 'example1' as a reCAPTCHA widget.
			// The id of the reCAPTCHA widget is assigned to 'widgetId1'.
			field.value_id = grecaptcha.render(document.getElementById(field.id), {
				'sitekey' : field.value,
				'theme' : 'light',
				//'type'
				'callback' : verifyCallback,
				'expired-callback' : expiredCallback,
			});
			//grecaptcha.reset(widgetId1);
			
		}
	}
}
function onloadCallback() {
	console.log("grecaptcha is ready!");
	//dbg = 1;
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
		content: '<button type="button" class="btn btn-danger" aria-label="Да" name="yes" onclick="">Да </button>&nbsp;<button type="button" class="btn btn-success" aria-label="Нет" name="no" onclick="">Нет</button>',
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

function checkPasswordStrength(ctrl, fieldID) {
	var input = $(ctrl);
	var result = input.parent().parent().find("#pass_strength_result").hide();
	var val = ctrl.value;
    var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
    var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
	var enoughRegex = new RegExp("(?=.{6,}).*", "g");
	var resultClass = '';//' label-default';
	if(val && val.length > 0 && val.length < 3) {
		result.html('слишком короткий пароль');
		resultClass = ' label-danger';
	}
	else if (!val || val.length == 0) {
		result.html('');
	}
	else if (false == enoughRegex.test(val)) {
		result.html('слабый пароль');
		resultClass = ' label-default';
	} else if (strongRegex.test(val)) {
		result.html('хороший пароль');
		resultClass = ' label-success';
	} else if (mediumRegex.test(val)) {
		result.html('средний пароль');
		resultClass = ' label-info';
	} else {
		result.html('очень слабый пароль');
		resultClass = ' label-warning';
	}
	if (val && val.length > 0){
		result.removeClass();
		result.addClass('label' + resultClass);
		result.show();
	}
	checkPasswordEq(ctrl, fieldID);
}

function togglePasswordMask(ctrl, fieldID) {
	var toggleEl = $(ctrl);
	var inputEl = toggleEl.parent().parent().find("#" + fieldID);
	var inputType = 'password';
	if(inputEl.prop("type")=="password") inputType = 'text';
	
	toggleEl.find('.glyphicon').toggleClass('glyphicon-eye-open glyphicon-eye-close');
	inputEl.prop("type", inputType);
}

function checkPasswordEq(ctrl, fieldID) {
	var container = $(ctrl).parent().parent().parent();
	var input1 = container.find("#" + fieldID);
	var input2 = container.find("#password2");
	var val1 = input1.val();
	var val2 = input2.val();
	var result = container.find("#pass_eq_result");
	var input2Cont = input2.parent();
	var input2Addon = input2Cont.find('.input-group-addon');
	result.removeClass();
	result.hide();
	if(val1 == '' && val2 == '') {
		input2Addon.html('<span class="glyphicon glyphicon-asterisk"></span>');
		input2Cont.removeClass('has-error');
		input2Cont.removeClass('has-success');
		result.html('');
		return true;
	}
	else if(val1 === val2) {
		input2Addon.html('<span class="glyphicon glyphicon-ok"></span>');
		input2Cont.removeClass('has-error');
		input2Cont.addClass('has-success');
		result.html('пароли совпадают');
		result.addClass('label label-success');
		result.show();
		return true;
	}
	else {
		input2Addon.html('<span class="glyphicon glyphicon-remove"></span>');
		input2Cont.removeClass('has-success');
		input2Cont.addClass('has-error');
		result.html('пароли не совпадают');
		result.addClass('label label-danger');
		result.show();
		return false;
	}
}

function t(text) {
	t = {
		text_no_data: 'Нет данных для отображения',
		text_page_sizes: 'Показывать по',
	};
	if(t[text]) return t[text];
	else return text;
}