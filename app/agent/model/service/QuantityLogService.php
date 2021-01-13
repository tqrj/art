<?php


namespace app\agent\model\service;


use app\user\model\service\WsService;
use art\db\Medoo;
use art\ws\ArtWs;

/**
 * Class QuantityLogService 积分变动记录
 * @package app\user\model\service
 */
class QuantityLogService
{
    /**
     * @param $userId
     * @param $agentId
     * @param $quantity
     * @param $over
     * @param string $mark
     * @return bool
     */
    public static function push($userId,$agentId,$quantity,$over,string $mark)
    {
        $medoo = new Medoo();
        $data['user_id'] = (int)$userId;
        $data['agent_id'] = (int)$agentId;
        $data['quantity'] = $quantity;
        $data['over'] = $over;
        $data['mark'] = (string)$mark;
        $data['create_time'] = art_d();
        $data['update_time'] = $data['create_time'];
        $pdoDoc = $medoo->insert('quantity_log',$data);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'积分变动记录生成错误');
        }
        $wsId = ArtWs::uidToWsId((int)$userId);
        if ($wsId !== false) {
            art_assign_ws(WsService::WS_SYN_QUANTITY_LOG,[],$data,0,$wsId);
        }
        return true;
    }


}