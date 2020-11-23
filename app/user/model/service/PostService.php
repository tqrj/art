<?php


namespace app\user\model\service;


use art\context\Context;
use art\db\Medoo;

class PostService
{
    public static function posts($params)
    {
        $userInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $result = $medoo->select('order','*',[
            'agent_id'=>$userInfo['agent_id'],
            'user_id'=>$userInfo['id'],
            'LIMIT'=>[$params['page'],$params['limit']],
            'ORDER'=>['id'=>'DESC']
        ]);
        return $result;
    }

    public static function postBack()
    {

    }
}