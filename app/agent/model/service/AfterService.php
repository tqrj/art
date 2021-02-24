<?php


namespace app\agent\model\service;


use art\context\Context;
use art\db\Medoo;

class AfterService
{
    public static function list($params)
    {
        $map = [];
        $medoo = new Medoo();
        $agentInfo = Context::get('authInfo');
        $map['agent_id'] = $agentInfo['id'];
        $map['LIMIT'] = [$params['page'], $params['limit']];
        $map['ORDER'] = ['a.id' => 'DESC','a.status'=>'DESC'];
        if (!empty($params['userId'])){
            $map['user_id'] = $params['userId'];
        }
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

    public static function cancel($params): array
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $pdoDoc = $medoo->update('after',['status'=>0],['agent_id'=>$agentInfo['id'],'id'=>$params['afterId']]);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'取消失败');
        }
        return [];
    }


    public static function clear(): array
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $pdoDoc =  $medoo->delete('after',['agent_id'=>$agentInfo['id'],'status'=>0]);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'清空失败');
        }
        return [];
    }
}