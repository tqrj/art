<?php


namespace app\api\controller;


use app\BaseController;

class test extends BaseController
{
    public function hello()
    {
        $this->response->end('<h1>hello</h1>');
    }
}