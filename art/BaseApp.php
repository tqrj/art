<?php


namespace art;


use art\context\Context;

class BaseApp
{


    public function __construct()
    {
        $artPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        Context::put('artPath', $artPath);
        $rootPath = $this->getDefaultRootPath();
        Context::put('rootPath', $rootPath);
        $appPath = $this->getRootPath() . 'app' . DIRECTORY_SEPARATOR;
        Context::put('appPath', $appPath);
    }

    protected function getArtPath():string
    {
        return Context::get('artPath');
    }

    protected function getRootPath():string
    {
        return Context::get('rootPath');
    }

    protected function getAppPath():string
    {
        return Context::get('appPath');
    }

    protected function putNamespace($namespace)
    {
        Context::put('namespace',$namespace);
    }

    protected function getNamespace()
    {
       return Context::get('namespace');
    }

    protected function putAppName($appName)
    {
        Context::put('appName',$appName);
    }

    protected function getAppName()
    {
       return Context::get('appName');
    }

    protected function putControllerName($controllerName)
    {
        Context::put('controllerName',$controllerName);
    }

    protected function getControllerName()
    {
        return Context::get('controllerName');
    }

    protected function putActionName($actionName)
    {
        Context::put('actionName',$actionName);
    }

    protected function getActionName()
    {
       return Context::get('actionName');
    }

    /**
     * 获取应用根目录
     * @access protected
     * @return string
     */
    protected function getDefaultRootPath(): string
    {
        return dirname($this->getArtPath(), 4) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    protected function parseClass(): string
    {

        return 'app' . '\\' . $this->getAppName() . '\\' . 'controller' . '\\' . $this->getControllerName();
    }

}