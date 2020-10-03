<?php


namespace art;


use art\context\Context;

class BaseApp
{


    protected static function initBase()
    {
        $artPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        Context::put('artPath', $artPath);
        $rootPath = self::getDefaultRootPath();
        Context::put('rootPath', $rootPath);
        $appPath = self::getRootPath() . 'app' . DIRECTORY_SEPARATOR;
        Context::put('appPath', $appPath);
    }

    protected static function getArtPath():string
    {
        return Context::get('artPath');
    }

    protected static function getRootPath():string
    {
        return Context::get('rootPath');
    }

    protected static function getAppPath():string
    {
        return Context::get('appPath');
    }

    protected static function putNamespace($namespace)
    {
        Context::put('namespace',$namespace);
    }

    protected static function getNamespace()
    {
       return Context::get('namespace');
    }

    protected static function putAppName($appName)
    {
        Context::put('appName',$appName);
    }

    protected static function getAppName()
    {
       return Context::get('appName');
    }

    protected static function putControllerName($controllerName)
    {
        Context::put('controllerName',$controllerName);
    }

    protected static function getControllerName()
    {
        return Context::get('controllerName');
    }

    protected static function putActionName($actionName)
    {
        Context::put('actionName',$actionName);
    }

    protected static function getActionName()
    {
       return Context::get('actionName');
    }

    /**
     * 获取应用根目录
     * @access protected
     * @return string
     */
    protected static function getDefaultRootPath(): string
    {
        return dirname(self::getArtPath(), 4) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    protected static function parseClass(): string
    {

        return 'app' . '\\' . self::getAppName() . '\\' . 'controller' . '\\' . self::getControllerName();
    }

}