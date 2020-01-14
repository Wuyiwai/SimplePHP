<?php


namespace common\web;


use common\base\Controller;

class WebController extends Controller
{
    public function render($view, $params = [])
    {
        extract($params);
        return require '../views/' . $view . '.php';
    }

    public function toJson($data)
    {
        if (is_string($data)) {
            return $data;
        }
        return json_encode($data);
    }
}