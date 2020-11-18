<?php


namespace app\user\controller;


use app\user\model\logic\LotteryLogic;
use app\user\model\service\LotteryService;

/**
 * Class Lottery 开奖信息
 * @package app\user\controller
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