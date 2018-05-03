(function (app) {
	"use strict";
	
	var messages = %messages%;
	
	var obj = {};
	
	obj._ = function(code, params) {
		if((!code || typeof code != 'string') && (params !== null && typeof params !== 'object')) return '<Модуль Переводчик. В функцию перевода переданы неверные параметры>';
		
		var result = code;
		if(messages[code]) {
			result = messages[code];
			for (var key in params) {
				var value = params[key];
				result = result.replace(new RegExp('{' + key + '}', 'g') , value);
			}
		}
		return result;
	};
	
	obj.messages = messages;
	
	app.t = obj;
	
}(app));
