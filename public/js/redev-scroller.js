(function (app) {
	"use strict";
	if(!app) {
		app = {core:{}};
	}
	else if(!app.core) {
		app.core = {};
	}
	
	app.core.scroller = {
		/* Показывает запись на чтение, данные беруться из самого скроллера, если сущность была уже загружена.
		Если сущность была загружена ранее, то ее данные должны быть актуальны, т.к. скроллер подписан на обновление сущности.
		Если сущность не была загружена ранее, то данные берутся с сервера */
		showEntity: function (containerId, id) {
			if(containerId === undefined || !containerId) containerId = null;
			if(id === undefined) id = null;
			
			// родительский контейнер (скроллер), элемент которого открываем
			if(!containerId) {
				console.error("app.core.scroller.getEntity. Параметр containerId не задан");
			}
			else {
				var container = app.core.containers[containerId];
				if(!container || !container.data) {
					console.error("app.core.scroller.getEntity. Переменная container.data не задана");
				}
				else {
					var scroller = container.data;
					// TODO. show надо вынести в параметры функции, чтобы можно было также использовать edit
					var url = '/'+scroller.entityNameLC + '/show' + (id ? '?id=' + id : '');
					if(scroller.edit_style=="url") {
						//console.log("row_edit_entity=" + url);
						document.location = url;
					}
					else if(scroller.edit_style=="modal") {
						// получаем сущность с сервера и сохраняем в записях скроллера
						var entity = app.core.scroller.getEntity(scroller, id)							
						.then(function (entity) {
							if(scroller.relationType) entity.localData.relationType = scroller.relationType;
							// TODO. Надо помещать сущность в общий массив app.entities и подписываться на события по их обновлению. И при каждом сохранении на сервер информации обновлять ее в скроллерах, если там сущность не отредактирована
							return Promise.resolve(entity);
						})
						.then(function (entity) {
							return app.core.scroller.renderEntity(scroller, id);
						})
						.then(function (entity) {
							// показываем модалку							
							app.core.modal.show(entity);
						})
						.catch(function (error) {
							console.error("app.core.scroller.getEntity. Операция не успешна");
							if(error) console.error(error);
						});
					}
					else {
						console.error("app.core.scroller.getEntity. Запрошены данные сущности с неизвестным scroller.edit_style"); 
					}
				}
			}
		},
		/* Возавращает данные сущности из скроллера или с сервера.
		@scroller: объект скроллера 
		@id: id объекта сущности из скроллера*/
		getEntity: function (scroller, id) {
			if(id === undefined || !id) id = null;
			if(scroller === undefined || !scroller) scroller = null;
			
			var result;
			
			if(id && scroller) {
				//for(var i = 0, itemsLength = scroller.items.length; i < itemsLength; i++) { }
				// из локальных берем только отредактированные сущности
				if(scroller.items && scroller.items[id] && scroller.items[id].entity && scroller.items[id].entity.localData && scroller.items[id].entity.localData.state != 'edited') result = Promise.resolve(scroller.items[id]);
				else {
					// запрашиваем данные сущности с сервера
					result = new Promise(function (resolve, reject) {
						$.ajax({
							url: '/'+code + '/show' + (id ? '?id=' + id : ''),
							dataType: 'json',
							method: 'get',
							beforeSend: function() {
								// показываем прогрессбар
							},
							complete: function() {
								// скрываем прогрессбар
							},			
							success: function(json) {
								// TODO. Надо заменить функцию на библиотечную в core
								if(!handleAjaxError(json.error)) {
									//console.log("С сервера получены полные данные сущности скроллера:");
									console.log(json);
									
									// сохраняем полученные данные
									var localEntity = app.core.scroller.saveFullServerEntity(scroller, json);
									
									resolve(localEntity);
								}
							},
							error: function(xhr, ajaxOptions, thrownError) {
								app.core.ajaxError(xhr, ajaxOptions, thrownError);
								reject();
							}
						});
					});
				}
			}
			else {
				result = Promise.reject();
			}
			return result;
		},
		renderEntity: function (scroller, id) {
			
		},
		/*Сохраняет в скроллер сущность, полученную с сервера, и поднимает событие "gotdata".
		@scroller: объект скроллера 
		@entity: объект сущности из скроллера*/
		saveFullServerEntity: function (scroller, entity) {
			if(scroller === undefined || scroller === null || !scroller) scroller = null;
			if(entity === undefined || entity === null || !entity) entity = null;
			
			var localEntity = entity;
			if(scroller && entity && entity.fields && entity.fields.id && entity.fields.id.value) {
				var id = entity.fields.id.value;
				if(scroller.items && scroller.items[id]) {
					var item = scroller.items[id];
					if(!item.entity || !item.entity.localData || item.entity.localData.state == 'actual') {
						// TODO. Сделать реальное копирование item.entity = entity
						item.entity = entity;
						localEntity = item.entity;
					}
				}
				else {
					if(!scroller.items) scroller.items = {};
					// TODO. Сделать реальное копирование items[id].entity = entity 
					scroller.items[id] = { 
						fields: entity.fields,
						entity: entity,
					};
					localEntity = scroller.items[id].entity;
				}
			}
			return localEntity;
		},
		/*Получает скроллер с сервера.
		@scrollerId: идентификатор скроллера */
		getData: function (scrollerId) {
			if(scrollerId === undefined || !scrollerId) scrollerId = null;
			
			if(!scrollerId) {
				console.error("app.core.scroller.getData. Параметр scrollerId не задан");
				return Promise.reject();
			}
			
			return new Promise(function (resolve, reject) {
				$.ajax({
					url: '/'+scrollerId,
					dataType: 'json',
					method: 'get',
					beforeSend: function() {
						// показываем прогрессбар
					},
					complete: function() {
						// скрываем прогрессбар
					},			
					success: function(json) {
						// TODO. Надо заменить функцию на библиотечную в core
						if(!handleAjaxError(json.error)) {
							
							// сохраняем полученные данные
							var localScroller = app.core.scroller.saveFromServer(json);
							
							if(localScroller != null) {
								console.log('app.core.scroller.getData. С сервера получены данные скроллера "' + (localScroller.title ? localScroller.title : '') + '":');
								console.log(json);
								console.log('app.core.scroller.getData. Данные скроллера сохранены локально:');
								console.log(localScroller);
								resolve(localScroller);
							}
							else {
								console.error("app.core.scroller.getData. Переменная localScroller не задана");
								reject();
							}
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						console.error("app.core.scroller.getData. AJAX error");
						app.core.ajaxError(xhr, ajaxOptions, thrownError);
						reject();
					}
				});
			});
		},
		/*Сохраняет локально скроллер, полученный с сервера.
		@scroller: описатель скроллера */
		saveFromServer: function (scroller) {
			if(scroller === undefined || scroller === null || !scroller) scroller = null;
			
			if(!scroller) return null;
			
			var localScroller = scroller;
			localScroller = app.core.scroller.copyDescriptor(scroller);
			// TODO. Сделать реальное сохранение
			// присваиваем идентификаторы скролеру
			app.core.scroller.init(localScroller);
			
			// сохраняем в виде локальных сущностей записи скроллера
			app.core.scroller.saveScrollerItems(localScroller);
			
			// TODO. Надо подписывать скроллер на обновление его сущностей
			
			return localScroller;
		},
		
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
			else {
				console.error("app.core.scroller.copyDescriptor. С сервера получен неизвестный тип дескриптора");
				return null;
			}
		},
		
		/* Рисует скроллер
		@scrollerId: идентификатор скроллера */
		render: function (scroller) {
			if(scroller === undefined || scroller === null || !scroller) scroller = null;
			
			return new Promise(function (resolve, reject) {
				if(scroller) {
					app.core.getTemplate(scroller)
					// отрисовываем модалку по шаблону
					.then(function (data) {
						var html = data.tmpl.render( { descriptor: scroller } );
						var containerId = scroller.localData.containerId;
						
						var newDOMEl = document.createElement('div');
						newDOMEl.innerHTML = html;
						
						// создаем контейнер для отрисовки
						app.core.containers[containerId] =  {
							data: scroller, 
							html: html, 
							jqobj: $(newDOMEl).find("#"+containerId), 
							DOMEl: newDOMEl.querySelector("#"+containerId), 
							containerId: containerId,
						};
						
						app.core.containers[containerId].DOMEl.removeAttribute('aria-hidden');
						
						resolve(containerId);
					})
					.catch(function (error) {
						console.error("app.core.scroller.render. Операция не успешна");
						if(error) console.error(error);
						reject();
					});
				}
				else {
					console.error("app.core.scroller.render. Параметр scroller не задан");
					reject();
				}
			});
		},
		/* Показывает скроллер в модальном окне
		@scrollerId: идентификатор скроллера
		@options: параметры (selectStyle - скроллер показывается для выбора значений)*/
		showModal: function (scrollerId, options) {
			if(scrollerId === undefined || scrollerId === null || !scrollerId) scrollerId = null;
			var defaultSelectStyle = '0..1'; //'1..1', '0..n', '1..n', null
			if(!options || options === undefined || options === null) options = {				
				selectStyle: defaultSelectStyle,
			}
			if(!options.selectStyle || options.selectStyle === undefined || options.selectStyle === null) options.selectStyle = defaultSelectStyle;
			
			return new Promise(function (resolve, reject) {
				if(scrollerId) {
					var localScroller = null;
					app.core.scroller.getData(scrollerId)
					// отрисовываем скроллер по шаблону
					.then(function (scroller) {
						localScroller = scroller;
						return app.core.scroller.render(localScroller);
					}).catch(function (error) {
						console.error("app.core.scroller.render. Операция не успешна");
						if(error) console.error(error);
					})
					// отрисовываем контейнер-модалку
					.then(function (containerId) {
						var modalOptions = {
							isWide: 1,
							title: app.core.containers[containerId].data.title,
							descriptor: app.core.containers[containerId].data,
						}
						
						// отрисовываем модалку и цепляем к DOM
						return app.core.modal.render(app.core.containers[containerId].html, modalOptions);
					}).catch(function (error) {
						console.error("app.core.modal.render. Операция не успешна");
						if(error) console.error(error);
					})
					// показываем отрисованные данные
					.then(function (containerId) {
						app.core.containers[localScroller.localData.containerId].parent_container_id = containerId;
						app.core.scroller.initScrollerScripts(localScroller.localData.containerId);
						app.core.modal.show(containerId);
					});
				}
			});
		},
		
		/* Инициализирует скроллер (инициализирует local_data, служебный скроллер, сохраняет кастомизированный шаблон)
		@scroller - описатель скроллера */
		init: function (scroller) {
			if(!scroller.localData) {
				scroller.localData = {
					udid: app.core.createID()
				}
				app.core.createUDID(scroller);
			}
			// охраняем кастомизированный шаблон, если он передан
			// TODO. Переделать на app.core
			//app.core.getTemplate(scroller);
		},
		/* Инициализирует скрипты скроллера
		* @containerId - идентификатор контейнера скроллера */
		initScrollerScripts: function (containerId) {
			var container = app.core.containers[containerId];
			// если у скроллера есть общий чекбокс для всех, то настраиваем его переключение
			
			var toggleAllEls = container.DOMEl.querySelectorAll("input.toggle-all");
			if(toggleAllEls.length == 1) {
				var toggleAllEl = toggleAllEls[0];
				toggleAllEl.onchange = function() {
					//alert(app.core.containers[containerId].jqobj.find("tbody tr input[name^='row_scroller']").length);
					
					var targetToggleAllEls = container.DOMEl.querySelectorAll("input[name^='row_scroller']");
					var targetToggleAllElsLength = targetToggleAllEls.length;
					for (var i = 0; i < targetToggleAllElsLength; i++) {
						targetToggleAllEls[i].checked = toggleAllEl.checked;
						targetToggleAllEls[i].onchange = function() {
							var targetToggleAllEls = container.DOMEl.querySelectorAll("input[name^='row_scroller']");
							var targetToggleAllElsLength = targetToggleAllEls.length;
							var allChecked = true;
							var allUnChecked = true;
							for (var i = 0; i < targetToggleAllElsLength; i++) {
								if(!targetToggleAllEls[i].checked && allChecked) {
									allChecked = false;
								}
								if(targetToggleAllEls[i].checked && allUnChecked) {
									allUnChecked = false;
								}
								if(!allChecked && !allUnChecked) break;
							}
							if(allChecked) toggleAllEl.checked = true;
							else toggleAllEl.checked = false;
						}
					}
				};
			}
			
			// для полей input строки фильтра добавляем применение фильтрации по Enter
			container.jqobj.find("input[name^='filter_']:not(:has(.inited))").each(function(indx, element) {
				$(this).focusin(function(){
					$(this).bind('keypress', function(e) {
						if(e.keyCode==13){
							apply_filter(containerId);
						}
					});
				}).focusout(function(){
					$(this).unbind('keypress', false);
				}).addClass("inited");
			});
			
			// если есть строки в добавленных, которые добавлены с сохранением на сервер, то анимируем изменение цвета статуса success
			container.jqobj.find("tr.status-actual").each(function(indx, element) {
				var obj = $(this);
				setTimeout(function(){
					obj.removeClass('status-actual success');
				}, 2000);  
			});
			
			// улучшаем выпадающие списки
			if ($.fn.select2) {
				$.fn.select2.defaults.set( "theme", "bootstrap");
				$("select.extended").select2();
			}
			
			// для полей select делаем автоприменение фильтра после выбора значения
			var filerSelectEls = container.DOMEl.querySelectorAll("select[name^='filter_']");
			var filerSelectElsLength = filerSelectEls.length;
			for (var i = 0; i < filerSelectElsLength; i++) {
				filerSelectEls[i].onchange = function(e) {
					app.core.scroller.applyFilter(container);
				};
			}
			
		},
		applyFilter: function (container) {
			var scroller = container.data;
			var udid = scroller.UDID;
			var jqobj = container.jqobj;
			var DOMEl = container.DOMEl;
			var rq = {};
			var controllerName = scroller.controllerName;
			
			var url = '/'+controllerName+'/index';
			
			if(container.parent_container_id) {
				var parentData = containers[container.parent_container_id].data;
				if(parentData.type === 'entity') {
					rq.scrollerName = controllerName;
					rq.actionName = parentData.actionName;
					controllerName = parentData.controllerName;
					url = '/'+controllerName+'/filter';
				}
			}
			
			// собираем служебные поля фильтра скроллера
			var page = DOMEl.getElementById('page').value;
			rq.page = encodeURIComponent(page ? page : 1);
			
			var sort = DOMEl.getElementById('sort').value;
			if (sort) rq.sort = encodeURIComponent(sort);
			
			var order = DOMEl.getElementById('order').value;
			if (order) rq.order = encodeURIComponent(order);
			
			var page_size = DOMEl.getElementById('page_size').value;
			if (page_size) rq.page_size = encodeURIComponent(page_size);
			
			var tr_offs = (data.group_operations && data.group_operations.length > 0) || (data.common_operations && data.common_operations.length > 0) ? 2 : 1;
			var columnEls = DOMEl.querySelectorAll("table tr:eq("+tr_offs+") td");
			var columnElsLength = columnEls.length;
			for(var i=0; i<columnElsLength; i++) {
				// перебираем инпуты
				var tmpEls = columnEls[i].querySelectorAll("input[name^='filter_']");
				var tmpElsLength = tmpEls.length;
				for(var j=0; j<tmpElsLength; j++) {
					var fieldEls = tmpEls[i].find("[type='text'], [type='number'], [type='email']");
					var fieldElsLength = fieldEls.length;
					for(var k=0; k<fieldElsLength; k++) {
						if(fieldEls[k].value) {
							rq[fieldEls[k].name] = encodeURIComponent(fieldEls[k].value);
							//console.log('filter = ' + rq[field.attr("name")]);
						}
					}
				}
				// TODO: checkbox и пр.
				for(var j=0; j<tmpElsLength; j++) {
					var fieldEls = tmpEls[i].find("[type='checkbox']");
					var fieldElsLength = fieldEls.length;
					for(var k=0; k<fieldElsLength; k++) {
						if(fieldEls[k].value) {
							rq[fieldEls[k].name] = encodeURIComponent(fieldEls[k].value);
							//console.log('filter = ' + rq[field.attr("name")]);
						}
					}
				}
				
				// перебираем селекты
				var fieldEls = columnEls[i].querySelectorAll("select[name^='filter_']");
				var fieldElsLength = fieldEls.length;
				for(var j=0; j<tmpElsLength; j++) {
					if(fieldEls[j].value && (fieldEls[j].value == "**" || fieldEls[j].value != "*")) {
						rq[fieldEls[j].name] = encodeURIComponent(fieldEls[j].value);
					}
				}
			}
			
			// доп. фильтры, используются при фильтрации скроллеров в сущностях для передачи id сущности
			if(scroller.add_filter) {
				rq.add_filter = {};
				for (var key in scroller.add_filter) rq.add_filter[key] = scroller.add_filter[key];
			}
			// исключение айдишников
			if(scroller.filter_values.exclude_ids) {
				rq.exclude_ids = scroller.filter_values.exclude_ids;
			}
			
			// TODO. Выяснить, для чего фильтр
			// статичные фильтры
			if (typeof static_filter_values !== "undefined") {
				for (var key in static_filter_values) rq[key] = static_filter_values[key];
			}
			
			console.log(url);
			
			var rqDefferred = $.ajax({
				url: url,
				data: rq,
				dataType: 'json',
				//dataType: 'html',
				method: 'post',
				beforeSend: function() {
					// показываем прогрессбар
					setTimeout(function() {
						if(rqDefferred.state() == 'pending') {
							var blockedJQ = $('#' + container_id + 'CollapseDiv');
							var spinnerJQ = blockedJQ.find('#spinner');
							//if(spinnerJQ.length == 0) blockedJQ.prepend('<div class="container-fluid"><div id="spinner" class="row" style="display: flex; align-items: center;"><div class="col-lg-12 bg-warning"><button class="btn btn-default center-block"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Обработка запроса</button></div></div></div>');
							if(spinnerJQ.length == 0) blockedJQ.prepend('<div id="spinner" class="LockOn"><button class="btn btn-default center-block"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Обработка запроса</button></div>');
							else {
								//spinnerJQ.show();
								spinnerJQ.removeClass('LockOff');
								spinnerJQ.addClass('LockOn');
							}
							//var getHeight = document.getElementById(container_id + 'CollapseDiv').clientHeight;
							//document.getElementById("spinner").style.height = getHeight*2;
							spinnerJQ.height(blockedJQ.height());
						}
					}, 500);
					
				},
				complete: function() {
					// скрываем прогрессбар
					clearTimeout(jqobj.data.timeoutID);
					//$('#spinner').hide();
					var blockedJQ = $('#' + container_id + 'CollapseDiv');
					var spinnerJQ = blockedJQ.find('#spinner');
					spinnerJQ.removeClass('LockOn');
					spinnerJQ.addClass('LockOff');
				},			
				success: (function(json) {
					if(!handleAjaxError(json.error)) {
						// используется замкание и bind, в свойстве this хранится container скроллера, как контекст
						console.log(json);
						
						// сохраняем способ редактирования скроллера в данном контейнере перед копирование
						var add_style = this.data.add_style;
						
						copyDescriptor(json, this.data);
						//delete json;
						
						// восстанавливаем способ редактирования после копирования
						this.data.add_style = add_style;
						
						var isFound = false;
						var len;
						if(this.data.local_data.added_items) {
							len = this.data.local_data.added_items.length;
							for(var i = 0; i<len; i++) {
								var item = this.data.local_data.added_items[i];
								var itemsLen = this.data.items.length;
								for(var j = 0; j<itemsLen; j++) {
									if(item.fields.id.value == this.data.items[j].fields.id.value) {
										this.data.local_data.added_items.items.splice(i,1);
										isFound = true;
										break;
									}
								}
							}
						}
						if(this.data.local_data.deleted_items && !isFound) {
							len = data.items.length;
							for(var i = 0; i<len; i++) {
								var item = this.data.local_data.deleted_items[i];
								var itemsLen = this.data.items.length;
								for(var j = 0; j<itemsLen; j++) {
									if(item.fields.id.value == this.data.items[j].fields.id.value) {
										this.data.items.splice(i,1);
										break;
									}
								}
							}
						}
						
						// сохраняем в виде локальных сущностей записи скроллера
						saveScrollerItems(this.data);
						
						renderScroller(this.data, false);
					}
				}).bind(container),
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
		},
		saveScrollerItems: function (scroller) {
			if(scroller.items) {
				var itemsLength = scroller.items.length;
				for (var i = 0; i<itemsLength; i++) {
					var item = scroller.items[i];
					//scroller.items[i] = app.core.copyServerEntityDataToLocal(scroller.items[i], scroller.entityNameLC);
					//if(scroller.relationType) scroller.items[i].local_data.relationType = scroller.relationType;
					if(!item.localData) {
						item.type = 'entity';
						item.entityNameLC = scroller.entityNameLC;
						item.controllerName = scroller.entityNameLC;
						item.localData = {
							status: 'actual',
							udid: app.core.createID(),
							eid:item.fields.id.value,
						};
						app.core.createUDID(item);
					}
					else item.localData.status = 'actual';
				}
			}
		},
		rowAdd: function (containerId) {
			console.log("app.core.scroller.rowAdd. Добавление новой записи");
		},
		rowShow: function (containerId, id) {
			console.log("app.core.scroller.rowShow. Просмотр записи");
			if(containerId === undefined || containerId === null || !containerId) containerId = null;
			if(id === undefined || id === null || !id) id = null;
			
			if(!containerId || !id) {
				console.error("app.core.scroller.rowEdit. Не заданы один или более параметров: containerId, id");
				return null;
			}
			else if(!app.core.containers[containerId]) {
				console.error("app.core.scroller.rowEdit. Не найден контейнер " + containerId);
				return null;
			}
			
			var container = app.core.containers[containerId];
			var scroller = container.data;
			
			//app.core.entity.show(scroller.entityNameLC, id);
			app.core.entity.showModal(scroller.entityNameLC, id, {action: 'show'});
		},
		rowEdit: function (containerId, id) {
			console.log("app.core.scroller.rowEdit. Редактирование записи");
			if(containerId === undefined || containerId === null || !containerId) containerId = null;
			if(id === undefined || id === null || !id) id = null;
			
			if(!containerId || !id) {
				console.error("app.core.scroller.rowEdit. Не заданы один или более параметров: containerId, id");
				return null;
			}
			else if(!app.core.containers[containerId]) {
				console.error("app.core.scroller.rowEdit. Не найден контейнер " + containerId);
				return null;
			}
			
			var container = app.core.containers[containerId];
			var scroller = container.data;
			
			//app.core.entity.show(scroller.entityNameLC, id);
			app.core.entity.showModal(scroller.entityNameLC, id, {action: 'edit'});
		},
		rowSelect: function (containerId, id) {
			console.log("app.core.scroller.rowSelect. Добавление путем выбора");
		},
	};
}(app));
