<?php


namespace app\agent\model\logic;

use art\request\Request;

/**
 * Class Room 房间设置
 * @package app\agent\controller
 */
class RoomLogic
{

    public static function setRule()
    {
        $params = Request::only([
            'class',
            'line',
            'max',
            'eat',
            'eatNum',
            'decimal',
            'status'
        ]);
        art_validate($params,[
            'class'=>'require|between:1,5',
            'line'=>'require|gt:0',
            'max'=>'require|gt:0',
            'eat'=>'require|between:0,1',
            'eatNum'=>'require|egt:0',
            'decimal'=>'require|egt:0',
            'status'=>'require|between:0,1'
        ]);
        return $params;
    }

    public static function change()
    {
        $params = Request::only([
            'closeTime',
            'notice',
            'notice_close',
            'notice_top',
            'site_user',
            'site_pwd',
            'site_code',
            'site_use',
            'whether_water',
            'whether_closeInfo'
        ]);
        art_validate($params,[
            'closeTime'=>'require',
            'notice'=>'require',
            'notice_close'=>'require',
            'notice_top'=>'require',
            'site_use'=>'require|between:0,1',
            'whether_water'=>'require|between:0,1',
            'whether_closeInfo'=>'require|between:0,1'
        ]);
        return $params;
    }
}