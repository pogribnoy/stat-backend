/*(function (app) {
	"use strict";
	
	var currLanguage = 'ru';
	
	var messages = {
		ru: {
			text_no_data: 'Нет данных для отображения',
			text_page_sizes: 'Показывать по',
		},
	};
	
	var t = {};
	
	t._ = function(code, params = null) {
		if(!code || typeof code != 'string' || (params !== null && typeof params !== 'object')) return '<Translator. Wrong params>';
		
		var result = code;
		if(messages[currLanguage][code]) {
			result = messages[currLanguage][code];
			for (var key in params) {
				var value = params[key];
				result = result.replace(new RegExp('{' + key + '}', 'g') , value);
			}
		}
		return result;
	};
	
	t.addTranslation = function(translation, language) {
		for (var key in translation) {
			if(!messages[language]) messages[language] = {};
			messages[language][key] = translation[key];
		}
	};
	
	t.messages = messages;
	
	app.t = t;
	
}(app));*/