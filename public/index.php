<?php
define('ROOT_PATH', dirname(__DIR__));
require_once __DIR__."/../vendor/autoload.php";

$application = new \common\web\WebApplication();
$application->run();