<?php
require_once __DIR__."/../vendor/autoload.php";

$urlParams = explode('/', $_SERVER['REQUEST_URI']);
$controllerName = "common\\controllers\\". ucfirst($urlParams[1]) . 'Controller';
$actionName = ucfirst(explode('?', $urlParams[2])[0]);
$controller = new $controllerName();
return call_user_func([$controller, 'action' . $actionName]);
