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


}