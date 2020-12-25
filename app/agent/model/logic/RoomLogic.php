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
            'rules',
        ]);
        art_validate($params,[
            'rules'=>'require',
        ]);
        $params = json_decode($params['rules'],true);
        if (!is_array($params) or count($params) != 5){
            art_assign(202,'数据格式错误');
        }
        array_walk($params,function ($item){
            art_validate($item,[
                'line'=>'require|gt:0',
                'max'=>'require|gt:0',
                'eat'=>'require|between:0,1',
                'eatNum'=>'require|egt:0',
                'decimal'=>'require|egt:0',
                'status'=>'require|between:0,1'
            ]);
        });

//        $params = Request::only([
//            'class',
//            'line',
//            'max',
//            'eat',
//            'eatNum',
//            'decimal',
//            'status'
//        ]);
        return $params;
    }

    public static function change()
    {
        $params = Request::only([
            'title',
            'reTime',
            'closeTime',
            'notice',
            'notice_close',
            'notice_top',
            'whether_water',
            'whether_closeInfo'
        ]);
        art_validate($params,[
            'title'=>'require',
            'reTime'=>'require',
            'closeTime'=>'require',
            'notice'=>'require',
            'notice_close'=>'require',
            'notice_top'=>'require',
            'whether_water'=>'require|between:0,1',
            'whether_closeInfo'=>'require|between:0,1'
        ]);
        return $params;
    }

    public static function changeSite()
    {
        $params = Request::only([
            'site_user',
            'site_pwd',
            'site_code',
            'site_use',
            'site_id',
            'site_domain'=>''
        ]);
        art_validate($params,[
            'site_use'=>'require|between:0,1',
            'site_user'=>'require',
            'site_pwd'=>'require',
            'site_code'=>'require',
            'site_id'=>'require',
            'site_domain'=>'require'
        ]);
        return $params;
    }
}