(function (app) {
	"use strict";
	var modules = {};
	
	var core = {
		isDEBUG: false,
		modalsContainer: null,

		init: function () {
			/*if(!app.core.modal.modalsContainer) {
				app.core.modal.modalsContainer = document.createElement('div');
				document.body.appendChild(app.core.modal.modalsContainer);
			}*/
		},
		getContainerById: function (id) {
			if(this.containers[id]) return this.containers[id];
			else return null;
		},
		getEntityByCodeId: function (code, id) {
			if(this.entities[code] && this.entities[code][id]) return this.entities[code][id];
			else return null;
		},
		ajaxError: function(xhr, ajaxOptions, thrownError) {
			console.error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			handleAjaxError({
				messages: [{
					title: 'Ошибка обмена данными',
					msg: 'Ошибка обработки запроса на стороне сервера. Обратитесь в службу поддержки',
				}],
			});
		},
		
		containers: {},
		entities: {},
		/* Создает уникальный локальный идентификатор описателя
		@descriptor - описатель, для которого необходимо создать уникальный идентификатор */
		createUDID: function (descriptor) {
			if(descriptor) {
				if(!descriptor.localData) descriptor.localData = {};
				descriptor.localData.containerId = descriptor.type + "_" + descriptor.controllerName + "_" + descriptor.localData.udid;
				return descriptor.localData.containerId;
			}
			else return "temporary_" + app.core.createID();
		},
		/* Создает уникальный локальный числовой идентификатор */
		createID: function () {
			return Math.random() * 1000000000000000000;
		},
		/* Проверяет, есть ли специализированный шаблон для отрисовки и берет его. Если нет, то берет стандартный
		@descriptor - описатель, для которого необходимо проверить наличие пециализированного шаблона */
		getTemplateByName: function (tmplName) {
			return new Promise(function (resolve, reject) {
				if(!$.templates[tmplName]) {
					// получаем шаблон с сервера
					// TODO. Избавиться от jquery
					$.get('/templates/' + tmplName + '.html', function (data, textStatus, jqXHR) {
						//console.log(data);
						$.templates(tmplName, data);
					}).done(function() {
						console.log('app.core.getTemplateByName. Загружен шаблон' + '/templates/' + tmplName + '.html');
						resolve({tmplName: tmplName, tmpl: $.templates[tmplName]});
					}).fail(function() {
						console.error('app.core.getTemplateByName. Шаблон' + '/templates/' + tmplName + '.html не получен с сервера');
						reject();
					});
				}
				else {
					console.log('app.core.getTemplateByName. Используется шаблон ' + tmplName);
					resolve({tmplName: tmplName, tmpl: $.templates[tmplName]});
				}
			});
		},
		/* Проверяет, есть ли специализированный шаблон для отрисовки и берет его. Если нет, то берет стандартный
		@descriptor - описатель, для которого необходимо проверить наличие пециализированного шаблона */
		getTemplate: function (descriptor) {
			if(descriptor === undefined || descriptor === null || !descriptor) descriptor = null;
			
			return new Promise(function (resolve, reject) {
				if(descriptor) {
					var tmplName;
					//TODO. Удалить грязный хак
					delete descriptor.templateName;
					// если предусмотрен специфичный шаблон
					if(descriptor.templateName) {
						// TODO. Удалить этот грязный хак!!!
						 descriptor.templateName += '2.dev';
						// проверка на существование шаблонов в виде блоков скриптов
						var tmpl = $.templates("#" + descriptor.templateName);
						
						if($.templates[descriptor.templateName]) resolve({tmplName: descriptor.templateName, tmpl: $.templates[descriptor.templateName]});
						else if(tmpl.tmplName) resolve({tmplName: '#' + descriptor.templateName, tmpl: $.templates("#" + descriptor.templateName)});
						else {
							// получаем шаблон с сервера
							// TODO. Избавиться от jquery
							$.get('/templates/' + descriptor.templateName + '.html', function (data, textStatus, jqXHR) {
								//console.log(data);
								$.templates(descriptor.templateName, data);
							}).done(function() {
								resolve({tmplName: descriptor.templateName, tmpl: $.templates[descriptor.templateName]});
							}).fail(function() {
								console.error("app.core.getTemplate. Операция get не успешна");
								reject();
							});
						}
					}
					else {
						// если специализированного шаблона нет, то берем стандартный
						if(descriptor.controllerNameLC.indexOf("list") > -1) tmplName = "scroller_template2.dev";
						else tmplName = "entity_template2.dev";
						
						// проверка на существование шаблонов в виде блоков скриптов
						var tmpl = $.templates("#" + tmplName);
						
						// стандартный шаблон еще не был загружен
						if($.templates[tmplName]) resolve({tmplName: tmplName, tmpl: $.templates[tmplName]});
						else if(tmpl.tmplName) resolve({tmplName: '#' + tmplName, tmpl: $.templates("#" + tmplName)});
						else {
							// получаем шаблон с сервера
							// TODO. Избавиться от jquery
							$.get('/templates/' + tmplName + '.html', function (data, textStatus, jqXHR) {
								//console.log(data);
								$.templates(tmplName, data);
							}).done(function() {
								resolve({tmplName: tmplName, tmpl: $.templates[tmplName]});
							}).fail(function() {
								console.error("app.core.getTemplate. Операция get не успешна");
								reject();
							});
						}
					}
				}
				else {
					console.error("app.core.getTemplate. Параметр descriptor не задан");
					reject();
				}
			});
		},
		
		/*copyServerEntityDataToLocal: function (descriptor, entityNameLC) {
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
		},*/
		
		copyDescriptor: function (fromObj, toObj) {
			// если копируется скролер
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
							for(var i = 0; i < len; i++) toObj[key][i] = app.core.deepCopy(fromObj[key][i]);
						} 
						// если объект
						else toObj[key] = app.core.deepCopy(fromObj[key]);
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
			// если копируем сущность
			// fromObj.type = "entity" или fromObj.type нет, т.е. сущность - запись скроллера
			else {
				if(!toObj && key != "fields") toObj = {};
				
				for (var key in fromObj) {
					// если свойство = null
					if(fromObj[key] == null) toObj[key] = null;
					else if(key=="scrollers"){
						//console.log("key='scrollers'");
						if(!toObj.scrollers && fromObj.scrollers) toObj.scrollers = {};
						for (var key2 in fromObj.scrollers) {
							toObj.scrollers[key2] = app.core.copyDescriptor(fromObj.scrollers[key2]);
						}
						continue;
					}
					else if(key=="local_data") {
						// пропускаем, у каждого объекта свой local_data
						continue;
					}
					else if(key=="fields" && toObj.fields) {
						for (var key2 in fromObj.fields) {
							if(toObj.fields[key2]) toObj.fields[key2] = app.core.copyDescriptor(fromObj.fields[key2], toObj.fields[key2]);
							else toObj.fields[key2] = app.core.copyDescriptor(fromObj.fields[key2]);
						}
						//toObj[key] = app.core.copyDescriptor(fromObj[key], toObj[key]);
						//continue;
					}/**/
					
					// если свойство - объект или массив
					else if (typeof fromObj[key] == "object") {
						// если массив
						if(1+fromObj[key].length >= 1) {
							/*if(!toObj[key])*/ toObj[key] = [];
							var len = fromObj[key].length;
							for(var i = 0; i < len; i++) {
								if(typeof fromObj[key][i] == "object") toObj[key][i] = app.core.copyDescriptor(fromObj[key][i]);
								else toObj[key][i] = fromObj[key][i];
							}
						} 
						// если объект
						else toObj[key] = app.core.copyDescriptor(fromObj[key]);
					// если свойство - простой тип
					} else {
						toObj[key] = fromObj[key];
					}
				}
				app.entity.initScrollers(toObj);
			}
			
			return toObj;
		},
		/* Копирует объекты слепо (без учета бизнес-смысла)
		* @fromObj - объект, который надо скопировать
		* @toObj - объект, в который надо скопировать. Не обязательный
		*/
		deepCopy: function (fromObj, toObj) {
			if(!toObj) toObj = {};
			if (typeof fromObj != "object") {
				return fromObj;
			}
			
			//var copy = fromObj.constructor();
			for (var key in fromObj) {
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
		},
		addCSSClassFix: function (element, cssClass) {
			var currentClassStr = '';
			if(element.classList) currentClassStr = element.classList;
			else currentClassStr = element.className;
				
			var CSSClasses = cssClass.split(" ");
			var CSSClassesLength = CSSClasses.length;
			for(var i = 0; i < CSSClassesLength; i++) {
				if(element.classList) element.classList.add(CSSClasses[i]);
				else if(!currentClassStr.indexOf(CSSClasses[i]) !== -1)	element.className += ' ' +	CSSClasses[i];
			}
		},
	};
	
	// внедрение в приложение
	app.core = core;
	app.modules = {};
	
	app.core.init();
	
}(app));


/*recaptcha fields*/
(function (app) {
	app.modules.recaptcha = {
		fields:[], // массив полей recaptcha, ожидающих загрузку библиотеки
		onloadCallback: function() {
			console.log("app. grecaptcha is ready!");
			for (var i=0, fieldsLength = this.fields.length; i<fieldsLength; i++) {
				this.initField(this.fields[i]);
			}
		},
		initField: function(field) {
			field.verifyCallback = function(response) {
				console.log('app. recaptcha response = ' + response);
				field.value = response;
			};
			field.expiredCallback = function(response) {
				console.log('app. recaptcha expired');
				field.value = null;
			};
			
			field.value_id = grecaptcha.render(document.getElementById(field.id), {
				'sitekey': field.value,
				'theme': 'light',
				'callback': field.verifyCallback,
				'expired-callback':field.expiredCallback,
			});
		},
	};
}(app));
