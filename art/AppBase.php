<?php


namespace art;


class AppBase
{
    /**
     * 当前应用类库命名空间
     * @var string
     */
    protected $namespace = 'app';

    /**
     * 应用根目录
     * @var string
     */
    protected $rootPath = '';

    /**
     * 框架目录
     * @var string
     */
    protected $artPath = '';

    /**
     * 应用目录
     * @var string
     */
    protected $appPath = '';

    protected $appName = '';

    protected $controllerName = '';

    protected $actionName = '';

    /**
     * @var \ReflectionClass
     */
    protected $instance = null;

    public function __construct()
    {
        $this->artPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $this->rootPath = $this->getDefaultRootPath();
        $this->appPath = $this->rootPath . 'app' . DIRECTORY_SEPARATOR;
    }


    /**
     * 获取应用根目录
     * @access protected
     * @return string
     */
    protected function getDefaultRootPath(): string
    {
        return dirname($this->artPath, 4) . DIRECTORY_SEPARATOR;
    }

}