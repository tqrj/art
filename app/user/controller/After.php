<?php


namespace app\user\controller;




use app\user\model\logic\AfterLogic;
use app\user\model\service\AfterService;

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
        AfterService::cancel($params);
    }

}