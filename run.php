<?php
chdir(__DIR__);
require_once('./vendor/autoload.php');

$di = new \Dice\Dice();
$hc = $di->create(\Modd\Engine\HealthCheck\HealthCheck::class);
$hc->run();
