<?php
use Phalcon\Config;

$config_array = array(
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
		"host" => "vhost.dlinkddns.com:81",
		"commonHost" => "http://vhost.dlinkddns.com:81",
		"commonControllersDir" => "app/common/controllers/",
		"commonPluginsDir" => "app/common/plugins/",
		"commonLibraryDir" => "app/common/library/",
		"commonModelsDir" => "app/common/models/",
		"commonTemplatesDir" => "app/views/templates/",
		"noImage" => "no_image.jpg",
		"cacheACL" => 0,
		"filesUploadDirectory" => "upload/files/", //Каталог, в который должны загружаться файлы сущностей. В конце обязательно указание символа "/";
		"tablePageSizes" => "[30,50,100]", // Ограничение количества строк для таблиц
		"tableMaxPageSize" => "100", // Максимальное количество строк для таблиц
		
		"controllersDir" => "app/controllers/",
		"viewsDir" => "app/views/",
		"layoutsDir" => "app/views/layouts/",
		"partialsDir" => "app/views/partials/",
		"templatesDir" => "app/views/templates/",
	)
);
$config = new Config($config_array);