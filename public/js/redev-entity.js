(function (app) {
	"use strict";
	if(!app) {
		app = {core:{}};
	}
	else if(!app.core) {
		app.core = {};
	}
	
	app.core.entity = {
		render: function (entity, options) {
			if(entity === undefined || entity === null || !entity) entity = null;
			
			return new Promise(function (resolve, reject) {
				if(entity) {
					app.core.getTemplate(entity)
					// отрисовываем сущность по шаблону
					.then(function (data) {
						var html = data.tmpl.render( { descriptor: entity  } );
						var containerId = entity.localData.containerId;
						
						var newDOMEl = document.createElement('div');
						newDOMEl.innerHTML = html;
						
						// создаем контейнер для отрисовки
						app.core.containers[containerId] =  {
							data: entity, 
							html: html, 
							jqobj: $(newDOMEl).find("#"+containerId), 
							DOMEl: newDOMEl.querySelector("#"+containerId), 
							containerId: containerId,
						};
						
						// отрисовываем скроллеры по шаблону
						//var entity = app.core.containers[containerId];
						var scrollerRenderers = [];
						for (var key in entity.scrollers) {
							scrollerRenderers.push(app.core.scroller.render(entity.scrollers[key]));
						}
						if (scrollerRenderers.length>0) {
							return Promise.all(scrollerRenderers)
							// после отрисовки скроллеров их надо поместить в DOM сущности
							.then(function (scrollerContainerIds) {
								console.log(scrollerContainerIds);
								var scrollerContainerIdsLength = scrollerContainerIds.length;
								
								for (var i = 0; i < scrollerContainerIdsLength; i++) {
									var scrollerContainer = app.core.containers[scrollerContainerIds[i]];
									scrollerContainer.parent_container_id = containerId;
									var scrollerContainerDOMEl = app.core.containers[containerId].DOMEl.querySelector("#placeholder_"+scrollerContainerIds[i]);
									scrollerContainerDOMEl.replaceWith(scrollerContainer.DOMEl);
									app.core.containers[containerId].html = app.core.containers[containerId].DOMEl.outerHTML;
									//scrollerContainer.html = scrollerContainer.DOMEl.outerHTML;
									
									//scrollerContainer.html;
								}
								resolve(containerId); 
							});
						}
						else resolve(containerId);
						
						//return new Promise().resolve(containerId);
					}).catch(function (error) {
						console.error("app.core.entity.render. Операция не успешна");
						if(error) console.error(error);
						reject();
					});
				}
				else {
					console.error("app.core.entity.render. Параметр entity не задан");
					reject();
				}
			});
		},
		save: function (containerId) {
			if(containerId) {
				// проверяем поля сущности
				app.core.entity.check(containerId)
				.then(function (descriptor) {
					return app.core.entity.checkedSave(descriptor);
				})
				.catch(function (error) {
					console.error("app.core.entity.save. Произошла ошибка при операции save");
					if(error) console.error(error);
				});
			}
			else {
				console.error("app.core.entity.save. Параметр containerId не задан");
			}
		},
		check: function (containerId) { 
			return new Promise(function (resolve, reject) {
				console.log("app.core.entity.check. Сущность проверена");
				resolve({actioNameLC:'check'});
			});
		},
		checkedSave: function (descriptor) { 
			return new Promise(function (resolve, reject) {
				// TODO. Сделать реальное сохранение на сервер
				descriptor.actioNameLC2 = 'checkedSave';
				console.log("app.core.entity.checkedSave. Сущность сохранена");
				resolve(descriptor);
				//console.error("app.core.entity.check. Произошла ошибка при операции checkedSave");
				//reject(descriptor);
			});
		},
		getData: function (entityNameLC, id, action) {
			if(id === undefined) id = null;
			
			return new Promise(function (resolve, reject) {
				// если сущность открыта в другой модалке или на родительской форме, то сообщаем об этом
				if(app.core.entities[entityNameLC] && entities[entityNameLC][id]) {
					var entity = app.core.entities[entityNameLC][id];
					if(app.core.containers[entity.localData.containerId]) {
						// TODO. такие уведомлялки делать в виде Popup на кнопке, исчезающего через несколько секунд
						alert("Сущность уже открыта на редактирование в другой экранной форме. Завершите работу с диалогами и вернитесь к редактированию сущности");
						reject();
					}
					// если сущность не сохранена на сервере или сущность сохранена на сервере, но она отредактирована / удалена локально
					if(entity.fields.id.value == -1 || (entity.fields.id.value != -1 && entity.localData.status != 'actual')) {
						resolve(entity);
					}
				}
					
				// запрашиваем с сервера более подробную информацию (всю сущность, а не только скроллерную запись)
				// при этом, если выполняется операция add, то будет запрошена информация пустой сущности
				$.ajax({
					url: '/'+entityNameLC + '/' + action + (id ? '?id=' + id : ''),
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
							var localEntity = app.core.entity.saveFromServer(json);
							//var localEntity = json;
							
							app.core.entity.initEntityScrollers(localEntity);
							
							if(localEntity != null) {
								console.log('app.core.entity.getData. С сервера получены данные скроллера "' + (localEntity.title ? localEntity.title : '') + '":');
								console.log(json);
								console.log('app.core.entity.getData. Данные скроллера сохранены локально:');
								console.log(localEntity);
								resolve(localEntity);
							}
							else {
								console.error("app.core.entity.getData. Переменная localEntity не задана");
								reject();
							}
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						console.error("app.core.entity.getData. AJAX error");
						app.core.ajaxError(xhr, ajaxOptions, thrownError);
						reject();
					}
				});
			});
		},
		/* Инициализирует скроллеры сущности
		* @entity - описатель сущности
		*/
		initEntityScrollers: function (entity) {
			// присваиваем идентификаторы скролерам, чтобы на привыводе основных данных сущности оставить метки для их размещения
			for (var key in entity.scrollers) {
				var scroller = entity.scrollers[key];
				app.core.scroller.init(scroller);
				// сохраняем в виде локальных сущностей записи скроллера
				app.core.scroller.saveScrollerItems(scroller);
			}
		},
		
		/* Показывает скроллер в модальном окне
		@entityId: идентификатор скроллера
		@options: параметры (action - show иди edit)*/
		showModal: function (entityName, entityId, options) {
			if(!entityName || entityName === undefined || entityName === null) entityName = null;
			if(!entityId || entityId === undefined || entityId === null) entityId = null;
			if(!options || options === undefined || options === null) options = {
				action: 'show',
			};
			
			return new Promise(function (resolve, reject) {
				if(entityName && entityId) {
					var localEntity = null;
					app.core.entity.getData(entityName, entityId, options.action)
					// отрисовываем по шаблону
					.then(function (entity) {
						localEntity = entity;
						return app.core.entity.render(localEntity);
					}).catch(function (error) {
						console.error("app.core.entity.render. Операция не успешна");
						if(error) console.error(error);
					})
					// отрисовываем контейнер-модалку
					.then(function (containerId) {
						var modalOptions = {
							isWide: 1,
							title: app.core.containers[containerId].data.title,
							descriptor: app.core.containers[containerId].data,
						}
						return app.core.modal.render(app.core.containers[containerId].html, modalOptions);
					}).catch(function (error) {
						console.error("app.core.modal.render. Операция не успешна");
						if(error) console.error(error);
					})
					// показываем отрисованные данные
					.then(function (containerId) {
						app.core.containers[localEntity.localData.containerId].parent_container_id = containerId;
						app.core.entity.initEntityScripts(localEntity.localData.containerId);
						
						app.core.modal.show(containerId);
					});
				}
			});
		},
		/*Сохраняет локально сущность, полученную с сервера.
		@entity: описатель сущности, полученной с сервера */
		saveFromServer: function (entity) {
			if(entity === undefined || entity === null || !entity) entity = null;
			
			if(!entity) return null;
			
			var localEntity = entity;
			// TODO. Сделать реальное сохранение
			localEntity = app.core.entity.copyDescriptor(entity);
			// присваиваем идентификаторы скролеру
			app.core.entity.init(localEntity);
			
			// сохраняем в виде локальных сущностей записи скроллера
			//app.core.entity.saveScrollerItems(localEntity);
			
			// TODO. Надо подписывать скроллер на обновление его сущностей
			
			return localEntity;
		},
		copyDescriptor: function (fromObj, toObj) {
			// если копируем сущность
			// fromObj.type = "entity" или fromObj.type нет, т.е. сущность - запись скроллера
			if(fromObj.type == "entity") {
				if(!toObj && key != "fields") toObj = {};
				
				for (var key in fromObj) {
					// если свойство = null
					if(fromObj[key] == null) toObj[key] = null;
					else if(key=="scrollers"){
						//console.log("key='scrollers'");
						if(!toObj.scrollers && fromObj.scrollers) toObj.scrollers = {};
						for (var key2 in fromObj.scrollers) {
							toObj.scrollers[key2] = app.core.scroller.copyDescriptor(fromObj.scrollers[key2]);
						}
						continue;
					}
					else if(key=="localData") {
						// пропускаем, у каждого объекта свой localData
						continue;
					}
					else if(key=="fields" && toObj.fields) {
						for (var key2 in fromObj.fields) {
							if(toObj.fields[key2]) toObj.fields[key2] = app.core.deepCopy(fromObj.fields[key2], toObj.fields[key2]);
							else toObj.fields[key2] = app.core.entity.copyDescriptor(fromObj.fields[key2]);
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
								if(typeof fromObj[key][i] == "object") toObj[key][i] = app.core.deepCopy(fromObj[key][i]);
								else toObj[key][i] = fromObj[key][i];
							}
						} 
						// если объект
						else toObj[key] = app.core.deepCopy(fromObj[key]);
					// если свойство - простой тип
					} else {
						toObj[key] = fromObj[key];
					}
				}
				app.core.entity.initScrollers(toObj);
				return toObj;
			}
			else {
				console.error("app.core.entity.copyDescriptor. С сервера получен неизвестный тип дескриптора");
				return null;
			}
		},
		/* Инициализирует скроллер (инициализирует localData, служебный скроллер, сохраняет кастомизированный шаблон)
		@entity - описатель сущности */
		init: function (entity) {
			if(!entity.localData) {
				entity.localData = {
					udid: app.core.createID()
				}
				app.core.createUDID(entity);
			}
			// охраняем кастомизированный шаблон, если он передан
			// TODO. Переделать на app.core
			//saveCustomTemplate(scroller);
		},
		/* Инициализирует скрипты сущности
		* @containerId - идентификатор контейнера сущности */
		initEntityScripts: function (containerId) {
			var container = app.core.containers[containerId];
			var entity = container.data;
			
			for(var fieldID in entity.fields) {
				var field = entity.fields[fieldID];
				// инициализируем все поля для загрузки изображений
				if(field.type == 'img') {
					//var ctrl = document.getElementById("#field_"+fieldID+"_"+entity.fields.id.value);
					// если форма открыта на редактирование и элемент доступен для редактирования
					if(entity.actionName == 'edit' && field['access'] == 'edit') {
						// создаем контейнер для хранения локальных изменений
						
						if(!entity.localData.fields) entity.localData.fields = {};
						if(!entity.localData.fields[fieldID]) entity.localData.fields[fieldID] = {};
						//сразу создаем отложенное уведомление о завершении загрузки и помещаем уведомление в сущность
						entity.localData.fields[fieldID].deferredUpload = $.Deferred();
						entity.localData.fields[fieldID].deferredDelete = $.Deferred();
						
						// после загрузки всех файлов текущего поля сущности запускаем отложенное удаление файлов этого поля
						entity.localData.fields[fieldID].deferredUpload.done(function(){
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
							this.options.params.parent_entity_id = entity.localData.eid;
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
								if(!entity.localData.fields[fieldID].deletedFiles) entity.localData.fields[fieldID].deletedFiles = [];
								// помещаем ID файла в массив для удаления
								// если удаляем серверный файл
								if(file.id) entity.localData.fields[fieldID].deletedFiles.push(file.id);
								deletedFilesCount = entity.localData.fields[fieldID].deletedFiles.length;
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
							if(this.getQueuedFiles().length==0) entity.localData.fields[fieldID].deferredUpload.resolve();
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
							entity.localData.fields[fieldID].deletedFiles = [];
						}
						
						if(!entity.localData.fields) entity.localData.fields = {};
						if(!entity.localData.fields[fieldID]) entity.localData.fields[fieldID] = {};
						entity.localData.fields[fieldID].uploader = myDropzone;
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
					/*var verifyCallback2 = function() {
						console.log('recaptcha field = ' + field.id);
					};
					var verifyCallback = function(response) {
						console.log('recaptcha response = ' + response);
						field.value = response;
						verifyCallback2();
					};
					var expiredCallback = function(response) {
						console.log('recaptcha expired');
						//grecaptcha.reset(field.value_id);
						field.value = null;
					};*/
					
					//app.getScript('https://www.google.com/recaptcha/api.js?onload=app.onloadCallback&render=explicit', true);
					
					
					// Renders the HTML element with id 'example1' as a reCAPTCHA widget.
					if(grecaptcha) {
						app.recaptcha.initField(field);
						/*field.value_id = grecaptcha.render(document.getElementById(field.id), {
							'sitekey': field.value,
							'theme': 'light',
							//'callback': verifyCallback,
							'callback': app.recaptcha.verifyCallback,
							//'expired-callback': expiredCallback,
							'expired-callback': app.recaptcha.expiredCallback,
						});*/
					}
					else {
						app.recaptcha.fields.push(field);
					}
				}
			}
		},
				
		/* Инициализирует скроллеры сущности
		* @entity - описатель сущности
		*/
		initScrollers: function (entity) {
			// присваиваем идентификаторы скролерам, чтобы на привыводе основных данных сущности оставить метки для их размещения
			for (var key in entity.scrollers) {
				app.core.scroller.init(entity.scrollers[key]);
			}
		}
	};
}(app));
