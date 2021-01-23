<?php


namespace app\agent\controller;


use app\agent\model\logic\AfterLogic;
use app\agent\model\service\AfterService;

class After
{
    private $isHttp = true;

    public function list()
    {
        $params = AfterLogic::list();
        $result = AfterService::list($params);
        art_assign(200,'success',$result);
    }

    public function cancel()
    {
        $params = AfterLogic::cancel();
        $result = AfterService::cancel($params);
        art_assign(200,'success',$result);
    }


    public function clear()
    {
        art_assign(200,'success',AfterService::clear());
    }

}