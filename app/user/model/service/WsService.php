<?php


namespace app\user\model\service;


use app\traits\Lottery;
use art\context\Context;
use art\db\Medoo;
use art\ws\ArtWs;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;

class WsService
{
    protected Response $ws;//$artWsId
    protected $agentInfo = null;
    protected Medoo $medoo;

    public function __construct()
    {
        $this->agentInfo = Context::get('authInfo');
        $this->ws = Context::get('response');
        $this->medoo = new Medoo();
    }

    /**
     * 连接上之后加入到该房间分组
     */
    public function joinGroup()
    {
        ArtWs::joinGroup($this->ws->artWsId,$this->agentInfo['agent_id']);
    }

    /**
     * 对用户消息进行处理
     * @param $params
     */
    public function push($params)
    {

        //if ($params)
    }

    /**
     * 查分识别
     * @param $message
     *
     */
    private function checkScore($message)
    {

        $exp = '历史走势图|历史图|走势图|开奖图|开奖|长条|历史';
        if ($this->_codeExp($exp,$message) != false){
            art_assign_ws(200,'success',Lottery::getCode(Lottery::LOTTERY_TYPE_OLD),$this->agentInfo['agent_id']);
        }
        $exp = '查流水';
        if ($this->_codeExp($exp,$message) != false) {

        }
        $exp = '查，查分，查钱，查信用';
        if ($this->_codeExp($exp,$message) != false) {

        }

    }

    private function _codeExp($code,$message):bool
    {
        $codeArr = explode("|",$code);
        foreach ($codeArr as $exp){
            if($exp == $message){
                return true;
            }
        }
        return false;
    }
}