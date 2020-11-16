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
           'page'=>0,
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
        $params = Request::only([
            'playerId',
        ]);
        art_validate($params,[
            'playerId'=>'require|number'
        ]);
        return $params;
    }

    /**
     * 投注列表
     */
    public static function infoPosts()
    {
        $params = Request::only([
            'playerId',
            'page'=>1,
            'limit'=>20
        ]);
        art_validate($params,[
            'playerId'=>'require|number',
            'limit'=>'require|between:10,50',
            'page'=>'require|between:1,999',
        ]);
    }

    /**
     * 上下分列表
     */
    public static function infoScore()
    {
        $params = Request::only([
            'playerId',
            'page'=>1,
            'limit'=>20
        ]);
        art_validate($params,[
            'playerId'=>'require|number',
            'limit'=>'require|between:10,50',
            'page'=>'require|between:1,999',
        ]);
    }

    public static function change()
    {
        $params = Request::only([
            'playerId',
            'nickname',
            'pass',
            'pass_sec',
            'quantity',
            'status'
        ]);
        art_validate($params,[
            'playerId'=>'require|number',
            'nickname|用户名称'=>'length:6,20',
            'pass|密码'=>'length:6,20',
            'pass_sec|二级密码'=>'length:6',
            'status'=>'between:0,1'
        ]);
        return $params;
    }

    public static function del()
    {
        $params = Request::only([
            'playerId',
        ]);
        art_validate($params,[
            'playerId'=>'require|gt:0'
        ]);
        return $params;
    }
}