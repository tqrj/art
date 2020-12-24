<?php


namespace app\agent\model\service;


use art\db\Medoo;

/**
 * Class QuantityLogService 积分变动记录
 * @package app\user\model\service
 */
class QuantityLogService
{
    public static function push($userId,$agentId,$quantity,$over,string $mark)
    {
        $medoo = new Medoo();
        $data['user_id'] = (int)$userId;
        $data['agent_id'] = (int)$agentId;
        $data['quantity'] = (float)$quantity;
        $data['over'] = (float)$over;
        $data['mark'] = (string)$mark;
        $data['create_time'] = art_d();
        $data['update_time'] = $data['create_time'];
        $pdoDoc = $medoo->insert('quantity_log',$data);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'积分变动记录生成错误');
        }
        return true;
    }


}