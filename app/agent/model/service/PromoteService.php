<?php


namespace app\agent\model\service;

use art\context\Context;
use art\db\Medoo;

/**
 * Class Promote 推广管理
 * @package app\agent\controller
 * @author 唤雨
 */
class PromoteService
{
    public static function list()
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        return $medoo->get('domain',['id','domain','status','create_time'],['status'=>1]);
    }
}