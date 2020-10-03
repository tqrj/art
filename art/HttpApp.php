<?php


namespace art;


use art\context\Context;
use art\exception\ClassNotFoundException;
use art\exception\HttpException;
use art\helper\Str;
use Swoole\Http\Request;
use Swoole\Http\Response;


class HttpApp extends BaseApp
{
    public function init(Request $request,Response $response)
    {
        parent::__construct();
        Context::put('request',$request);
        Context::put('response',$response);
        $pathInfo = $request->server['request_uri'];
        $pathInfo = explode('/', $pathInfo);
        if (count($pathInfo) < 4) {
            throw new HttpException(404, 'App not find');
        }
        // 获取应用名
        $app = strip_tags($pathInfo[1]);
        $this->putAppName(Str::camel($app));
        // 获取控制器名
        $controller = strip_tags($pathInfo[2]);
        $this->putControllerName(Str::studly($controller));
        // 获取方法名
        $action = strip_tags($pathInfo[3]);
        $this->putActionName(Str::camel($action));


    }

    private function controller():object
    {
        //$class = $this->parseClass('controller', );
        $class= $this->parseClass();
        if (class_exists($class)) {
            try {
                $reflect = new \ReflectionClass($class);
                //$object = $reflect->newInstanceArgs([$request,$response]);
            } catch (\ReflectionException $e) {
                throw new ClassNotFoundException('class not exists: ' . $class, $class, $e);
            }
        } else {
            throw new ClassNotFoundException('class not exists: ' . $class);
        }
        return $reflect;
    }

    public function run()
    {
        $instance = $this->controller();
        if (!is_callable([$instance, $this->getActionName()])) {
            throw new HttpException(404, 'method not exists:' . get_class($instance) . '->' . $this->actionName . '()');
        }
        try {
            $reflect = new \ReflectionMethod($instance, $this->getActionName());
            // 严格获取当前操作方法名
            $this->putActionName($reflect->getName());
        } catch (\ReflectionException $e) {
            throw new HttpException(404, 'method not exists:' . get_class($instance) . '->' . $this->getActionName() . '()');
        }
        $reflect->invokeArgs($instance,[]);
        //$this->response->end('qwq');
    }

}