<?php


namespace app;


use art\context\Context;
use Swoole\Database\PDOPool;
use Swoole\Http\Request;
use Swoole\Http\Response;

class BaseController
{
    /**
     * @var Request|null
     */
    protected $request = null;

    /**
     * @var Response|null
     */
    protected $response = null;

    public function __construct()
    {
        $this->request = Context::get('request');;
        $this->response = Context::get('response');
    }
}