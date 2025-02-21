<?php

define('ROOT_DIR', __DIR__);

$cpr = require_once ROOT_DIR .'/vendor/autoload.php';

$app = new wlib\Application\Sys\Kernel([
	'sys.config_dir'	=> ROOT_DIR .'/config',
	'sys.composer'		=> &$cpr,
]);

// Register your dependencies before running app
// $app->register(App\Providers\MyDiProvider::class);
$app->register(wlib\Application\Sys\TracyDiProvider::class);

$app->run();