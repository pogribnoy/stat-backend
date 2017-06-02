function initScrollerScripts(container_id) {
	var container = containers[container_id];
	// если у скроллера есть общий чекбокс для всех, то настраиваем его переключение
	var toggleAll = container.jqobj.find("input.toggle-all:not(:has(.inited))");
	toggleAll.on('change', function() {
		//alert(containers[container_id].jqobj.find("tbody tr input[name^='row_scroller']").length);
		containers[container_id].jqobj.find("input[name^='row_scroller']").each(function(indx, element){
			$(this).prop("checked", $("#" + container_id + " input.toggle-all[name='" + container_id + "']").prop('checked'));
		});
	});
	toggleAll.addClass("inited");
	
	// если скроллер сворачиваемый, то добавляем смену индикатора развернутости в заголовке скроллера
	container.jqobj.find(".panel-heading[data-toggle='collapse']").each(function(indx, element) {
		$(this).on('click', function(e) {
			var icon = $(this).find('i');
			if(icon.hasClass('glyphicon-minus')) icon.removeClass('glyphicon-minus').addClass('glyphicon-plus');
			else icon.removeClass('glyphicon-plus').addClass('glyphicon-minus');
		});
	});
	
	// если скроллер открыт на странице, то инициализируем кнопки удаления, с вопросом о подтверждении
	/*if(!container.parent_container_id) {
		container.jqobj.find("button[data-toggle='popover']").each(function(indx, element) {
			var btn = $(this);
			// определяем, является ли это кнопка удаления
			var rb = btn.find('span.glyphicon-remove');
			if(rb.length == 1) {
				var options = {
					placement: 'top',
					title: 'Вы уверены, что хотите удалить запись?',
					content: function() {
						var html = '<button type="button" class="btn btn-xs" aria-label="Да" name="yes" onclick="">Да</button><button type="button" class="btn btn-xs" aria-label="Нет" name="no" onclick="">Нет</button>';
						return html;
					},
				}
				var pp = btn.popover(options);
				pp.find('div.popover-content').each(function(indx, element) {
					($this).html('<button type="button" class="btn btn-xs" aria-label="Да" name="yes" onclick="">Да</button><button type="button" class="btn btn-xs" aria-label="Нет" name="no" onclick="">Нет</button>');
				});
			}
			btn.on('click', function() {$(this).popover('show');});
		});
	}*/
	
	// для полей input строки фильтра добавляем применение фильтрации по Enter
	container.jqobj.find("input[name^='filter_']:not(:has(.inited))").each(function(indx, element) {
		$(this).focusin(function(){
			$(this).bind('keypress', function(e) {
				if(e.keyCode==13){
					apply_filter(container_id);
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
	container.jqobj.find("select[name^='filter_']").each(function(indx, element) {
		$(this).bind('change', function(e) {
			apply_filter(container_id);
		});
	});
}

/* Переход к просмотру строки
* @container_id - идентифкатор контейнера (скроллера), запись которого необходимо открыть на просмотр
* @id - id (eid) просматриваемой сущности (null, если создается новая сущность)
*/
function row_show(container_id, id) {
	// родительский контейнер (скроллер), элемент которого открываем
	var container = containers[container_id];
	var scroller = container.data;
	url = '/'+scroller.entityNameLC + '/show' + (id ? '?id=' + id : '');
	
	if(scroller.edit_style=="url") {
		//console.log("row_edit_entity=" + url);
		document.location = url;
	}
	else if(scroller.edit_style=="modal") {
		// если сущность открыта в другой модалке или на родительской форме, то сообщаем об этом
		if(entities[scroller.entityNameLC] && entities[scroller.entityNameLC][id]) {
			if(containers[entities[scroller.entityNameLC][id].local_data.container_id]) {
				// TODO. такие уведомлялки делать в виде Popup на кнопке, исчезающего через несколько секунд
				alert("Сущность уже открыта на редактирование в другой экранной форме. Завершите работу с диалогами и вернитесь к редактированию сущности");
				return;
			}
			// если сущность не сохранена на сервере или сущность сохранена на сервере, но она отредактирована / удалена локально
			if(entities[scroller.entityNameLC][id].fields.id.value == -1 || (entities[scroller.entityNameLC][id].fields.id.value != -1 && entities[scroller.entityNameLC][id].local_data.status != 'actual')) {
				// рисуем модалку
				var renderData = renderModal(entities[scroller.entityNameLC][id], container);
				renderData.dfd.done(function(data){
					// показываем модалку
					showModal(renderData.container_id);
				});
				return;
			}
		}
		
		// запрашиваем с сервера более подробную информацию (всю сущность, а не только скроллерную запись)
		// при этом, если выполняется операция add, то будет запрошена информация пустой сущности
		$.ajax({
			url: url,
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
					//console.log("С сервера получены полные данные сущности скроллера:");
					console.log(json);
					
					// сохраняем полученные данные
					var local_entity = saveFullServerEntity(json);
					//if(!id) local_entity.local_data.status = 'added';
					//local_entity.local_data.target_container_id = container.local_data.container_id;
					if(scroller.relationType) local_entity.local_data.relationType = scroller.relationType;
					
					// присваиваем идентификаторы скролерам, чтобы при выводе основных данных сущности оставить метки для их размещения
					initEntityScrollers(local_entity);
					
					// рисуем модалку
					var renderData = renderModal(local_entity, container);
					renderData.dfd.done(function(data){
						// показываем модалку
						showModal(renderData.container_id);
					});
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
	else {
		alert("Запрошен просмотр сущности с неизвестным scroller.edit_style");
	}
}

/* Переход к редактированию строки
* @container_id - идентифкатор контейнера (скроллера), запись которого необходимо открыть на редактирование
* @id - id (eid) редактируемой сущности (null, если создается новая сущность)
*/
function row_edit(container_id, id) {
	// родительский контейнер (скроллер), элемент которого открываем
	var container = containers[container_id];
	var scroller = container.data;
	url = '/'+scroller.entityNameLC + '/edit' + (id ? '?id=' + id : '');
	
	if(scroller.edit_style=="url") {
		//console.log("row_edit_entity=" + url);
		document.location = url;
	}
	else if(scroller.edit_style=="modal") {
		// если сущность открыта в другой модалке или на родительской форме, то сообщаем об этом
		if(entities[scroller.entityNameLC] && entities[scroller.entityNameLC][id]) {
			if(containers[entities[scroller.entityNameLC][id].local_data.container_id]) {
				// TODO. такие уведомлялки делать в виде Popup на кнопке, исчезающего через несколько секунд
				alert("Сущность уже открыта на редактирование в другой экранной форме. Завершите работу с диалогами и вернитесь к редактированию сущности");
				return;
			}
			// если сущность не сохранена на сервере или сущность сохранена на сервере, но она отредактирована / удалена локально
			if(entities[scroller.entityNameLC][id].fields.id.value == -1 || (entities[scroller.entityNameLC][id].fields.id.value != -1 && entities[scroller.entityNameLC][id].local_data.status != 'actual')) {
				// рисуем модалку
				var renderData = renderModal(entities[scroller.entityNameLC][id], container);
				renderData.dfd.done(function(data){
					// показываем модалку
					showModal(renderData.container_id);
				});
				return;
			}
		}
		
		// запрашиваем с сервера более подробную информацию (всю сущность, а не только скроллерную запись)
		// при этом, если выполняется операция add, то будет запрошена информация пустой сущности
		$.ajax({
			url: url,
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
					//console.log("С сервера получены полные данные сущности скроллера:");
					console.log(json);
					
					// сохраняем полученные данные
					var local_entity = saveFullServerEntity(json);
					//if(!id) local_entity.local_data.status = 'added';
					//local_entity.local_data.target_container_id = container.local_data.container_id;
					if(scroller.relationType) local_entity.local_data.relationType = scroller.relationType;
					
					// присваиваем идентификаторы скролерам, чтобы при выводе основных данных сущности оставить метки для их размещения
					initEntityScrollers(local_entity);
					
					// рисуем модалку
					var renderData = renderModal(local_entity, container);
					renderData.dfd.done(function(data){
						// показываем модалку
						showModal(renderData.container_id);
					});
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
	else {
		alert("Запрошено создание/редактирование сущности с неизвестным scroller.edit_style");
	}
}

/* Удаляет строку из скроллера/грида. Если скроллер отображается не в рамках сущности, то удаление строки выполняется с сервера
* @container_id - идентифкатор контейнера (скроллера), запись которого необходимо удалить
* @id - id удаляемой сущности
*/
function row_delete(container_id, id) {
	container = containers[container_id];
	// родительский скроллер/грид, с элементом которого работаем
	var scroller = containers[container_id].data;
	// описатель удаляемой сущности
	var entity = entities[scroller.entityNameLC][id];
	
	// удаляем с запросом, если этот скроллер открыт на отдельной странице или в ммодеальном окне,т.е. не предусмотрена кнопка "Сохранить" или "Применить"
	if((!container.parent_container_id)){// ||
		// отображается на форме сущности и отношение 'n' и сущность, т.е. сущности скроллера создаются только для текущей сущности
		//(container.parent_container_id && containers[container.parent_container_id].data.type == 'entity' && scroller.relationType == 'n')) {
		
		entityDelete(container_id, id).done(function (){
			deleteItemFromScroller(entity, scroller, {confirmFromServer:true});
			renderScroller(scroller, false);
		});
	}
	// помечаем удаленными в скроллере
	else {
		deleteItemFromScroller(entities[scroller.entityNameLC][id], scroller, {confirmFromServer:false})
		renderScroller(scroller, false);
	}
}

// Запрашивает и выводит отфильтрованные данные
function apply_filter(container_id) {
	var container = containers[container_id];
	var data = container.data;
	var udid = data.local_data.udid;
	var jqobj = container.jqobj;
	var rq = {};
	var controllerName = data.controllerName;
	
	url = '/'+controllerName+'/index';
	
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
	var page = jqobj.find('#page').val();
	//url += 'page=' +  encodeURIComponent(page ? page : 1);
	rq.page = encodeURIComponent(page ? page : 1);
	
	var sort = jqobj.find('#sort').val();
	//if (sort) url += '&sort=' + encodeURIComponent(sort);
	if (sort) rq.sort = encodeURIComponent(sort);
	
	var order = jqobj.find('#order').val();
	//if (order) url += '&order=' + encodeURIComponent(order);
	if (order) rq.order = encodeURIComponent(order);
	
	var page_size = jqobj.find("select[name='page_sizes']").val();
	//if (page_size) url += '&page_size=' + encodeURIComponent(page_size);
	if (page_size) rq.page_size = encodeURIComponent(page_size);
	
	// пробегаемся по столбцам, чтобы собрать данные фильтров в столбцах данных
	var tr_offs = (data.group_operations && data.group_operations.length > 0) ? 2 : 1;
	var columns = jqobj.find("table tr:eq("+tr_offs+") td");
	var columns_length = columns.length;
	for(var i=0; i<columns_length; i++){
		var field = columns.eq(i).find("input[name^='filter_']").filter("[type='text'], [type='number'], [type='email']");
		if(field.val()) {//url += '&'+field.attr("name")+'=' + encodeURIComponent(field.val());
			rq[field.attr("name")] = encodeURIComponent(field.val());
			//console.log('filter = ' + rq[field.attr("name")]);
		}
		else {
			field = columns.eq(i).find("select[name^='filter_']");
			if(field.val() && field.val() == "**") {
				rq[field.attr("name")] = encodeURIComponent(field.val());
			}
			else if(field.val() && field.val() != "*") {
				rq[field.attr("name")] = encodeURIComponent(field.val());
			}
			else {
				// TODO: checkbox и пр.
			}
		}
	}
	
	// доп. фильтры, используются при фильтрации скроллеров в сущностях для передачи id сущности
	if(data.add_filter) {
		rq.add_filter = {};
		for (var key in data.add_filter) rq.add_filter[key] = data.add_filter[key];
	}
	// исключение айдишников
	if(data.filter_values.exclude_ids) {
		rq.exclude_ids = data.filter_values.exclude_ids;
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
				delete json;
				
				// восстанавливаем способ редактирования после копирования
				this.data.add_style = add_style;
				
				// TODO. Необходимо удалить из added_items записи, которые находятся в полученном items, т.к. это значит, что кто-то уже добавил эту запись
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
				// TODO. Необходимо удалить из items записи, которые находятся в deleted_items
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
}

// клик по номеру страницы
function change_page(container_id, p){
	console.log('p='+p);
	containers[container_id].jqobj.find('#page').val(p);
	console.log('page='+containers[container_id].jqobj.find('#page').val());
	apply_filter(container_id);
}

// клик по сортировке колонки
function change_sort(container_id, id){
	var order = containers[container_id].jqobj.find("#order");
	var sort = containers[container_id].jqobj.find("#sort");
	if(sort.val()==id) order.val() == "DESC" ? order.val("ASC") : order.val("DESC");
	else sort.val(id);
	apply_filter(container_id);
}

// выбор количества строк на странице
function change_page_size(container_id){
	containers[container_id].jqobj.find("#page_size").val(containers[container_id].jqobj.find("select[name='page_sizes']").val());
	apply_filter(container_id);
}

// Очистка бизнес-полей фильтра, запрашивает и выводит отфильтрованные данные
function clear_filter(container_id) {
	var data = containers[container_id].data;
	var tr_offs = ((data.group_operations && data.group_operations.length > 0) ? 2 : 1);
	var columns = containers[container_id].jqobj.find("#" + createUDID(data) + " table tr:eq("+tr_offs+") td");
	for(var i=0;i<columns.length;i++){
		columns.eq(i).find("input[name^='filter_'], select[name^='filter_']").each(function(){
			$(this).val("");
		});
		// TODO: checkbox и пр.
	}
	apply_filter(container_id);
}

