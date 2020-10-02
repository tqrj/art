<?php


namespace art;


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

    public function __construct(Request $request,Response $response)
    {
        parent::__construct();
        $this->request = $request;
        $this->response = $response;
    }

    public function run()
    {
        $this->response->end('qwq');
    }

}