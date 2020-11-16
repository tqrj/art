<?php


namespace app\agent\controller;


use app\agent\model\logic\LotteryLogic;
use app\agent\model\service\LotteryService;

/**
 * Class Lottery 开奖信息
 * @package app\agent\controller
 */
class Lottery
{
    private $isHttp = true;

    public function old()
    {
        LotteryLogic::old();
        $result = LotteryService::old();
        art_assign(200,'success',$result);
    }

    public function new()
    {
        LotteryLogic::new();
        $result = LotteryService::new();
        art_assign(200,'success',$result);
    }

    public function check()
    {
        $params = LotteryLogic::check();
        $result  = LotteryService::check($params);
        art_assign(20,'success',$result);
    }
}