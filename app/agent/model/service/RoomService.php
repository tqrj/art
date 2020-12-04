<?php


namespace app\agent\model\service;

use art\context\Context;
use art\db\Medoo;
use art\ws\ArtWs;
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
        Timer::tick(5000, function (int $timer_id, $agent_info,Medoo $medoo) {
            //这里不要每次都去查数据库 可以redis 记录一下在等待开奖的期号，然后每次去查服务的当前期号如果不是当前期号了就查该期号的结果
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
            //如果是的话就是第一次被开启
            //第一次被开启，需要先结清之前的账单，然后 获取即将开奖的号码，以及马上需要开奖的时间 放入table
            //然后定时检查该害差多久开始封盘，以及是不是已经开奖，已经开奖了就马上算账！
            if (!$roomInfo['timerID']){
                $medoo->update('room',['timerID'=>$timer_id],['id'=>$roomInfo['id']]);
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