<?php


namespace art;


use art\context\Context;
use art\exception\HttpException;
use art\helper\Str;
use art\middleware\Middleware;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;

class WsApp extends BaseApp
{
    private function __construct()
    {

    }

    private function __clone()
    {

    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    public static function init(Request $request,Response $ws,Frame $frame)
    {
        self::initBase();
        Context::put('request',$request);
        Context::put('response',$ws);
        Context::put('frame',$frame);
        $frame->data = json_decode($frame->data,true);
        if (!is_array($frame->data)){
            throw new HttpException(404, 'App not find',[],'',0,$ws->artWsId);
        }
        $pathInfo = $frame->data['artPath'];
        $pathInfo = explode('/', $pathInfo);
        if (count($pathInfo) < 4) {
            throw new HttpException(404, 'App not find',[],'',0,$ws->artWsId);
        }
        // 获取应用名
        $app = strip_tags($pathInfo[1]);
        self::putAppName(Str::camel($app));
        // 获取控制器名
        $controller = strip_tags($pathInfo[2]);
        self::putControllerName(Str::studly($controller));
        // 获取方法名
        $action = strip_tags($pathInfo[3]);
        self::putActionName(Str::camel($action));
    }



    public static function run(Request $request,Response $ws,Frame $frame)
    {
        $instance = self::controller('isWs');
        if (!is_callable([$instance, self::getActionName()])) {
            throw new HttpException(404, 'method not exists:' . get_class($instance) . '->' . self::getActionName() . '()',[],'',0,$ws->artWsId);
        }
        try {
            $reflect = new \ReflectionMethod($instance, self::getActionName());
            // 严格获取当前操作方法名
            self::putActionName($reflect->getName());
        } catch (\ReflectionException $e) {
            throw new HttpException(404, 'method not exists:' . get_class($instance) . '->' . self::getActionName() . '()',[],'',0,$ws->artWsId);
        }
        Middleware::Auth(self::getAppName());
        $reflect->invokeArgs($instance,[]);
        //$this->response->end('qwq');
    }

    public static function end()
    {
        Context::delete();
    }

}