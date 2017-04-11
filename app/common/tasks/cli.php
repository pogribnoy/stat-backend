<?php

use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Loader;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Events\Manager as EventsManager;

define('APP_PATH', realpath(__DIR__ . "/../../..") . "/");

// Используем стандартный для CLI контейнер зависимостей
$di = new CliDI();

/**
 * Регистрируем автозагрузчик, и скажем ему, чтобы зарегистрировал каталог задач
 */
$loader = new Loader();

$loader->registerDirs(
    [
        __DIR__ . "/",
        realpath(__DIR__ . "/../models") . "/",
    ]
);

$loader->register();


// Загружаем файл конфигурации, если он есть

$configFile = APP_PATH . "app/config/config.php";
echo "DIR=" . __DIR__ . PHP_EOL;
echo "APP_PATH=" . APP_PATH . PHP_EOL;
echo "configFile=" . $configFile . PHP_EOL;

if (file_exists(APP_PATH . 'app/config/config.php')) require APP_PATH . 'app/config/config.php';
else echo "Config file not found: " . APP_PATH . 'app/config/config.php';

$di->set("config", $config);

echo "config=" . json_encode($di["config"]) . PHP_EOL . PHP_EOL;

// Соединение с БД создается на основе параметров из конфигурационного файла
$di->setShared('db', function() use ($config) {
	/*$eventsManager = new EventsManager();
	$logger = new FileAdapter(APP_PATH . "app/logs/db_tasks.log", array('mode' => 'a'));
	echo "FileAdapter: done" . PHP_EOL;
	// Слушаем все события БД
	$eventsManager->attach('db', function($event, $connection) use ($logger) {
        if ($event->getType() == 'beforeQuery') {
            $logger->log($connection->getSQLStatement());
        }
    });*/
	
	$dbclass = "Phalcon\Db\Adapter\Pdo\\" . $config->database->adapter;
	$connection = new $dbclass(array(
		"host"    	=> $config["database"]["host"],
		"username"	=> $config["database"]["username"],
		"password"	=> $config["database"]["password"],
		"dbname"	=> $config["database"]["name"],
		"charset"	=> $config["database"]["charset"]
	));
	
	echo "connection=" . json_encode($connection) . PHP_EOL;
	
	 // Привзываем eventsManager к адаптеру БД
    //$connection->setEventsManager($eventsManager);
	
	return $connection;
});

//var_dump($di);

echo "db=" . json_encode($di["db"]) . PHP_EOL;

// Создаем консольное приложение
$console = new ConsoleApp();

$console->setDI($di);

$di->setShared("console", $console);

/**
 * Определяем консольные аргументы
 */
$arguments = [];


foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments["task"] = $arg;
    } elseif ($k === 2) {
        $arguments["action"] = $arg;
    } elseif ($k >= 3) {
        $arguments["params"][] = $arg;
    }
}
echo "arguments=" . json_encode($arguments) . PHP_EOL;

try {
    // обрабатываем входящие аргументы
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();

    exit(255);
}