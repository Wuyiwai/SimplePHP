### 前言
之前用MVC框架时,经常发现应用中的`Controller`类,经常需要`extends BaseController`,开发过程中,看了一下各大框架中`BaseController`的源码,大致用来做以下几类工作:
1. `action的处理`
```
abstract function beforeAction();
abstract function processAction();
abstract function afterAction();
```
2. `render`的处理,用作渲染视图
3. `createWidget`的处理,用作注册组件
4. `cache`的处理,用作注册缓存
5. `http`的处理,用作处理一些比较特殊的http参数,给开发者提供扩展业务的可能性

### 本次工作
本次工作主要是抽象出一个简单的基类,实现基本的`api`用法和`render`用法
注意: 之后文档所述的创建文件的路径都是基于根目录描述

1. 创建`\base\controller.php`,内容如下:
```
<?php


namespace common\base;


class Controller
{
    public $id;

    public $action;
}
```
本次不打算在Controller基类定义过多实现.因为有打算之后拆分Controller的子类实现
2. 创建`\web\WebController.php`,内容如下:
```
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
```
这里实现`render`和`toJson`方法,处理基本视图和接口响应,这里暂不深入处理,后续可以扩展抽象`render`类和`afterAction`方法
3. 修改`\controller\SiteController.php`
```
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
```
4. 创建`\views\site\view.php`
```
<html>
<head>
    <title>title</title>
    <head>
<body>
<?php echo 'Msg : ' . $msg . '<br>';?>
</body>
</html>
```
5. 访问:
    1. 此时访问 http://localhost/site/test 可以获取接口响应
    2. 此时访问 http://localhost/site/view 可以看到视图
