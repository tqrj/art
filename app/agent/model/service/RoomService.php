<?php


namespace app\agent\model\service;

use art\context\Context;
use art\db\Medoo;
use Swoole\Timer;

/**
 * Class Room 房间设置
 * @package app\agent\controller
 */
class RoomService
{

    /**
     * @return array
     * @todo 房间定时开奖算账害没有写
     */
    public static function switchOpen()
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $roomInfo = $medoo->get('room', ['id', 'status', 'timerID'], ['agent_id' => $agentInfo['id']]);
        if (!$roomInfo) {
            art_assign(202, '房间数据异常');
        }
        if ($roomInfo['timerID'] && $roomInfo['status']) {
            art_assign(202, '房间已经开启');
        }
        if (!$roomInfo['status']) {
            $bool = $medoo->update('room', ['status' => 1], ['agent_id' => $agentInfo['id']])->rowCount();
            if (!$bool) {
                art_assign(202, '更新数据出错');
            }
        }
        if ($roomInfo['timerID']) {
            return [];
        }
        //开启房间定时器
        Timer::tick(2000, function (int $timer_id, $agent_info,Medoo $medoo) {
            $roomInfo = $medoo->get('room', ['id', 'status', 'timerID'], ['agent_id' => $agent_info['id']]);
            if (!$roomInfo) {
                echo '房间定时器被清除了1';
                Timer::clear($timer_id);
                return;
            } else if ($roomInfo['status'] == 0) {
                echo '房间定时器被清除了2';
                $medoo->update('room',['timerID'=>0],['id'=>$roomInfo['id']]);
                Timer::clear($timer_id);
                return;
            }


        },$agentInfo, $medoo);


        return [];
    }

    public static function switchClose()
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $roomInfo = $medoo->get('room', ['id', 'status', 'timerID'], ['agent_id' => $agentInfo['id']]);
        if (!$roomInfo) {
            art_assign(202, '房间数据异常');
        }
        if (!$roomInfo['status']) {
            art_assign(202, '房间已经被关闭');
        }
        if ($roomInfo['status']) {
            $bool = $medoo->update('room', ['status' => 0], ['agent_id' => $agentInfo['id']])->rowCount();
            if (!$bool) {
                art_assign(202, '更新数据出错');
            }
        }
        return [];
    }

    public static function setRule($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $roomInfo = $medoo->get('room', ['id', 'status'], ['agent_id' => $agentInfo['id']]);
        if (!$roomInfo) {
            art_assign(202, '房间数据异常');
        }
        $pdoDoc = $medoo->update('room_rule',$params,['agent_id'=>$agentInfo['id'],'class'=>$params['class']]);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'更新失败');
        }
        return [];
    }

    public static function change($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $roomInfo = $medoo->get('room', ['id', 'status'], ['agent_id' => $agentInfo['id']]);
        if (!$roomInfo) {
            art_assign(202, '房间数据异常');
        }
        $pdoDoc = $medoo->update('room',$params,['id'=>$roomInfo['id']]);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'更新失败');
        }
        return [];
    }

    public static function info()
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $roomInfo = $medoo->get('room', '*', ['agent_id' => $agentInfo['id']]);
        if (!$roomInfo) {
            art_assign(202, '房间数据异常');
        }
        $roomInfo['rule'] = $medoo->select('room_rule','*',['agent_id'=>$agentInfo['id']]);
        return $roomInfo;
    }

    /**
     * 创建代理的时候附带初始化所属房间
     * @param $agentId
     */
    public static function create($agentId)
    {
        $medoo = new Medoo();
        $bool = $medoo->has('room',['agent_id'=>$agentId]);
        if ($bool){
            art_assign(202,'创建房间失败');
        }
        $roomData['agent_id'] = $agentId;
        $roomData['status'] = 0;
        $roomData['timerID'] = 0;
        $roomData['create_time'] = art_d();
        $roomData['update_time'] = art_d();
        $pdoDoc = $medoo->insert('room',$roomData);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'创建房间信息失败');
        }
        $roomRule['agent_id'] = $agentId;
        $roomRule['line'] = 97;
        $roomRule['max'] =1000;
        $roomRule['eat'] = 0;
        $roomRule['eatNum'] = 20;
        $roomRule['decimal'] = 0;
        $roomRule['status'] = 1;
        $roomRuleAll = [];
        for ($i = 1; $i <= 5; $i++){
            $roomRule['class'] = $i;
            $roomRuleAll[] =  $roomRule;
        }
        $pdoDoc = $medoo->insert('room_rule',$roomRuleAll);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'创建房间规则信息失败');
        }
        return [];
    }
}