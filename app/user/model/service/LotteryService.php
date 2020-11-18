<?php


namespace app\user\model\service;


use app\traits\Lottery;

/**
 * Class Lottery 开奖信息
 * @package app\agent\controller
 */
class LotteryService
{
    public static function old()
    {
        $result = Lottery::getCode(Lottery::LOTTERY_TYPE_OLD);
        if (!$result){
            art_assign(202,'查询失败');
        }
        return $result;
    }

    public static function new()
    {
        $result = Lottery::getCode(Lottery::LOTTERY_TYPE_now);
        if (!$result){
            art_assign(202,'查询失败');
        }
        return $result;
    }

    public static function check($params)
    {
        $result = Lottery::getCode(Lottery::LOTTERY_TYPE_check,$params['issue']);
        if (!$result){
            art_assign(202,'查询失败');
        }
        return ['issue'=>$result];
    }
}