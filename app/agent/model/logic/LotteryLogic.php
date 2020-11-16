<?php


namespace app\agent\model\logic;


use art\request\Request;

/**
 * Class Lottery 开奖信息
 * @package app\agent\controller
 */
class LotteryLogic
{
    public static function old()
    {

    }

    public static function new()
    {

    }

    public static function check()
    {
        $params = Request::only(['issue']);
        art_validate($params,[
            'issue'=>'require|length:5,20'
        ]);
        return $params;
    }
}