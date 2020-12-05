<?php


namespace app\user\model\service;


use app\traits\Lottery;
use art\context\Context;
use art\db\Medoo;
use art\exception\HttpException;
use art\ws\ArtWs;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;

/**
 * Class WsService
 * @package app\user\model\service
 * ws 的code的话 200是不用去做解析 201
 */
class WsService
{
    protected Response $ws;//$artWsId
    protected $userInfo = null;
    protected Medoo $medoo;

    public function __construct()
    {
        $this->userInfo = Context::get('authInfo');
        $this->ws = Context::get('response');
        $this->medoo = new Medoo();
    }

    /**
     * 连接上之后加入到该房间分组
     */
    public function joinGroup()
    {
        ArtWs::joinGroup($this->ws->artWsId,$this->userInfo['agent_id']);
    }

    /**
     * 对用户消息进行处理
     * @param $params
     */
    public function push($params)
    {
        $this->checkScore($params['message']);
        $this->checkPay($params['message']);
        $this->checkReBack($params['message']);

        //if ($params)
    }

    /**
     * 查分识别
     * @param $message
     */
    private function checkScore($message)
    {
        $exp = '历史走势图|历史图|走势图|开奖图|开奖|长条|历史';
        if ($this->_codeExp($exp,$message) != false){
            art_assign_ws(Lottery::LOTTERY_TYPE_OLD,'success',Lottery::getCode(Lottery::LOTTERY_TYPE_OLD),$this->agentInfo['agent_id']);
        }
        $exp = '查流水';
        if ($this->_codeExp($exp,$message) != false) {
            $message = "[".$this->userInfo['nickname']."] 您当前流水:".$this->medoo->sum('order',[
                'user_id'=>$this->userInfo['id'],
                'agent_id'=>$this->userInfo['agent_id'],
                'status'=>1
            ],'quantity');
            art_assign_ws(200,$message,[],$this->userInfo['agent_id']);
        }
        $exp = '查，查分，查钱，查信用';
        if ($this->_codeExp($exp,$message) != false) {
            $message = "[".$this->userInfo['nickname'].'] 您当前分数:'.$this->medoo->get('user_quantity','quantity',[
                    'user_id'=>$this->userInfo['id'],
                    'agent_id'=>$this->userInfo['agent_id'],
                ]);
            art_assign_ws(200,$message,[],$this->userInfo['agent_id']);
        }
    }

    /**
     * 识别上分
     * @param $message
     */
    private function checkPay($message)
    {
        $matches = [];
        $bool = preg_match("#上|充|加|上分|充值|充钱|加钱|加分(\d+)#",$message,$matches);
        if (!$bool){
            return;
        }
        try {
            $params['quantity'] = $matches[1];
            UserService::pay($params);
        }catch (HttpException $e){
            art_assign_ws($e->getStatusCode(),$e->getMessage(),[],$this->userInfo['agent_id']);
        }
        art_assign_ws(200, '['.$this->userInfo['nickname'].'] 上分受理中',[],$this->userInfo['agent_id']);
    }

    /**
     * 识别退分
     * @param $message
     */
    private function checkReBack($message)
    {
        $matches = [];
        $bool = preg_match("#下|减|提|拿|下分|减分|提现|提钱|拿钱(\d+)#",$message,$matches);
        if (!$bool){
            return;
        }
        try {
            $params['quantity'] = $matches[1];
            UserService::reBack($params);
        }catch (HttpException $e){
            art_assign_ws($e->getStatusCode(),$e->getMessage(),[],$this->userInfo['agent_id']);
        }
        art_assign_ws(200, '['.$this->userInfo['nickname'].'] 下分受理中',[],$this->userInfo['agent_id']);
    }

    /**
     * 识别下单
     * @param $message
     */
    private function checkOrder($message)
    {

    }

    /**
     * 识别退单
     * @param $message
     */
    private function checkReOrder($message)
    {

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