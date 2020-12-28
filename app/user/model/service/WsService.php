<?php


namespace app\user\model\service;


use app\agent\model\service\RoomService;
use app\traits\Lottery;
use art\context\Context;
use art\db\Medoo;
use art\db\Redis;
use art\exception\HttpException;
use art\helper\Str;
use art\ws\ArtWs;
use Carbon\Carbon;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;

/**
 * Class WsService
 * @package app\user\model\service
 * ws 的code的话 200是不用去做解析 201
 */
class WsService
{
    const ORDER_REST_INC = 'ORDER_REST_INC';
    protected Response $ws;//$artWsId
    protected $userInfo = null;
    protected Medoo $medoo;
    protected $roomInfo;

    public function __construct()
    {
        $this->userInfo = Context::get('authInfo');
        $this->ws = Context::get('response');
        $this->medoo = new Medoo();
        $this->roomInfo = $this->medoo->get('room', '*', ['agent_id' => $this->userInfo['agent_id']]);
    }

    /**
     * 连接上之后加入到该房间分组
     * @todo 我是个傻逼 直接发送到群组 有人自然能收到~
     */
    public function joinGroup()
    {
        ArtWs::bindUid($this->ws->artWsId,(int)$this->userInfo['id']);
        ArtWs::joinGroup($this->ws->artWsId, $this->userInfo['agent_id']);
    }

    /**
     * 对用户消息进行处理
     * @param $params
     */
    public function push($params)
    {

        $data['authInfo'] = Context::get('authInfo');
        unset($data['authInfo']['openid']);
        art_assign_ws(200, htmlspecialchars($params['message']), $data, $this->userInfo['agent_id'],0,$this->ws->artWsId,);
        if ($this->checkScore($params['message'])) {
            return;
        } elseif ($this->checkPay($params['message'])) {
            return;
        } elseif ($this->checkReBack($params['message'])) {
            return;
        } elseif ($this->checkReOrder($params['message'])) {
            return;
        } elseif ($this->checkOrder($params['message'])) {
            return;
        };
        //if ($params)
    }

    /**
     * 查分识别
     * @param $message
     */
    private function checkScore($message)
    {
        $exp = '历史走势图|历史图|走势图|开奖图|开奖|长条|历史';
        if ($this->_codeExp($exp, $message) != false) {
            art_assign_ws(Lottery::LOTTERY_TYPE_OLD, 'success', Lottery::getCode(Lottery::LOTTERY_TYPE_OLD), $this->userInfo['agent_id']);
            return true;
        }
        $exp = '查流水';
        if ($this->_codeExp($exp, $message) != false) {
            $message = "[" . $this->userInfo['nickname'] . "] 您当前流水:" . $this->medoo->sum('order', 'quantity',[
                    'user_id' => $this->userInfo['id'],
                    'agent_id' => $this->userInfo['agent_id'],
                    'status' => 1
                ]);
            art_assign_ws(200, $message, [], $this->userInfo['agent_id']);
            return true;
        }
        $exp = '查|查分|查钱|查信用';
        if ($this->_codeExp($exp, $message) != false) {
            $message = "[" . $this->userInfo['nickname'] . '] 您当前分数:' . $this->medoo->get('user_quantity', 'quantity', [
                    'user_id' => $this->userInfo['id'],
                    'agent_id' => $this->userInfo['agent_id'],
                ]);
            art_assign_ws(200, $message, [], $this->userInfo['agent_id']);
            return true;
        }
        return false;
    }

    /**
     * 识别上分
     * @param $message
     */
    private function checkPay($message)
    {
        $matches = [];
        $bool = preg_match("#(上|充|加|上分|充值|充钱|加钱|加分)(\d+)#", $message, $matches);
        if (!$bool) {
            return false;
        }
        try {
            $params['quantity'] = (float)$matches[2];
            UserService::pay($params);
            art_assign_ws(200, '[' . $this->userInfo['nickname'] . '] 上分受理中', [], $this->userInfo['agent_id']);
        } catch (HttpException $e) {
            art_assign_ws($e->getStatusCode(), $e->getMessage(), [], $this->userInfo['agent_id']);
        }
        return true;
    }

    /**
     * 识别退分
     * @param $message
     */
    private function checkReBack($message)
    {
        $matches = [];
        $bool = preg_match("#(下|减|提|拿|下分|减分|提现|提钱|拿钱)(\d+)#", $message, $matches);
        if (!$bool) {
            return false;
        }
        try {
            $params['quantity'] = (float)$matches[2];
            UserService::reBack($params);
            art_assign_ws(200, '[' . $this->userInfo['nickname'] . '] 下分受理中', [], $this->userInfo['agent_id']);
        } catch (HttpException $e) {
            art_assign_ws($e->getStatusCode(), $e->getMessage(), [], $this->userInfo['agent_id']);
        }
        return true;
    }

    /**
     * 识别下单
     * 如果后期 要扩展网盘 需要注意下单这里，以及结算那里
     * @param $message
     */
    private function checkOrder($message)
    {
        if (empty($message) or !preg_match("#\d{1,}#", $message)) {
            return false;
        }
        $expMsg = Lottery::parseExp($message);
        if ($expMsg == false) {
            echo '没有识别成功'.$expMsg.PHP_EOL;
            return false;
        }
        $medoo = $this->medoo;
        $userInfo = $this->userInfo;
        $userInfo['quantity'] = (float)$medoo->get('user_quantity','quantity',['user_id'=>$userInfo['id'],'agent_id'=>$userInfo['agent_id']]);
//        $roomInfo = $medoo->get('room', '*', ['agent_id' => $userInfo['agent_id']]);
        $class = '';
        switch ($expMsg[2]) {
            case '一定':
                $class = 1;
                break;
            case '二定':
                $class = 2;
                break;
            case '三定':
                $class = 3;
                break;
            case '四定':
                $class = 4;
                break;
            case '五定':
                $class = 5;
                break;
        }
        if (empty($class)) {
            return false;
        }
        $nowLottery = Lottery::getCode(Lottery::LOTTERY_TYPE_now);
        if (count($nowLottery) != 5) {
            echo 'Ws:开奖信息错误' . PHP_EOL;
            return false;
        }
        $redis = \art\db\Redis::getInstance()->getConnection();
        $issue = $redis->get(RoomService::ROOM_ISSUE . $userInfo['agent_id']);
        Redis::getInstance()->close($redis);
        if (empty($issue) or $issue != $nowLottery[0]) {
            echo 'ws:当前期数错误' . PHP_EOL;
            return false;
        }
        $CarbonIssue = Carbon::parse(art_d(), 'Asia/Shanghai');
        $diff = $CarbonIssue->diffInRealSeconds($nowLottery[1]);
        if ((int)$diff <= (int)$this->roomInfo['closeTime']) {
            art_assign_ws(200,$userInfo['nickname'].': 当前已经封盘',[],$userInfo['agent_id']);
            return false;
        }
        $roomRule = $medoo->get('room_rule', '*', ['agent_id' => $userInfo['agent_id'], 'class' => $class]);
        if (empty($roomRule)) {
            return false;
        }
        if ($roomRule['status'] != 1){
            art_assign_ws(200,$userInfo['nickname'].': 暂不接收该玩法',[],$userInfo['agent_id']);
            return false;
        }
        if ($roomRule['max'] < $expMsg[6]){
            art_assign_ws(200,$userInfo['nickname'].': 金额无效 单注金额超出'.$roomRule['max'],[],$userInfo['agent_id']);
            return false;
        }
        if (!$this->asDecimal($expMsg[6],$roomRule['decimal'])){
            art_assign_ws(200,$userInfo['nickname'].': 金额无效',[],$userInfo['agent_id']);
            return false;
        }

        if ($userInfo['quantity'] < (float)$expMsg[7]){
            art_assign_ws(200,$userInfo['nickname'].' 账户积分不足:'.$userInfo['quantity'],[],$userInfo['agent_id']);
            return false;
        }
        $temp = 5 + strlen($userInfo['agent_id']);
        $orderData['orderNo'] = $issue . substr(time(), $temp) .mt_rand(100,999). $userInfo['agent_id'];
        $orderData['game'] = 'hn5f';
        $orderData['user_id'] = $userInfo['id'];
        $orderData['agent_id'] = $userInfo['agent_id'];
        $orderData['issue'] = $issue;
        $orderData['old_msg'] = $message;
        $orderData['exp_msg'] = $expMsg[8];
        $redis = \art\db\Redis::getInstance()->getConnection();
        $redis->set(self::ORDER_REST_INC . $issue . $userInfo['id'], 0, ['nx', 'ex' => $diff + mt_rand(10, 20)]);
        $orderData['reset_code'] = $redis->incr(self::ORDER_REST_INC . $issue . $userInfo['id']);/**/
        Redis::getInstance()->close($redis);
        $orderData['play_method'] = $expMsg[2];
        $orderData['play_site'] = $expMsg[3];
        $orderData['play_code'] = $expMsg[4];
        $orderData['play_code_count'] = $expMsg[5];
        $orderData['single_quantity'] = $expMsg[6];
        $orderData['quantity'] = $expMsg[7];
        $orderData['loc_quantity'] = $expMsg[7];//网盘的话这里要分一下~
        $orderData['line'] = $roomRule['line'];
        $orderData['whether_hit'] = 0;
        $orderData['status'] = 0;
        $orderData['create_time'] = art_d();
        $orderData['update_time'] = $orderData['create_time'];
        $msg = $userInfo['nickname']." {$issue}期".PHP_EOL.$orderData['play_method'].'-'.$orderData['play_site'].PHP_EOL;
        $msg.= $orderData['exp_msg'].PHP_EOL.'组'.$orderData['quantity'].'扣'.$orderData['quantity'].'余'.((float)$userInfo['quantity']-(float)$orderData['quantity']).PHP_EOL;
        $medoo->beginTransaction();
        try {
            $pdoDoc = $medoo->update('user_quantity',[
                'quantity[-]'=>$orderData['quantity']
            ],[
                'quantity'=>$userInfo['quantity'],
                'user_id'=>$userInfo['id'],
                'agent_id'=>$userInfo['agent_id']
            ]);
            if(!$pdoDoc->rowCount()){
                throw new \Exception('下单失败');
            }
            $pdoDoc = $medoo->insert('order',$orderData);
            if (!$pdoDoc->rowCount()){
                throw new \Exception('下单失败.');
            }
            $medoo->commit();
        }catch (\Exception $e){
            $medoo->rollBack();
            $msg.= '【下单失败】';
            art_assign_ws(200,$msg,[],$userInfo['agent_id']);
            return false;
        }
        $msg.= '退单请发送:退'.$orderData['reset_code'];
        art_assign_ws(200,$msg,[],$userInfo['agent_id']);
        return true;
    }

    /**
     * 识别退单
     * @param $message
     */
    private function checkReOrder($message)
    {
        $matches = [];
        $bool = preg_match("#^退(\d+)$#", $message, $matches);
        if (!$bool) {
            return false;
        }
        $nowLottery = Lottery::getCode(Lottery::LOTTERY_TYPE_now);
        if (count($nowLottery) != 5) {
            echo '退单:开奖信息错误' . PHP_EOL;
            return false;
        }
        $medoo = $this->medoo;
        $userInfo =$this->userInfo;
        $userInfo['quantity'] = (float)$medoo->get('user_quantity','quantity',['user_id'=>$userInfo['id'],'agent_id'=>$userInfo['agent_id']]);
//        $roomInfo = $medoo->get('room', '*', ['agent_id' => $userInfo['agent_id']]);
        $reCode = (int)$matches[1];
        $redis = \art\db\Redis::getInstance()->getConnection();
        $issue = $redis->get(RoomService::ROOM_ISSUE . $userInfo['agent_id']);
        Redis::getInstance()->close($redis);
        if (empty($issue) or $issue != $nowLottery[0]) {
            echo '退单:当前期数错误' . PHP_EOL;
            return false;
        }
        $CarbonIssue = Carbon::parse(art_d(), 'Asia/Shanghai');
        $diff = $CarbonIssue->diffInRealSeconds($nowLottery[1]);
        if ((int)$diff <= (int)$this->roomInfo['closeTime']) {
            //echo '退单:已封盘' . PHP_EOL;
            art_assign_ws(200,$userInfo['nickname'].' 退单失败:当前已封盘','',$userInfo['agent_id']);
            return false;
        }
        $orderInfo = $medoo->get('order',['id','quantity','create_time'],
            [
                'agent_id'=>$userInfo['agent_id'],
                'user_id'=>$userInfo['id'],
                'reset_code'=>$reCode,
                'issue'=>$issue
            ]);
        $diff = $CarbonIssue->diffInRealSeconds($orderInfo['create_time']);
        if ((int)$diff >= (int)$this->roomInfo['reTime']) {
            echo '退单:已超时' . PHP_EOL;
            art_assign_ws(200,$userInfo['nickname'].' 退单失败:已经超过退单时间','',$userInfo['agent_id']);
            return false;
        }


        if (empty($orderInfo)){
            art_assign_ws(200,$userInfo['nickname'].' 退单失败:没有查找到订单','',$userInfo['agent_id']);
            return false;
        }
        $medoo->beginTransaction();
        try {
            $pdoDoc = $medoo->update('user_quantity',[
                'quantity[-]'=>$orderInfo['quantity']
            ],[
                'quantity'=>$userInfo['quantity'],
                'user_id'=>$userInfo['id'],
                'agent_id'=>$userInfo['agent_id']
            ]);
            if(!$pdoDoc->rowCount()){
                throw new \Exception('退单失败');
            }
            $pdoDoc = $medoo->update('order',['status'=>-1],['id'=>$orderInfo['id']]);
            if (!$pdoDoc->rowCount()){
                throw new \Exception('退单失败.');
            }
            $medoo->commit();
        }catch (\Exception $e){
            $medoo->rollBack();
            art_assign_ws(200,$userInfo['nickname'].' 退单失败','',$userInfo['agent_id']);
            return false;
        }
        $msg  =$userInfo['nickname'].' 退单成功'.PHP_EOL;
        $msg.= '退'.$orderInfo['quantity'].'余'.($userInfo['quantity'] + $orderInfo['quantity']);
        art_assign_ws(200,[],$msg,$userInfo['agent_id']);
        return true;
    }

    private function _codeExp($code, $message): bool
    {
        $codeArr = explode("|", $code);
        foreach ($codeArr as $exp) {
            if ($exp == $message) {
                return true;
            }
        }
        return false;
    }


    /**
     * @param $quantity
     * @param $decimal
     * @return bool
     */
    private function asDecimal($quantity,$decimal):bool
    {
        switch ($decimal){
            case 0:
                $decimal = 0;
                break;
            case 1:
                $decimal = 0.1;
                break;
            case 2:
                $decimal = 0.01;
                break;
            case 3:
                $decimal = 0.001;
                break;
        }
        if ($decimal == 0 && $quantity < 1){
            return false;
        }elseif ($decimal == 0 && $quantity > 1 ){
            return true;
        }elseif ($quantity < $decimal or $this->getRemainder($quantity,$decimal) != 0){
            return false;
        }
        return true;
    }

    /**
     * @param $dividend
     * @param $divisor
     * @return float|int
     */
    private function getRemainder($dividend,$divisor)
    {
        $result = $dividend / $divisor;
        $result = explode('.',$result);
        if (count($result) == 1 ){
            return 0;
        }
        return (float)("0.".$result[1]);
    }
}