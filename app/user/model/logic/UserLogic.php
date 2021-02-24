<?php


namespace app\user\model\logic;


use art\request\Request;

class UserLogic
{
    public static function auth(): array
    {
        $params = Request::only(['state','code']);
        art_validate($params,[
            'state'=>'require',
            'code'=>'require'
        ]);
        return $params;
    }

    public static function info(): array
    {
        $params = Request::only(['token','agent_id']);
        art_validate($params,[
            'token'=>'require',
            'agent_id'=>'require'
        ]);
        return $params;
    }

    public static function pay(): array
    {
        $params = Request::only(['quantity']);
        art_validate($params,[
            'quantity'=>'require'
        ]);
        $params['quantity'] = (int)$params['quantity'];
        if ($params['quantity'] <=0 ){
            art_assign(202,'积分错误');
        }
        return $params;
    }

    public static function reBack(): array
    {
        $params = Request::only(['quantity']);
        art_validate($params,[
            'quantity'=>'require'
        ]);
        $params['quantity'] = (int)$params['quantity'];
        if ($params['quantity'] <=0 ){
            art_assign(202,'积分错误');
        }
        return $params;
    }

    public static function payList(): array
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
        ]);
        art_validate($params,[
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }

    public static function reBackList(): array
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
        ]);
        art_validate($params,[
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }


    public static function baseConfig()
    {

    }
}