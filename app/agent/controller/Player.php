<?php


namespace app\agent\controller;

use app\agent\model\logic\PlayerLogic;
use app\agent\model\service\PlayerService;

/**
 * Class Player 玩家管理
 * @package app\agent\controller
 */
class Player
{
    private $isHttp = true;

    public function list()
    {
        $params = PlayerLogic::list();
        $result = PlayerService::list($params);
        art_assign(200,'success',$result);
    }

    public function orderList()
    {
        $params = PlayerLogic::orderList();
        $result = PlayerService::orderList($params);
        art_assign(200,'success',$result);
    }

    public function quantityLog()
    {
        $params = PlayerLogic::quantityLog();
        $result = PlayerService::quantityLog($params);
        art_assign(200,'success',$result);
    }

    public function pointsPay()
    {
        $params = PlayerLogic::pointsPay();
        $result = PlayerService::pointsPay($params);
        art_assign(200,'success',$result);
    }

/*    public function pointsReject()
    {
        $params = PlayerLogic::pointsReject();
        $result = PlayerService::pointsReject($params);
        art_assign(200,'success',$result);
    }*/

    public function info()
    {
        $params = PlayerLogic::info();
        $result = PlayerService::info($params);
        art_assign(200,'success',$result);
    }


    public function change()
    {
        $params = PlayerLogic::change();
        $result = PlayerService::change($params);
        art_assign(200,'success',$result);
    }

    public function score()
    {
        $params = PlayerLogic::score();
        $result = PlayerService::score($params);
        art_assign(200,'success',$result);
    }

    public function del()
    {
        $params = PlayerLogic::del();
        $result = PlayerService::del($params);
        art_assign(200,'success',$result);
    }

    public function setStatus()
    {
        $params = PlayerLogic::setStatus();
        $result = PlayerService::setStatus($params);
        art_assign(200,'设置成功',$result);
    }

    public function delAllOrder()
    {
        $params = PlayerLogic::delAllOrder();
        $result = PlayerService::delAllOrder($params);
        art_assign(200,'清空成功',$result);
    }

    public function delAllQuantityLog()
    {
        $params = PlayerLogic::delAllQuantityLog();
        $result = PlayerService::delAllQuantityLog($params);
        art_assign(200,'清空成功',$result);
    }

    public function delAllPoints()
    {
        $params = PlayerLogic::delAllPoints();
        $result = PlayerService::delAllPoints($params);
        art_assign(200,'清空成功',$result);
    }

}