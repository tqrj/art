<?php


namespace app\user\controller;

use app\user\model\logic\UserLogic;
use app\user\model\service\UserService;

/**
 * Class User 用户信息
 * @package app\user\controller
 */
class User
{
    private $isHttp = true;

    /**
     * auth之后应该直接带着参数跳转到前端的
     */
    public function auth()
    {
        $params = UserLogic::auth();
        $result = UserService::auth($params);
        art_assign(200,'success',$result);
    }

    public function info()
    {
        $params = UserLogic::info();
        $result = UserService::info($params);
        art_assign(200,'success',$result);
    }

    public function pay()
    {
        $params = UserLogic::pay();
        $result = UserService::pay($params);
        art_assign(200,'success',$result);
    }

    public function reBack()
    {
        $params = UserLogic::reBack();
        $result = UserService::reBack($params);
        art_assign(200,'success',$result);
    }

    public function payList()
    {
        $params = UserLogic::payList();
        $result = UserService::payList($params);
        art_assign(200,'success',$result);
    }

    public function reBackList()
    {
        $params = UserLogic::reBackList();
        $result = UserService::reBackList($params);
        art_assign(200,'success',$result);
    }


    public function baseConfig()
    {
        $result = UserService::baseConfig();
        art_assign(200,'success',$result);
    }

}