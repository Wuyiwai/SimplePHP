<?php


namespace common\controllers;


use common\cache\FileCache;
use common\cache\RedisCache;
use common\models\CashDetail;
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

    public function actionGetCashInfo()
    {
        $data = CashDetail::findAll([]);
        echo $this->toJson($data);
    }

    public function actionCache()
    {
        $cache = new RedisCache();
        $cache->set('msg', 'test cache msg');
        $msg = $cache->get('msg');
        echo $msg;
    }

    public function actionFileCache()
    {
        $cache = new FileCache();
        $cache->set('test', 'test of fileCache');
        $result = $cache->get('test');
        echo $result;
    }
}