<?php


namespace app\api\middleware;


use Swoole\Http\Request;
use Swoole\Http\Response;

class Auth
{
    public $app = null;

    public $controller = null;

    public $action = null;

    public function __construct($app,$controller,$action)
    {
        $this->app = $app;
        $this->controller = $controller;
        $this->action = $action;
    }


    public function handle(Request $request, Response $response):bool
    {


        return true;
    }
}