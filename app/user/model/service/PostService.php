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
        $result = $medoo->select('order',
            [
                'game',
                'issue'=>Medoo::raw("LEFT(issue,8)"),
                'orderNo',
                'reset_code',
                'play_method',
                'play_code',
                'play_site',
                'quantity',
                'single_quantity',
                'lottery_code',
                'line',
                'whether_hit',
                'began_quantity',
                'after_quantity',
                'end_quantity'=>Medoo::raw('after_quantity+loc_quantity_ret+fly_quantity_ret'),
                'status',
            ],
            [
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