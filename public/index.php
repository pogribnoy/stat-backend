<?php
error_reporting(E_ALL);
use Phalcon\Mvc\Application;

//$_GET['_url'] = '/contact/send';
//$_SERVER['REQUEST_METHOD'] = 'POST';

try {

	define('APP_PATH', realpath('..') . '/');

	/**
	 * Read the configuration
	 */
	if (file_exists(APP_PATH . 'app/config/config.php')) require APP_PATH . 'app/config/config.php';
	else echo "Config file not found: " . APP_PATH . 'app/config/config.php';

	/**
	 * Auto-loader configuration
	 */
	//require APP_PATH . 'app/config/loader.php';
	$loader = new \Phalcon\Loader();
	$loader->registerDirs([
		APP_PATH . $config->application->commonControllersDir,
		APP_PATH . $config->application->commonPluginsDir,
		APP_PATH . $config->application->commonLibraryDir,
		APP_PATH . $config->application->commonModelsDir,
		APP_PATH . $config->application->controllersDir,
	]);
	 $loader->register();

	/**
	 * Load application services
	 */
	require APP_PATH . 'app/config/services.php';

	$application = new Application($di);

	echo $application->handle()->getContent();

} catch (Exception $e){
	echo $e->getMessage();
}
