<?php


namespace app\user\controller;

use app\user\model\logic\WsLogic;
use app\user\model\service\WsService;

/**
 * Class Ws
 * @package app\user\controller
 */
class Ws
{
    private $isWs = true;

    public function joinGroup()
    {
        (new WsService())->joinGroup();
    }

    public function push()
    {
        $params = WsLogic::push();
        (new WsService())->push($params);
    }
}