> 本次工作主要是把`index.php`直接操作路由的方式改为`new application()->run()`的方式.

##### Application的处理
1. 实现Application基类
    1. 在根目录创建`base`文件夹.在`base`文件夹创建Application类,内容如下:
    ```
    <?php


    namespace common\base;
    
    use Exception;
    
    
    abstract class Application
    {
        public $controllerNamespace = "common\\controllers";
    
        public function run()
        {
            try {
                return $this->handleRequest();
            } catch (Exception $e) {
                return $e;
            }
        }
    
        abstract public function handleRequest();
    }
    ```
    2. Application类被创建出来的期待是:`new Application()->run()`;
    3. 这里的Application类是抽象类,创建其他的application需要继承此类.
2. 实现WebApplication类
    1. 在根目录新建`web`文件夹
    2. 在web文件夹中新建`WebApplication`类,内容如下:
    ```
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
    ```
    3. 此处增加了一些很简单的默认路由处理.所以要在`controllers`目录下创建`SiteController`类.内容同之前的`TestController`
    4. 此时的路由处理还是很粗糙的.莫急.我们后续可以去`composer`上参考一下别的路由组件的处理.对其封装抽象出来.不用写的这么僵硬.这里暂时不处理.
3. 优化`index.php`
    1. 鉴于以上的改变.那么`index.php`可以修改成如下:
    ```
    <?php
    require_once __DIR__."/../vendor/autoload.php";
    
    $application = new \common\web\WebApplication();
    $application->run();
    ```
