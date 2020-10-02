<?php


namespace app;


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

    public function __construct(Request $request,Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}