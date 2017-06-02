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
	/*"email" => array(
		"username" => "rashodygoroda",
		"password" => "refaliu",
		"from" => "rashodygoroda@yandex.ru",
		"charset" => "utf-8",
		"SMTP" => [
			"host" => "smtp.yandex.ru",
			"secure" => "ssl", //tls, SSL
			"port" => 465, //"587","465",
		],
		"POP3" => [
			"host" => "pop.yandex.ru",
			"secure" => "SSL",
			"port" => "995",
		],
		"IMAP" => [
			"host" => "imap.yandex.ru",
			"secure" => "SSL",
			"port" => "993",
		],
    ),*/
	"email" => array(
		// rashodygoroda@yandex.ru|refaliu
		"username" => 'info@rashodygoroda.org',
		"password" => "s4df\$G3d", // s4df$G3d
		"from" => 'info@rashodygoroda.org',
		"charset" => "utf-8",
		"SMTP" => [
			"host" => "smtp.yandex.ru",
			"secure" => "ssl", //tls, SSL
			"port" => 465, //"587","465",
		],
		"POP3" => [
			"host" => "pop.yandex.ru",
			"secure" => "SSL",
			"port" => "995",
		],
		"IMAP" => [
			"host" => "imap.yandex.ru",
			"secure" => "SSL",
			"port" => "993",
		],
    ),
    "application" => array(
		"version" => "0.3",
		"module" => "backend",
		//"host" => "vhost.dlinkddns.com:81",
		"host" => "rashodygoroda.org",
		//"host" => "rgor-b.ddns.net",
		//"commonHost" => "http://vhost.dlinkddns.com:81",
		"commonHost" => "http://rs.rashodygoroda.org",
		"commonControllersDir" => "app/common/controllers/",
		"commonPluginsDir" => "app/common/plugins/",
		"commonLibraryDir" => "app/common/library/",
		"commonModelsDir" => "app/common/models/",
		//"commonTemplatesDir" => "app/views/templates/",
		"commonPartialsDir" => "partials/common/",
		"commonTemplatesDir" => "/public/templates/",
		"noImage" => "no_image.jpg",
		"cacheACL" => 0, // кешировать ACL из БД
		"filesUploadDirectory" => "upload/files/", //Каталог, в который должны загружаться файлы сущностей. В конце обязательно указание символа "/";
		"tablePageSizes" => "[30,50,100]", // Ограничение количества строк для таблиц
		"tableMaxPageSize" => "200", // Максимальное количество строк для таблиц
		//"sessionTimeout" => "43200", //12ч // Время жизни сессии в секундах
		"sessionTimeout" => "1200", // Время жизни сессии в секундах
		'caching' => [
			"aclCacheDedaultTime" => 20, // в секундах, используется для ACL
			'dataCacheDedaultTime' => 60, // в секундах, используется для структурированных данных
			'viewCacheDedaultTime' => 60, // в секундах, используется для представлений
		],
		
		"controllersDir" => "app/controllers/",
		"viewsDir" => "app/views/",
		"layoutsDir" => "app/views/layouts/",
		"partialsDir" => "app/views/partials/",
		"templatesDir" => "app/views/templates/",
		"adminRoleID" => '1',
		"guestRoleID" => '2',
		"orgOperatorRoleID" => '5',
		"orgAdminRoleID" => '6',
		"requestStatus" => [
			"newStatusID" => '1',
			"processedStatusID" => '2',
			"doneStatusID" => '5',
		],
		"reCaptchaPublicKey" => "6LdBTg8UAAAAABPJQ5TBv1X-aX6p0KhkORpd7JAl",
		"reCaptchaSecretKey" => "6LdBTg8UAAAAABEspdEcFpSwj0YBIFJp4iJe_LF3",
	)
));