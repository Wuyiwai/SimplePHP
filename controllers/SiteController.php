<?php


namespace common\controllers;


use common\web\WebController;

class SiteController extends WebController
{
    public function actionTest()
    {
        $result = [
            'code' => 200,
            'msg' => 'success',
            'data' => 'Hello World',
        ];
        echo $this->toJson($result);
    }

    public function actionView()
    {
        $this->render('site/view', ['msg' => 'This is view']);
    }
}