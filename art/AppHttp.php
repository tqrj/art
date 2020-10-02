<?php


namespace art;


use art\exception\ClassNotFoundException;
use art\exception\HttpException;
use art\helper\Str;
use Swoole\Http\Request;
use Swoole\Http\Response;


class AppHttp extends AppBase
{
    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @var Response
     */
    protected $response = null;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct();
        $this->request = $request;
        $this->response = $response;
    }

    protected function init()
    {
        $pathInfo = $this->request->server['request_uri'];
        $pathInfo = explode('/', $pathInfo);
        if (count($pathInfo) < 3) {
            throw new HttpException(404, 'App not find');
        }
        // 获取应用名
        $app = strip_tags($pathInfo[1]);
        $this->appName = Str::camel($app);
        // 获取控制器名
        $controller = strip_tags($pathInfo[2]);
        $this->controllerName = Str::studly($controller);
        // 获取方法名
        $action = strip_tags($pathInfo[3]);
        $this->actionName = Str::camel($action);


    }

    protected function controller():object
    {
        //$class = $this->parseClass('controller', );
        $class='app'.'\\'.$this->appName.'\\'.'controller'.'\\'.$this->controllerName;
        if (class_exists($class)) {
            try {
                $reflect = new \ReflectionClass($class);
                $object = $reflect->newInstanceArgs([$this->request,$this->response]);
            } catch (\ReflectionException $e) {
                throw new ClassNotFoundException('class not exists: ' . $class, $class, $e);
            }
        } else {
            throw new ClassNotFoundException('class not exists: ' . $class);
        }
        return $object;
    }

    /**
     * 解析应用类的类名
     * @access public
     * @param string $layer 层名 controller model ...
     * @param string $name 类名
     * @return string
     */
    public function parseClass(string $layer, string $name): string
    {
        $name = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = Str::studly(array_pop($array));
        $path = $array ? implode('\\', $array) . '\\' : '';

        return $this->namespace . '\\' . $layer . '\\' . $path . $class;
    }

    /**
     *
     */
    public function run()
    {
        $this->init();
        $instance = $this->controller();
        if (!is_callable([$instance, $this->actionName])) {
            throw new HttpException(404, 'method not exists:' . get_class($instance) . '->' . $this->actionName . '()');
        }
        try {
            $reflect = new \ReflectionMethod($instance, $this->actionName);
            // 严格获取当前操作方法名
            $this->actionName = $reflect->getName();
        } catch (\ReflectionException $e) {
            throw new HttpException(404, 'method not exists:' . get_class($instance) . '->' . $this->actionName . '()');
        }
        $reflect->invokeArgs($instance,[]);
        //$this->response->end('qwq');
    }

}