<?php


namespace app;


use art\context\Context;
use Swoole\Http\Request;
use Swoole\Http\Response;

class BaseController
{
    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @var Response
     */
    protected $response = null;

    public function __construct()
    {
        $this->request = Context::get('request');;
        $this->response = Context::get('response');
    }
}