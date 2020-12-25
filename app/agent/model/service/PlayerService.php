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
        $map['LIMIT'] = [$params['page'], $params['limit']];
        $map['ORDER'] = ['q.id' => 'DESC'];
        $map['u.status'] = [1, 0];
        $map['q.status'] = [1, 0];
        $map['q.agent_id'] = $agentInfo['id'];
        $medoo = new Medoo();
        return $medoo->select('user(u)',
            ['[><]user_quantity(q)' => ['u.id' => 'user_id']],
            ['u.id', 'u.nickname','u.token', 'q.quantity', 'u.group_id','u.headimgurl', 'u.status', 'q.create_time'],
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
            ['[><]user_quantity(q)' => ['u.id' => 'user_id']],
            ['u.id', 'u.nickname','u.token','u.headimgurl', 'q.quantity', 'u.group_id', 'q.status'],
            $map);
        if (!$userInfo) {
            art_assign(202, '用户ID错误');
        }
//        $agentInfo = Context::get('authInfo');
//        $map = [
//            'agent_id' => $agentInfo['id'],
//            'user_id' => $userInfo['id']
//        ];
        //$userInfo['order'] = $medoo->select('order', '*', $map);
//        $map['ORDER'] = ['status' => 'DESC'];
//        $map['type'] = 1;
//        $userInfo['points_pay'] = $medoo->select('points', '*', $map);
//        $map['type'] = -1;
//        $userInfo['points_reject'] = $medoo->select('points', '*', $map);
        return $userInfo;
    }

    public static function orderList($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $map = [
            'agent_id' => $agentInfo['id'],
            'user_id' => $params['playerId'],
            'LIMIT'=> [$params['page'], $params['limit']],
            'ORDER'=>['id'=>'DESC']
        ];
        return $medoo->select('order', '*', $map);
    }

    public static function quantityLog($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $map = [
            'agent_id' => $agentInfo['id'],
            'user_id' => $params['playerId'],
            'LIMIT'=> [$params['page'], $params['limit']],
            'ORDER'=>['id'=>'DESC']
        ];
        return $medoo->select('quantity_log', ['mark','over','create_time','quantity'], $map);
    }

    public static function pointsPay($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $map = [
            'agent_id' => $agentInfo['id'],
            'user_id' => $params['playerId'],
            'LIMIT'=> [$params['page'], $params['limit']],
            'ORDER'=>['id' => 'DESC']
        ];
        return $medoo->select('points', '*', $map);
    }

    public static function pointsReject($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $map = [
            'agent_id' => $agentInfo['id'],
            'user_id' => $params['playerId'],
            'type'=>-1,
            'LIMIT'=> [$params['page'], $params['limit']],
            'ORDER'=>['status' => 'DESC']
        ];
        return $medoo->select('points', '*', $map);
    }

    public static function change($params)
    {
        if (count($params) < 2) {
            art_assign(202, '输入错误');
        }
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $map = [];
        $map['u.status'] = [1, 0];
        $map['q.status'] = [1, 0];
        $map['u.id'] = $params['playerId'];
        $map['q.agent_id'] = $agentInfo['id'];
        $userInfo = $medoo->get('user(u)',
            ['[><]user_quantity(q)' => ['u.id' => 'user_id']],
            ['u.id', 'salt'],
            $map);
        if (!$userInfo) {
            art_assign(202, '用户信息错误');
        }
        if (empty($userInfo['salt'])) {
            $params['salt'] = art_set_salt();
            $userInfo['salt'] = $params['salt'];
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
        $pdoDoc = $medoo->update('user_quantity', ['quantity' => $params['score']], $map);
        if (!$pdoDoc->rowCount()) {
            art_assign(202, '更新数据失败');
        }
        QuantityLogService::push($map['user_id'], $map['agent_id'], $params['score'], $params['score'],'主动修改');
        return $medoo->get('user_quantity', ['quantity'], $map);
    }

    public static function del($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $pdoDoc = $medoo->update('user_quantity', ['status' => -1], ['user_id' => $params['playerId'], 'agent_id' => $agentInfo['id']]);
        if (!$pdoDoc->rowCount()) {
            art_assign(202, '删除失败');
        }
        return [];
    }

}