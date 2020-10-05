<?php


namespace app\api\controller;


use app\BaseController;
use art\request\Request;

class Test extends BaseController
{
    public function hello()
    {
        $params = Request::only(['pp','cc']);
        $message = art_validate($params,[
            'cc'=>'require|mobile'
        ]);
        if ($message !== true){
            art_assign(202,$message);
            return;
        }
        $this->response->end(json_encode($params));

    }
}