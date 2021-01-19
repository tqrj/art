<?php


namespace app\user\model\service;


use art\context\Context;
use art\db\Medoo;

class AfterService
{
    public static function list($params)
    {
        $map = [];
        $medoo = new Medoo();
        $authInfo = Context::get('authInfo');
        $map['agent_id'] = $authInfo['agent_id'];
        $map['user_id'] = $authInfo['id'];
        $map['LIMIT'] = [$params['page'], $params['limit']];
        $map['ORDER'] = ['a.id' => 'DESC'];
        return $medoo->select('after(a)', ['[><]user(u)'=>['a.user_id'=>'id']],
            [
                'u.id(user_id)',
                'a.id(after_id)',
                'u.nickname',
                'u.headimgurl',
                'a.message',
                'a.count',
                'a.executeds',
                'a.rate_type',
                'a.rate_count',
                'a.rate',
                'a.halt_profit',
                'a.halt_loss',
                'a.reset_code',
                'a.profit',
                'a.status',
                'a.create_time'
            ],$map);
    }

    public static function cancel($params)
    {
        $authInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $pdoDoc = $medoo->update('after',['status'=>0],['agent_id'=>$authInfo['agent_id'],'user_id'=>$authInfo['id'],'id'=>$params['afterId']]);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'取消失败');
        }
        art_assign(200,'取消成功');
    }
}