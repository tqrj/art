<?php


namespace app\user\model\service;


use app\traits\Lottery;
use art\context\Context;
use art\ws\ArtWs;

class WsService
{

    /**
     * 连接上之后加入到该房间分组
     */
    public static function joinGroup()
    {
        $agentInfo = Context::get('authInfo');
        $ws = Context::get('response');
        ArtWs::joinGroup($ws->artWsId,$agentInfo['agent_id']);
    }

    /**
     * 对用户消息进行处理
     * @param $params
     */
    public static function push($params)
    {
        $agentInfo = Context::get('authInfo');
        $ws = Context::get('response');
        ArtWs::pushMsg($params['message'],0,0,$agentInfo['agent_id']);
        //if ($params)
    }

    /**
     * 查分识别
     * @param $message
     *
     */
    private static function checkScore($message)
    {
        $exp = '历史走势图|历史图|走势图|开奖图|开奖|长条|历史';
        if (strpos($exp,$message) !== false){
            art_assign(200,'success',Lottery::getCode(Lottery::LOTTERY_TYPE_OLD));
        }
        $exp = '查流水';
        if (strpos($exp,$message) !== false) {

        }
        $exp = '查，查分，查钱，查信用';
        if (strpos($exp,$message) !== false) {

        }

    }
}