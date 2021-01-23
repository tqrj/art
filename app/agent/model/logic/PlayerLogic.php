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
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
            'keyword'=>'length:1,12'
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }

    public static function orderList()
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
            'playerId',
            'afterId'
        ]);
        art_validate($params,[
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
            'playerId'=>'require|number',
            'afterId'=>'number'
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }

    public static function quantityLog()
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
            'playerId',
        ]);
        art_validate($params,[
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
            'playerId'=>'require|number'
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }

    public static function pointsPay()
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
            'playerId',
        ]);
        art_validate($params,[
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
            'playerId'=>'require|number'
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }

    public static function pointsReject()
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
            'playerId',
        ]);
        art_validate($params,[
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
            'playerId'=>'require|number'
        ]);
        $params['page'] *= $params['limit'];
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
            'limit'=>'require|between:5,50',
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
            'limit'=>'require|between:5,50',
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
        ]);
        art_validate($params,[
            'playerId'=>'require|number',
            'nickname|用户名称'=>'length:6,20',
            'pass|密码'=>'length:6,20',
            'pass_sec|二级密码'=>'length:6',
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

    public static function score()
    {
        $params = Request::only([
            'playerId',
            'score'
        ]);
        art_validate($params,[
            'playerId'=>'require|gt:0',
            'score'=>'require'
        ]);
        return $params;
    }

    public static function setStatus()
    {
        $params = Request::only([
            'playerId',
            'status'
        ]);
        art_validate($params,[
            'playerId'=>'require|gt:0',
            'status'=>'require|egt:0'
        ]);
        return $params;
    }

    public static function delAllOrder()
    {
        $params = Request::only([
            'playerId',
        ]);
        art_validate($params,[
            'playerId'=>'require|gt:0'
        ]);
        return $params;
    }

    public static function delAllQuantityLog()
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