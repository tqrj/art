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
        $result = $medoo->select('domain',['id','domain','status','create_time'],['status'=>1,'ORDER'=>['id'=>'DESC']]);
        array_walk($result,function (&$value,$key) use ($agentInfo)
        {
            $value['domain'].= $agentInfo['code'];
        });
        return $result;
    }
}