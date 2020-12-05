<?php


namespace art\middleware;


use art\exception\ClassNotFoundException;
use art\exception\HttpException;
use Swoole\Http\Response;

class Middleware
{


    protected static function parseClass($appName): string
    {

        return 'app' . '\\' . $appName . '\\' . 'middleware' . '\\' . 'Auth';
    }

    protected static function controller($appName)
    {
        //$class = $this->parseClass('controller', );
        $class= self::parseClass($appName);
        if (class_exists($class)) {
            try {
                $reflect = new \ReflectionClass($class);
                $object = $reflect->newInstance();
//                $object = $reflect->newInstance([$request,$response]);
            } catch (\ReflectionException $e) {
                throw new ClassNotFoundException('auth class not exists: ' . $class, $class, $e);
            }
        } else {
            return null;
        }
        return $object;
    }

    public static function Auth($appName)
    {
        $instance = self::controller($appName);
        if (is_null($instance)){
            return;
        }
        if (!is_callable([$instance, 'hand'])) {
            throw new HttpException(404, 'auth method not exists:' . get_class($instance) . '->hand()');
        }
        try {
            $reflect = new \ReflectionMethod($instance, 'hand');
        } catch (\ReflectionException $e) {
            throw new HttpException(404, 'auth method not exists:' . get_class($instance) . '->hand()');
        }

        $reflect->invokeArgs($instance,[]);
        //$this->response->end('qwq');
    }

}