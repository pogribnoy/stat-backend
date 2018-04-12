(function (app) {
	"use strict";
	if(!app) {
		app = {core:{}};
	}
	else if(!app.core) {
		app.core = {};
	}
	
	app.core.modal = {
		modalsContainer: null,
		
		init: function () {
			if(!app.core.modal.modalsContainer) {
				app.core.modal.modalsContainer = document.createElement('div');
				document.body.appendChild(app.core.modal.modalsContainer);
			}
		},
		
		/* Рисует модалку
		@html: HTML того, что надо вставить в тело
		@options: параметры (isWide - растяжение по ширине экрана, selectStyle - в модалке показывается скроллер для выбора значений, title - заголовок)*/
		//render: function (descriptor, targetContainer) {
		render: function (innerHtml, options) {
			var modalContainerId = app.core.createUDID();
			
			return new Promise(function (resolve, reject) {
				app.core.getTemplateByName('modal_template2.dev')
				.then(function(data) {
					var renderData = null;
					// отрисовываем модалку
					var html = data.tmpl.render({html:innerHtml, modalContainerId: modalContainerId, options: options});
					
					app.core.modal.modalsContainer.innerHTML += html;
					
					app.core.containers[modalContainerId] = {jqobj:$("#" + modalContainerId), DOMEl: document.getElementById(modalContainerId), data: options.descriptor};
					
					app.core.modal.initModalScripts(modalContainerId);
					renderData = {modalContainerId: modalContainerId, html: html};
					console.log("app.core.modal.render. Отрисовано модальное окно");
					resolve(renderData);
				})
				.catch(function (error) {
					console.error("app.core.modal.render. Операция не успешна");
					if(error) console.error(error);
					reject();
				});
			});
		},
		/* Отображает модальное окно
		@modalContainerId: идентификатор контейнера модального окна */
		show: function (containerId) {
			if(containerId === undefined || !containerId) containerId = null;
			
			if(containerId && app.core.containers[containerId] && app.core.containers[containerId].jqobj) {
				app.core.containers[containerId].jqobj.modal('show');
				$.notifyClose();
				console.log("app.core.modal.show. Показано модальное окно");
			}
			else {
				console.error("app.core.modal.show. Не найден контейнер модального окна");
			}
		},
		/* Закрывает модальное окно по нажатию на кастомную кнопку Закрыть/Отмена и пр.
		@modalContainerId: идентификатор контейнера модального окна */
		hideModal: function (modalContainerId) {
			if(modalContainerId === undefined || !modalContainerId) modalContainerId = null;
			
			if(modalContainerId && app.core.containers[modalContainerId] && app.core.containers[modalContainerId].jqobj) {
				app.core.containers[modalContainerId].jqobj.modal('hide');
				console.log("app.core.modal.hideModal. Закрыто модальное окно");
			}
			else {
				console.error("app.core.modal.hideModal. Не удалось закрыть модальное окно");
			}
		},
		/* Закрывает модальное окно по событию "Нажатие на крестик"
		@modalContainerId: идентификатор контейнера модального окна */
		closeModal: function (modalContainerId) {
			// контейнер модалки
			var container = app.core.containers[modalContainerId];
			// удаляем контейнер в модалке
			if(container.data && container.data.localData && container.data.localData.containerId && app.core.containers[container.data.localData.containerId]) delete app.core.containers[container.data.localData.containerId];
			
			// удаляем контейнер модалки из DOM
			//container.jqobj.parent.removeChild(container.jqobj);
			//container.jqobj.modal('hide');
			//container.jqobj.remove();
			// удаляем контейнер модалки из контейнеров
			//delete app.core.containers[modalContainerId];
		},
		
		/* Инициализирует скрипты для модального окна
		@modalContainerId - идентификатор контейнера модального окна */
		initModalScripts: function (modalContainerId) {
			//console.log ('initModalScripts');
			// при закрытии окна через крестик или клике мимо окна необходимо выполнить операцию закрытия
			app.core.containers[modalContainerId].jqobj.on('hidden.bs.modal', function (e) {
				//console.log ('hidden.bs.modal');
				app.core.modal.closeModal(modalContainerId);
				//$(this).removeData("modal");
			});
		},
	};
	
	app.core.modal.init();
	
}(app));


