<?php


namespace common\web;


use common\base\Application;

class WebApplication extends Application
{
    public function handleRequest()
    {
        $urlParams = explode('/', $_SERVER['REQUEST_URI']);
        $controllerName = ucfirst(empty($urlParams[1]) ? 'site' : $urlParams[1]);
        $actionName = empty($urlParams[2]) ? 'test' : $urlParams[2];
        $controllerName = "common\\controllers\\". $controllerName . 'Controller';
        $actionName = ucfirst(explode('?', $actionName)[0]);
        $controller = new $controllerName();
        return call_user_func([$controller, 'action' . $actionName]);
    }
}