/* Инициализирует скрипты для модального окна
* @container_id - идентификатор контейнера модального окна
*/
function initModalScripts(container_id) {
	//console.log ('initModalScripts');
	// при закрытии окна через крестик или клике мимо окна необходимо выполнить операцию закрытия
	containers[container_id].jqobj.on('hidden.bs.modal', function (e) {
		//console.log ('hidden.bs.modal');
		closeModal(container_id);
		//$(this).removeData("modal");
	});
}

// Скрываем модальное окно
function hideModal(container_id) {
	containers[container_id].jqobj.modal('hide');
}

// Показывает контейнр в модальном окне
function showModal(container_id) {
	containers[container_id].jqobj.modal('show');
}

// Закрывает модальное окно
function closeModal(container_id) {
	// контейнер модалки
	var container = containers[container_id];
	//console.log ('closeModal, container_id=' + container_id);
	// удаляем контейнер сущности или скроллера, отрисованного в модалке
	delete containers[container.data.local_data.container_id];
	// удаляем ссылку на контейнер, для которого был открыт данный
	delete container.data.local_data.target_container_id;
	// удаляем контейнеры скроллеров
	if(container.data.scrollers) {
		for (var key in container.data.scrollers) {
			var sld = container.data.scrollers[key].local_data;
			// удаляем сам скроллер
			delete containers[sld.container_id];
		}
	}
	// удаляем ссылку на контейнер основной сущности/скроллера модалки
	delete container.data.local_data.container_id;
	// удаляем контейнер модалки из DOM
	container.jqobj.remove();
	// удаляем контейнер модалки из контейнеров
	delete containers[container_id];
}

/*
* Рисует сущность в модальном окне, создает контейнер и возвращает его ID. Не отображает ничего пользователю, для этого используется отдельная функция showModal
* descriptor - описатель основных скроллера или сущности, которые открываются в модалке
* targetContainer - контейнер, в который будет подставлено выбранное в модалке значение (если это модалка для выбора)
*/
function renderModal(descriptor, targetContainer) {
	var modal_container_id = createUDID();
	// сохраняем в сущности идентификатор будущего контейнера
	descriptor.local_data.container_id = createUDID(descriptor);
	if(targetContainer) descriptor.local_data.target_container_id = targetContainer.data.local_data.container_id;
	
	var tmplName = getTemplateName(descriptor);
	
	// рисуем модалку, а в ней сущность или скроллер
	var tmpl = $.templates("#modal_template");
	var html = tmpl.render({descriptor:descriptor, tmplName:tmplName, modal_container_id:modal_container_id});
	
	$("#container_modals").append(html);
	containers[modal_container_id] = {jqobj:$("#container_modals").find("#" + modal_container_id), data: descriptor};
	
	// указываем отдельный контейнер для сущности или скроллера в модалке
	if(descriptor.type == 'entity') {
		containers[descriptor.local_data.container_id] = {jqobj:containers[modal_container_id].jqobj.find("#" + descriptor.local_data.container_id), data: descriptor, parent_container_id: modal_container_id};
		// обрабатываем полученные скроллеры сущности, если они есть
		renderEntityScrollers(descriptor);
		initEntityScripts(descriptor.local_data.container_id);
	}
	else if(descriptor.type == 'scroller') {
		containers[descriptor.local_data.container_id] = {jqobj:containers[modal_container_id].jqobj.find("#" + descriptor.local_data.container_id), data: descriptor, parent_container_id: modal_container_id};
		initScrollerScripts(descriptor.local_data.container_id);
	}
	
	initModalScripts(modal_container_id);
	
	
	return modal_container_id;
}

/* Находит сущность/скроллер, для которого было открыто модальное окно.
* Обновляет данные в сущности.
* Перерисовывает поле сущности/скроллер.
* Закрывает текущее модальное окно.
*/
function linkSelectedRows(source_container_id){
	/* Алгоритм:
		1. Находим связанную сущность.
		2. Находим поле, которое заполняется из этого справочника
		3. Обновляем значение поля
		4. Перерисовываем поле в интерфейсе
	*/
	var sContainer = containers[source_container_id];
	var tContainer = containers[sContainer.data.local_data.target_container_id];
	var tScroller;
	var entity;
	
	// собираем выбранные скроки
	var rows = sContainer.jqobj.find("tbody input[name^='row_']:checked");
	
	selectTarget = tContainer.data.local_data.selectTarget;
	tData = tContainer.data;
	
	if(tContainer.data.type == 'entity'){
		// находим поле, для которого делали выбор
		// если выборделается для поля сущности
		if(selectTarget.field_id) {
			var tField = tData.fields[selectTarget.field_id];
		}
		else return;
		
		// если выбрано одно значение в скроллере
		if(rows.length==1) {
			// находим сущность
			var name = rows[0].id;
			var entity_id = name.substring(4, name.length);
			entity = entities[sContainer.data.entity][entity_id];		
			console.log(entity);
			
			// обновляем значение поля
			tField.value_id = entity.fields.id.value;
			tField.value = entity.fields[tField.field].value;
			tField.entity = entity;
		}
		// если в скроллере ничего не выбрано
		else if(rows.length==0) {
			console.log("В скроллере ничего не выбрано");
			// обновляем значение поля
			tField.value_id = null;
			tField.value = null;
		}
		else {
			console.log("В скроллере выбраны две или более записей");
			// обновляем значение поля
			tField.value_id = null;
			tField.value = null;
		}
		
		tData.local_data.status = "edited";
		// перерисовываем поле
		var tmpl = $.templates["entity_field_" + tField.type];
		
		//tField.container_id = tData.local_data.container_id;
		var html = tmpl.render(tField);
		
		var jqf = tContainer.jqobj.find("[id='field_"+tField.id+"_value']").parent();
		jqf.replaceWith(html);
		hideModal(sContainer.parent_container_id);			
	}
	// если выбор делается для скроллера (грида)
	else if(tContainer.data.type == 'scroller') {
		var rowsLength = rows.length;
		if(rowsLength>0) {
			for(var i=0; i<rowsLength; i++) {
				var name = rows[i].id;
				var entity_id = name.substring(4, name.length);
				
				console.log(entities[sContainer.data.entity][entity_id]);
				tScroller = tContainer.data;
				
				addItemToScroller(entities[sContainer.data.entity][entity_id], tScroller, {confirmFromServer:false});
			}
			// перерисовываем грид/скроллер, для которого выполнено добавление
			var tmpl = $.templates("#"+getTemplateName(tScroller));
			
			var html = tmpl.render({descriptor:tScroller});
			
			//tContainer.jqobj.replaceWith(html);
			tContainer.jqobj.html(html);
		}
		hideModal(sContainer.parent_container_id);
	}
}

/* Грязный хак для мультимодаьлности Bottstrap
* Ввзят отсюда: http://www.bootply.com/cObcYInvpq#
* Проект: https://github.com/jhaygt/bootstrap-multimodal
*/
(function($, window) {
    'use strict';

    var MultiModal = function(element) {
        this.$element = $(element);
        this.modalCount = 0;
    };

    MultiModal.BASE_ZINDEX = 1040;

    MultiModal.prototype.show = function(target) {
        var that = this;
        var $target = $(target);
        var modalIndex = that.modalCount++;

        $target.css('z-index', MultiModal.BASE_ZINDEX + (modalIndex * 20) + 10);

        // Bootstrap triggers the show event at the beginning of the show function and before
        // the modal backdrop element has been created. The timeout here allows the modal
        // show function to complete, after which the modal backdrop will have been created
        // and appended to the DOM.
        window.setTimeout(function() {
            // we only want one backdrop; hide any extras
            if(modalIndex > 0)
                $('.modal-backdrop').not(':first').addClass('hidden');

            that.adjustBackdrop();
        });
    };

    MultiModal.prototype.hidden = function(target) {
        this.modalCount--;

        if(this.modalCount) {
           this.adjustBackdrop();

            // bootstrap removes the modal-open class when a modal is closed; add it back
            $('body').addClass('modal-open');
        }
    };

    MultiModal.prototype.adjustBackdrop = function() {
        var modalIndex = this.modalCount - 1;
        $('.modal-backdrop:first').css('z-index', MultiModal.BASE_ZINDEX + (modalIndex * 20));
    };

    function Plugin(method, target) {
        return this.each(function() {
            var $this = $(this);
            var data = $this.data('multi-modal-plugin');

            if(!data)
                $this.data('multi-modal-plugin', (data = new MultiModal(this)));

            if(method)
                data[method](target);
        });
    }

    $.fn.multiModal = Plugin;
    $.fn.multiModal.Constructor = MultiModal;

    $(document).on('show.bs.modal', function(e) {
        $(document).multiModal('show', e.target);
    });

    $(document).on('hidden.bs.modal', function(e) {
        $(document).multiModal('hidden', e.target);
    });
}(jQuery, window));