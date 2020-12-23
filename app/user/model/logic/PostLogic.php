<?php


namespace app\user\model\logic;


use art\request\Request;

class PostLogic
{
    public static function posts()
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

    public static function postBack()
    {

    }
}