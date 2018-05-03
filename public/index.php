<?php
error_reporting(E_ALL);
use Phalcon\Mvc\Application;

//define('PROFILE', TRUE);

if(defined('PROFILE')) xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

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

if(defined('PROFILE')) {
	$xhprof_data = xhprof_disable('/tmp');

	$XHPROF_ROOT = "/var/www/xhprof/";
	include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
	include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

	$xhprof_runs = new XHProfRuns_Default();
	$run_id = $xhprof_runs->save_run($xhprof_data, "backend");

	//echo "http://178.215.86.165:83/index.php?run={$run_id}&source=backend\n";
}
