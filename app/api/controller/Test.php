<?php


namespace app\api\controller;


use app\BaseController;
use art\request\Request;

class Test extends BaseController
{
    public function hello()
    {
        $params = Request::only(['pp','cc']);
        art_validate($params,[
            'cc'=>'require|mobile'
        ]);
        $this->response->end(json_encode($params));

    }
}