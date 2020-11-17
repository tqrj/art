<?php


namespace app\agent\controller;

use app\agent\model\service\PromoteService;

/**
 * Class Promote 推广管理
 * @package app\agent\controller
 * @author 唤雨
 */
class Promote
{
    private $isHttp = true;

    public function list()
    {
        $result = PromoteService::list();
        art_assign(200,'success',$result);
    }

}