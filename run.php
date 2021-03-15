<?php
chdir(__DIR__);
require_once './src/LogFactory.php';
require_once './src/AccountInfo.php';
require_once './src/TestResult.php';
require_once './src/HealthCheck.php';
require_once './vendor/autoload.php';
$di = new \Dice\Dice();
$hc = $di->create(\Modd\Engine\HealthCheck\HealthCheck::class);
$hc->run();
