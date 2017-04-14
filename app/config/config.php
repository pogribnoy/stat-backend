<?php
use Phalcon\Config;

$config = new Config(array(
    "database" => array(
		"adapter" => "Mysql",
		"host" => "localhost",
		"username" => "root",
		"password" => "refaliu",
		"name" => "stat",
		"charset" => "UTF8",
    ),
    "application" => array(
		"module" => "backend",
		//"host" => "vhost.dlinkddns.com:81",
		"host" => "rgor-b.ddns.net",
		//"commonHost" => "http://vhost.dlinkddns.com:81",
		"commonHost" => "http://rgor-b.ddns.net",
		"commonControllersDir" => "app/common/controllers/",
		"commonPluginsDir" => "app/common/plugins/",
		"commonLibraryDir" => "app/common/library/",
		"commonModelsDir" => "app/common/models/",
		"commonTemplatesDir" => "app/views/templates/",
		"noImage" => "no_image.jpg",
		"cacheACL" => 0, // кешировать ACL из БД
		"filesUploadDirectory" => "upload/files/", //Каталог, в который должны загружаться файлы сущностей. В конце обязательно указание символа "/";
		"tablePageSizes" => "[30,50,100]", // Ограничение количества строк для таблиц
		"tableMaxPageSize" => "100", // Максимальное количество строк для таблиц
		"sessionTimeout" => "7200", // Время жизни сессии в секундах
		
		"controllersDir" => "app/controllers/",
		"viewsDir" => "app/views/",
		"layoutsDir" => "app/views/layouts/",
		"partialsDir" => "app/views/partials/",
		"templatesDir" => "app/views/templates/",
	)
));