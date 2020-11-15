<?php


namespace app\agent\model\logic;

use art\request\Request;

/**
 * Class Player 玩家管理
 * @package app\agent\controller
 */
class PlayerLogic
{

    public static function list()
    {
        $params = Request::only([
           'page'=>1,
           'limit'=>10,
            'keyWord'
        ]);
        art_validate($params,[
            'limit'=>'require|between:10,50',
            'page'=>'require|between:1,999',
            'keyword'=>'length:1,12'
        ]);
        return $params;
    }

    public static function info()
    {

    }

    /**
     * 投注列表
     */
    public static function infoPosts()
    {

    }

    /**
     * 上下分列表
     */
    public static function infoScore()
    {

    }

    public static function change()
    {

    }

    public static function del()
    {

    }
}