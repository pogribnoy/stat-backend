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
		//mialogEl = null;
		modalsOverlayEl: null,
		modalCount: 0,
		BASE_ZINDEX: 1040,
		
		init: function () {
			if(!app.core.modal.modalsContainer) {
				app.core.modal.modalsContainer = document.createElement('div');
				app.core.modal.modalsOverlayEl = document.createElement('div');
				var overlayEl = app.core.modal.modalsOverlayEl;
				overlayEl.id = "app.core.modal.modalsOverlayEl";
				app.core.addCSSClassFix(overlayEl, "modal-overlay");
				overlayEl.tabIndex = -1;
				overlayEl.style.display = 'none';
				app.core.modal.modalsContainer.appendChild(overlayEl);
				
				document.body.appendChild(app.core.modal.modalsContainer);
				//app.core.modal.mialogEl = document.querySelector('.dialog');
				//app.core.modal.modalOverlay = document.querySelector('.dialog-overlay');
			}
		},
		
		/* Рисует модалку
		@html: HTML того, что надо вставить в тело
		@options: параметры (isWide - растяжение по ширине экрана, selectStyle - в модалке показывается скроллер для выбора значений, title - заголовок)*/
		//render: function (descriptor, targetContainer) {
		render: function (innerHtml, options) {
			var containerId = app.core.createUDID();
						
			return new Promise(function (resolve, reject) {
				app.core.getTemplateByName('modal_template2.dev')
				.then(function(data) {
					// отрисовываем модалку
					var html = data.tmpl.render({html:innerHtml, modalContainerId: containerId, options: options});
					
					//app.core.modal.modalsContainer.innerHTML += html;
					var newModalEl = document.createElement('div');
					newModalEl.innerHTML = html;
					while (newModalEl.firstChild) {
						app.core.modal.modalsContainer.appendChild(newModalEl.firstChild);
					}
					
					app.core.containers[containerId] = {jqobj:$("#" + containerId), DOMEl: document.getElementById(containerId), data: options.descriptor };
					
					app.core.modal.initModalScripts(containerId);
					console.log("app.core.modal.render. Отрисовано модальное окно");
					resolve(containerId);
				})
				.catch(function (error) {
					console.error("app.core.modal.render. Операция не успешна");
					if(error) console.error(error);
					reject();
				})// показываем модалку, если она используется
				/*.then(function (renderData) {
					// указываем отдельный контейнер для скроллера в модалке
					app.core.containers[localScroller.localData.containerId] = {jqobj:app.core.containers[renderData.modalContainerId].jqobj.find("#" + localScroller.localData.containerId), data: localScroller, parent_container_id: renderData.modalContainerId};
					
					app.core.scroller.initScrollerScripts(localScroller.localData.containerId);
					
					if(options.inModal) app.core.modal.show(renderData.modalContainerId);
				})*/;
			});
		},
		/* Отображает модальное окно
		@modalContainerId: идентификатор контейнера модального окна */
		show: function (containerId) {
			if(containerId === undefined || !containerId) containerId = null;
			
			if(containerId && app.core.containers[containerId] && app.core.containers[containerId].jqobj) {
				app.core.modal.modalCount++;
				app.core.containers[containerId].DOMEl.removeAttribute('aria-hidden');
				app.core.containers[containerId].DOMEl.style['z-index'] = app.core.modal.BASE_ZINDEX + (app.core.modal.modalCount * 20) + 10;
				app.core.containers[containerId].DOMEl.style['overflow'] = 'scroll';
				if(app.core.modal.modalCount == 1) {
					app.core.modal.modalsOverlayEl.style.display = 'block';
					document.getElementsByTagName('body')[0].style['overflow'] = 'hidden';
				}
				
				
				$.notifyClose();
				console.log("app.core.modal.show. Показано модальное окно");
			}
			else {
				console.error("app.core.modal.show. Не найден контейнер модального окна");
			}
		},
		/* Закрывает модальное окно по нажатию на кастомную кнопку Закрыть/Отмена и пр.
		@modalContainerId: идентификатор контейнера модального окна */
		close: function (modalContainerId) {
			if(modalContainerId === undefined || !modalContainerId) modalContainerId = null;
			
			if(modalContainerId && app.core.containers[modalContainerId] && app.core.containers[modalContainerId].DOMEl) {
				var container = app.core.containers[modalContainerId];
				container.DOMEl.parentNode.removeChild(container.DOMEl);
				app.core.modal.modalCount--;
				if(app.core.modal.modalCount <= 0) {
					app.core.modal.modalsOverlayEl.style.display = 'none';
					document.getElementsByTagName('body')[0].style['overflow'] = 'scroll';
				}
				
				delete app.core.containers[container.data.localData.containerId];
				delete app.core.containers[modalContainerId];
				
				console.log("app.core.modal.close. Закрыто модальное окно");
			}
			else {
				console.error("app.core.modal.close. Не удалось закрыть модальное окно");
			}
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


