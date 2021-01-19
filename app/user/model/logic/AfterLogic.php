<?php


namespace app\user\model\logic;


use art\request\Request;

class AfterLogic
{
    public static function list()
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
            'userId'
        ]);
        art_validate($params,[
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }

    public static function cancel()
    {
        $params = Request::only(['afterId']);
        art_validate($params,[
            'afterId'=>'require'
        ]);
        return $params;
    }

    public static function info()
    {
        $params = Request::only(['afterId']);
        art_validate($params,[
            'afterId'=>'require'
        ]);
        return $params;
    }
}