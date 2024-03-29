<?php
chdir(__DIR__);
require_once './vendor/autoload.php';
require_once './src/LogFactory.php';
require_once './src/AccountInfo.php';
require_once './src/TestResult.php';
require_once './src/HealthCheck.php';
$di = new \Dice\Dice();
set_time_limit(0);
$hc = $di->create(\Modd\Engine\HealthCheck\HealthCheck::class);
$hc->run();
