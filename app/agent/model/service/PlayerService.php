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
        if (!empty($params['keyWord'])) {
            $map['u.nickname[~]'] = $params['keyWord'] . '%';
        }
        $map['LIMIT'] = [$params['page'], $params['limit']];
        $map['ORDER'] = ['q.id' => 'DESC'];
        $map['u.status'] = [1, 0];
        $map['q.status'] = [1, 0];
        $medoo = new Medoo();
        return $medoo->select('user(u)',
            ['[><]user_quantity(q)'=>['u.id'=>'q.user_id']],
            ['u.id', 'u.nickname', 'q.quantity', 'u.group_id', 'u.status','q.create_time'],
            $map);
    }

    public static function info($params)
    {
        $map = [];
        $map['id'] = $params['playerId'];
        $medoo = new Medoo();
        $userInfo = $medoo->get('user', ['id', 'nickname', 'quantity', 'group_id', 'status'], $map);
        if (!$userInfo) {
            art_assign(202, '用户ID错误');
        }
        $agentInfo = Context::get('authInfo');
        $map = [
            'agent_id' => $agentInfo['id'],
            'user_id' => $userInfo['id']
        ];
        $userInfo['order'] = $medoo->select('order', '*', $map);
        $userInfo['quantityLog'] = $medoo->select('user_quantity', '*', $map);
        return $userInfo;
    }

    public static function change($params)
    {
        $medoo = new Medoo();
        $userInfo = $medoo->get('user', 'id,salt', ['id' => $params['playerId']]);
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

    public static function del($params)
    {
        $medoo = new Medoo();
        $userInfo = $medoo->get('user', 'id,salt', ['id' => $params['playerId']]);
        if (!$userInfo) {
            art_assign(202, '用户信息错误');
        }
        $result = $medoo->update('user', ['status'=>-1], ['id' => $userInfo['id']]);
        if (!$result->rowCount()) {
            art_assign(202, '删除失败');
        }
        return [];
    }

}