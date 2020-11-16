<?php


namespace app\agent\controller;

use app\agent\model\logic\RoomLogic;
use app\agent\model\service\RoomService;

/**
 * Class Room 房间设置
 * @package app\agent\controller
 */
class Room
{
    private $isHttp = true;

    public function switchOpen()
    {
        $result = RoomService::switchOpen();
        art_assign(200,'success',$result);
    }

    public function switchClose()
    {
        $result = RoomService::switchClose();
        art_assign(200,'success',$result);
    }

    public function setRule()
    {
        $params = RoomLogic::setRule();
        $result = RoomService::setRule($params);
        art_assign(200,'success',$result);
    }

    public function change()
    {
        $params = RoomLogic::change();
        $result = RoomService::change($params);
        art_assign(200,'success',$result);
    }

    public function info()
    {
        $result = RoomService::info();
        art_assign(200,'success',$result);
    }
}