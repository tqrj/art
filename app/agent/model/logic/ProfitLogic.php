<?php


namespace app\agent\model\logic;

use art\request\Request;

/**
 * Class Profit
 * @package app\agent\controller
 */
class ProfitLogic
{
    public static function applyList()
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
        ]);
        art_validate($params,[
            'limit'=>'require|between:10,50',
            'page'=>'require|between:0,999',
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }

    public static function payApplyList()
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
        ]);
        art_validate($params,[
            'limit'=>'require|between:10,50',
            'page'=>'require|between:0,999',
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }

    public static function reBackApplyList()
    {
        $params = Request::only([
            'page'=>0,
            'limit'=>10,
        ]);
        art_validate($params,[
            'limit'=>'require|between:10,50',
            'page'=>'require|between:0,999',
        ]);
        $params['page'] *= $params['limit'];
        return $params;
    }

    /**
     * @return array
     */
    public static function passApply()
    {
        $params = Request::only([
            'id',
        ]);
        art_validate($params,[
            'id'=>'require'
        ]);
        return $params;
    }

    public static function rejectApply()
    {
        $params = Request::only([
            'id',
        ]);
        art_validate($params,[
            'id'=>'require'
        ]);
        return $params;
    }

    public static function profitList()
    {

    }

    public static function profitDetailList()
    {
        $params = Request::only([
            'timeStart',
            'timeEnd',
        ]);
        art_validate($params,[
            'timeStart'=>'require',
            'timeEnd'=>'require',
        ]);
        return $params;
    }

}