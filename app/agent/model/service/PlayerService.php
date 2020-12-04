<?php


namespace app\agent\model\service;

use art\context\Context;
use art\db\Medoo;

/**
 * Class Player 玩家管理
 * @package app\agent\controller
 */
class PlayerService
{

    public static function list($params)
    {
        $map = [];
        $agentInfo = Context::get('authInfo');
        if (!empty($params['keyWord'])) {
            $map['u.nickname[~]'] = $params['keyWord'] . '%';
        }
        $map['LIMIT'] = [0, $params['limit']];
        $map['ORDER'] = ['q.id' => 'DESC'];
        $map['u.status'] = [1, 0];
        $map['q.status'] = [1, 0];
        $map['q.agent_id'] = $agentInfo['id'];
        $medoo = new Medoo();
        return $medoo->select('user(u)',
            ['[><]user_quantity(q)'=>['u.id'=>'user_id']],
            ['u.id', 'u.nickname', 'q.quantity', 'u.group_id', 'u.status','q.create_time'],
            $map);
    }

    public static function info($params)
    {
        $map = [];
        $agentInfo = Context::get('authInfo');
        $map['u.id'] = $params['playerId'];
        $map['u.status'] = [1, 0];
        $map['q.status'] = [1, 0];
        $map['q.agent_id'] = $agentInfo['id'];
        $medoo = new Medoo();
        $userInfo = $medoo->get('user(u)',
            ['[><]user_quantity(q)'=>['u.id'=>'user_id']],
            ['u.id', 'u.nickname', 'q.quantity', 'u.group_id', 'q.status'],
            $map);
        if (!$userInfo) {
            art_assign(202, '用户ID错误');
        }
        $agentInfo = Context::get('authInfo');
        $map = [
            'agent_id' => $agentInfo['id'],
            'user_id' => $userInfo['id']
        ];
        $userInfo['order'] = $medoo->select('order', '*', $map);
        $userInfo['quantityLog'] = $medoo->select('quantity_log', '*', $map);
        return $userInfo;
    }

    public static function change($params)
    {
        if (count($params)<2){
            art_assign(202,'输入错误');
        }
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $map = [];
        $map['u.status'] = [1, 0];
        $map['q.status'] = [1, 0];
        $map['u.id'] = $params['playerId'];
        $map['q.agent_id'] = $agentInfo['id'];
        $userInfo = $medoo->get('user(u)',
            ['[><]user_quantity(q)'=>['u.id'=>'user_id']],
            ['u.id','salt'],
            $map);
        if (!$userInfo) {
            art_assign(202, '用户信息错误');
        }
        if (!empty($params['pass'])) {
            $params['pass'] = art_set_password($params['pass'], $userInfo['salt']);
        } elseif (!empty($params['pass_sec'])) {
            $params['pass_sec'] = art_set_password($params['pass_sec'], $userInfo['salt']);
        }
        unset($params['playerId']);
        $result = $medoo->update('user', $params, ['id' => $userInfo['id']]);
        if (!$result->rowCount()) {
            art_assign(202, '更新失败');
        }
        return [];
    }

    public static function score($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $map['agent_id'] = $agentInfo['id'];
        $map['user_id'] = $params['playerId'];
        QuantityLogService::push($map['user_id'],$map['agent_id'],$params['score'],'手动更改了玩家分数');
        $pdoDoc = $medoo->update('user_quantity',['quantity'=>$params['score']],$map);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'更新数据失败');
        }
        return $medoo->get('user_quantity',['quantity'],$map);
    }

    public static function del($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $pdoDoc = $medoo->update('user_quantity',['status'=>-1],['user_id'=>$params['playerId'],'agent_id'=>$agentInfo['id']]);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'删除失败');
        }
        return [];
    }

}