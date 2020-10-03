<?php


namespace app\api\controller;


use app\BaseController;

class Test extends BaseController
{
    public function hello()
    {

        $this->response->end(json_encode(art_verify()));

    }
}