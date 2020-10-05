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
        art_assign(200,json_encode($params));

    }
}