<?php


namespace app\agent\controller;


use app\agent\model\logic\AgentLogic;
use app\agent\model\service\AgentService;

class Agent
{
    public function login()
    {
        $params = AgentLogic::login();
        $result = AgentService::login($params);
        art_assign(200,'success',$result);
    }

    public function sign()
    {
        $params = AgentLogic::sign();
        $result = AgentService::sign($params);
        art_assign(200,'success',$result);
    }

    public function sendCode()
    {
        AgentLogic::sendCode();
        $result = AgentService::sendCode();
        art_assign(200,'success',$result);
    }

    public function userInfo()
    {
        $params = AgentLogic::userInfo();
        $result = AgentService::userInfo($params);
        art_assign(200,'success',$result);
    }

    public function change()
    {
        $params = AgentLogic::change();
        $result = AgentService::change($params);
        art_assign(200,'success',$result);
    }

}