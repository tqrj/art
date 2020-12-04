<?php


namespace app\user\model\logic;


use art\request\Request;

class WsLogic
{
    public static function push()
    {
        $params = Request::only([
            'message',
        ]);
        art_validate($params,[
            'message'=>'require'
        ]);
        return $params;
    }
}