(function (app) {
	"use strict";
	
	var currLanguage = 'ru';
	
	var codes = {
		ru: {
			text_no_data: 'Нет данных для отображения',
			text_page_sizes: 'Показывать по',
		},
	};
	
	var t = {};
	
	t._ = function(code, params = null) {
		if(!code || typeof code != 'string' || (params != null && typeof params != 'object')) return '<Translator. Wrong params>';
		
		var result = code;
		if(codes[currLanguage][code]) {
			result = codes[currLanguage][code];
			for (var key in params) {
				var value = params[key];
				result = result.replace(new RegExp('{' + key + '}', 'g') , value);
			}
		}
		return result;
	};
	
	t.addTranslation = function(translations, language) {
		for (var key in translations) {
			if(!codes[language]) codes[language] = {};
			codes[language][key] = translations[key];
		}
	};
	
	app.t = t;
}(app));