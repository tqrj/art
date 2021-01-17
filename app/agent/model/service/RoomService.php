<?php


namespace app\agent\model\service;

use app\traits\Lottery;
use app\user\model\service\WsService;
use art\context\Context;
use art\db\Medoo;
use art\ws\ArtWs;
use Carbon\Carbon;
use Co\Redis;
use Swoole\Timer;

/**
 * Class Room 房间设置
 * @package app\agent\controller
 */
class RoomService
{
    const ROOM_ISSUE = 'room_issue_';
    const ROOM_CLOSE_MSG_FLAG = 'ROOM_CLOSE_MSG_FLAG';
    const ROOM_AFTER_FLAG = 'ROOM_AFTER_FLAG';
    const ROOM_STATUS_SETTLE = 1002;
    const ROOM_STATUS_CLOSE = 2002;

    /**
     * @return array
     */
    public static function switchOpen()
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $roomInfo = $medoo->get('room', ['id', 'status', 'timerID'], ['agent_id' => $agentInfo['id']]);
        if (!$roomInfo) {
            art_assign(202, '房间数据异常');
        }
        if ($roomInfo['timerID'] or $roomInfo['status']) {
            art_assign(202, '请勿重复开启!');
        }
        if (!$roomInfo['status']) {
            $bool = $medoo->update('room', ['status' => 1], ['agent_id' => $agentInfo['id']])->rowCount();
            if (!$bool) {
                art_assign(202, '更新数据出错!');
            }
        }
        $redis = \art\db\Redis::getInstance()->getConnection();
        $redis->del(self::ROOM_ISSUE . $agentInfo['id']);
        \art\db\Redis::getInstance()->close($redis);
        //开启房间定时器
        Timer::tick(5000, function (int $timer_id, $agent_info) {
            //这里不要每次都去查数据库 可以redis 记录一下在等待开奖的期号，然后每次去查服务的当前期号如果不是当前期号了就查该期号的结果
            $medoo = new Medoo();
            $roomInfo = $medoo->get('room', '*', ['agent_id' => $agent_info['id']]);
            if (!$roomInfo) {
                echo '房间定时器被清除了1';
                Timer::clear($timer_id);
                return;
            } else if ($roomInfo['status'] == 0) {
                echo '房间定时器被清除了2';
                art_assign_ws(200, '房间已关闭', [], $agent_info['id']);
                Timer::clear($timer_id);
                return;
            }
            if ($agent_info['expire_time'] < time()) {
                echo '代理过期 房间定时器被清除了3';
                art_assign_ws(200, '房间已关闭', [], $agent_info['id']);
                Timer::clear($timer_id);
                return;
            }
            //如果是的话就是第一次被开启
            //第一次被开启，需要先结清之前的账单，然后 获取即将开奖的号码，以及马上需要开奖的时间 放入table
            //然后定时检查该害差多久开始封盘，以及是不是已经开奖，已经开奖了就马上算账！
            if (!$roomInfo['timerID']) {
                echo '第一次开启补单' . PHP_EOL;
                $medoo->update('room', ['timerID' => $timer_id], ['id' => $roomInfo['id']]);
                self::repairOrder($agent_info['id']);//补单处理
                art_assign_ws(200, '房间已开启', [], $agent_info['id']);
                art_assign_ws(200, $roomInfo['notice'], [], $agent_info['id']);
            }
            $nowLottery = Lottery::getCode(Lottery::LOTTERY_TYPE_now);
            if (count($nowLottery) != 6) {
                echo 'Ws:开奖信息错误' . PHP_EOL;
                return false;
            }
            $nowLottery = $nowLottery['raw'];
            $redis = \art\db\Redis::getInstance()->getConnection();
            $issue = $redis->get(self::ROOM_ISSUE . $agent_info['id']);
            $CarbonIssue = Carbon::parse(art_d(), 'Asia/Shanghai');
            $diff = $CarbonIssue->diffInRealSeconds($nowLottery[1]);
            //echo $CarbonIssue->toDateTimeString().' diff:'.$diff.PHP_EOL;
            //如果redis没有获取到期号就是第一次 把当前期号设置进去
            if (empty($issue)) {

                $redis->set(self::ROOM_ISSUE . $agent_info['id'], $nowLottery[0], $diff + mt_rand(10, 20));
                $issue = $nowLottery[0];
            }
            $showIssue = mb_strlen($issue) == 11 ? mb_substr($issue, 8, 3) : $issue;


            //封盘处理
            if ((int)$diff <= (int)$roomInfo['closeTime']) {

                $bool = $redis->set(self::ROOM_CLOSE_MSG_FLAG . $agent_info['id'] . $nowLottery[0], '1', ['nx', 'ex' => $diff + mt_rand(10, 20)],);
                if ($bool) {
                    //echo '成功封盘'.$issue.PHP_EOL;
                    art_assign_ws(200, $roomInfo['notice_close'], [], $agent_info['id']);
                    if ($roomInfo['whether_closeInfo'] == 1) {
                        self::closeNotes($agent_info['id'], $issue);//F盘清账通知消息
                    }

                }
            }
            //有期号 且是当前期那么一样返回
            if (!empty($issue) and $issue === $nowLottery[0]) {
                //echo '有期号且是当前期'.$issue.PHP_EOL;
                //追码处理
                $bool = $redis->set(self::ROOM_AFTER_FLAG . $agent_info['id'] . $nowLottery[0], '1', ['nx', 'ex' => $diff + mt_rand(10, 20)],);
                if ($bool) {
                    echo '进入自动追码成功';
                    self::afterPay($roomInfo, $agent_info);
                }
                \art\db\Redis::getInstance()->close($redis);
                return;
            }
            //有期号 且是上一期那么就结算 并设置为当前期
            //结算
            if (!empty($issue) and $issue === $nowLottery[3]) {
                echo '进入结算成功' . $issue . PHP_EOL;
                art_assign_ws(200, $showIssue . '期 开' . $nowLottery[4], [], $agent_info['id']);
                self::settleOrder($agent_info['id'], $issue, $nowLottery[4]);//结算订单
                $redis->set(self::ROOM_ISSUE . $agent_info['id'], $nowLottery[0], $diff + mt_rand(10, 20));
                \art\db\Redis::getInstance()->close($redis);
                return;
            }
            echo '开奖可能出现问题' . $issue . PHP_EOL;
            \art\db\Redis::getInstance()->close($redis);
        }, $agentInfo, $medoo);
        return [];
    }

    /**
     * 封盘通知
     * @param $agentId
     * @param $issue
     */
    private static function closeNotes($agentId, $issue)
    {
        $medoo = new Medoo();
        $userList = $medoo->select('user_quantity(q)',
            [
                '[><]user(u)' => ['q.user_id' => 'id'],
                '[><]order(o)' => ['q.user_id' => 'user_id']
            ],
            [
                'u.id',
                'q.quantity',
                'u.nickname'
            ],
            [
                'GROUP' => 'u.id',
                'o.agent_id' => $agentId,
                'o.issue' => $issue,
                'o.status' => 0,
                'u.status' => 1,
                'q.status' => 1
            ]);
        array_walk($userList, function ($userInfo) use ($medoo, $agentId, $issue, &$result) {

            $userOrderList = $medoo->select('order(o)',
                [
                    '[><]user(u)' => ['o.user_id' => 'id'],
                    '[><]user_quantity(q)' => ['o.user_id' => 'user_id']
                ],
                [
                    'o.play_method',
                    'o.play_site',
                    'o.play_code',
                    'o.single_quantity',
                    'o.quantity',
                ],
                [
                    'u.id' => $userInfo['id'],
                    'o.agent_id' => $agentId,
                    'o.issue' => $issue,
                    'o.status' => 0,
                    'u.status' => 1,
                    'ORDER' => ['o.play_method' => 'ASC']
                ]);

            $orderResultData = [];
            $orderResultData['issue'] = mb_strlen($issue) == 11 ? mb_substr($issue, 8, 3) : $issue;
            $orderResultData['nickname'] = $userInfo['nickname'];
            $orderResultData['user_id'] = $userInfo['id'];
            $orderSumQuantity = 0;
            array_walk($userOrderList, function ($orderInfo) use (&$orderResultData, &$orderSumQuantity) {
                $temp['play_method'] = $orderInfo['play_method'];
                $temp['play_site'] = $orderInfo['play_site'];
                $temp['play_code'] = $orderInfo['play_code'];
                $temp['single_quantity'] = $orderInfo['single_quantity'];
                $temp['quantity'] = $orderInfo['quantity'];
                $orderResultData['orderList'][] = $temp;
                $orderSumQuantity += (float)$orderInfo['quantity'];
            });
            //用户历史总流水
            $orderResultData['past_sum_quantity'] = $medoo->sum('order', 'quantity', [
                'agent_id' => $agentId,
                'user_id' => $userInfo['id'],
                'status' => [1, 0]
            ]);
            $orderResultData['user_id'] = $userInfo['id'];
            $orderResultData['order_sum_quantity'] = $orderSumQuantity;
            $orderResultData['user_quantity'] = $userInfo['quantity'];
            art_assign_ws(self::ROOM_STATUS_CLOSE, '', $orderResultData, 0, (int)ArtWs::uidToWsId($userInfo['id']));
            //$result['orderResultList'][] = $orderResultData;
        });

    }

    /**
     * 补单
     * @param $agentId
     */
    private static function repairOrder($agentId)
    {
        $medoo = new Medoo();
        $orderList = $medoo->select('order', "*", [
            'agent_id' => $agentId,
            'status' => 0
        ]);
        array_walk($orderList, function ($orderInfo) use ($medoo) {
            $lotteryCode = Lottery::getCode(Lottery::LOTTERY_TYPE_check, $orderInfo['issue']);
            if (empty($lotteryCode) or $lotteryCode == false) {
                return;
            }
            $whetherScore = self::_whetherScore($lotteryCode, $orderInfo['play_code'], $orderInfo['play_site'], $orderInfo['single_quantity'], $orderInfo['line']);
            if ($whetherScore[0] == false) {
                //没中奖直接滚蛋
                $medoo->update('order',
                    [
                        'profit' => $orderInfo['quantity'],
                        'lottery_code' => $lotteryCode,
                        'whether_hit' => -1,
                        'status' => 1,
                        'update_time' => art_d()
                    ],
                    ['id' => $orderInfo['id']]);
                return;
            }
            $medoo->beginTransaction();
            try {
                $orderData['profit'] = bcsub($orderInfo['quantity'], $whetherScore[1], 2);
                $orderData['loc_quantity_ret'] = bcmul(bcdiv($whetherScore[1], $orderInfo['quantity'], 2), $orderInfo['loc_quantity'], 2);
                $orderData['whether_hit'] = 1;
                $orderData['status'] = 1;
                $orderData['lottery_code'] = $lotteryCode;
                $pdoDoc = $medoo->update('order', $orderData, ['id' => $orderInfo['id']]);
                if (!$pdoDoc->rowCount()) {
                    throw new \Exception('更新订单数据错误');
                }
                $userQuantity = $medoo->get('user_quantity', 'quantity', [
                    'user_id' => $orderInfo['user_id'],
                    'agent_id' => $orderInfo['agent_id']
                ]);
                $pdoDoc = $medoo->update('user_quantity', [
                    'quantity[+]' => $orderData['loc_quantity_ret']
                ], [
                    'user_id' => $orderInfo['user_id'],
                    'agent_id' => $orderInfo['agent_id'],
                    'quantity' => $userQuantity
                ]);
                if (!$pdoDoc->rowCount()) {
                    throw new \Exception('更新用户数据错误');
                }
                QuantityLogService::push($orderInfo['user_id'], $orderInfo['agent_id'], $orderData['loc_quantity_ret'], $userQuantity + $orderData['loc_quantity_ret'], '开盘补单 订单ID' . $orderInfo['id']);
                $medoo->commit();
            } catch (\Exception $e) {
                echo $e->getMessage();
                $medoo->rollBack();
                return;
            }
        });
    }

    /**
     * 结单
     * @param $agentId
     * @param $issue
     * @param $lotteryCode
     */
    private static function settleOrder($agentId, $issue, $lotteryCode)
    {
        $medoo = new Medoo();
        $roomInfo = $medoo->get('room', ['id', 'status', 'timerID', 'whether_water'], ['agent_id' => $agentId]);
        $orderList = $medoo->select('order(o)',
            [
                '[><]user(u)' => ['o.user_id' => 'id'],
                '[><]user_quantity(q)' => ['o.user_id' => 'user_id']
            ],
            [
                'o.id',
                'o.play_code_count',
                'o.play_site',
                'o.play_method',
                'o.quantity',
                'o.single_quantity',
                'o.loc_quantity',
                'o.line',
                'o.play_code',
                'o.user_id',
                'o.agent_id',
                'u.nickname',
                'q.quantity(user_quantity)'
            ],
            [
                'o.agent_id' => $agentId,
                'o.issue' => $issue,
                'o.status' => 0,
                'u.status' => 1,
                'ORDER' => ['u.id' => 'ASC']
            ]);

        $userOrderList = [];
//        $quantityTemp = [];

        array_walk($orderList, function ($orderInfo) use ($medoo, $issue, $lotteryCode, &$userOrderList) {
            $userOrderList[$orderInfo['user_id']]['nickname'] = $orderInfo['nickname'];
            $userOrderList[$orderInfo['user_id']]['user_id'] = $orderInfo['user_id'];
            $playerTempData['order_quantity'] = $orderInfo['quantity'];
            $playerTempData['play_code_count'] = $orderInfo['play_code_count'];
            $playerTempData['play_site'] = $orderInfo['play_site'];
            $playerTempData['play_method'] = $orderInfo['play_method'];
            $playerTempData['whether_hit'] = 0;
            $whetherScore = self::_whetherScore($lotteryCode, $orderInfo['play_code'], $orderInfo['play_site'], $orderInfo['single_quantity'], $orderInfo['line']);
            if ($whetherScore[0] == false) {
                //没中奖直接滚蛋
                $medoo->update('order',
                    [
                        'profit' => $orderInfo['quantity'],
                        'lottery_code' => $lotteryCode,
                        'whether_hit' => -1,
                        'status' => 1,
                        'update_time' => art_d()
                    ],
                    ['id' => $orderInfo['id']]);
                $userOrderList[$orderInfo['user_id']][] = $playerTempData;
                return;
            }
            $medoo->beginTransaction();
            try {
                $orderData['profit'] = bcsub($orderInfo['quantity'], $whetherScore[1], 2);
//                $orderData['loc_quantity_ret'] = $whetherScore[1] / $orderInfo['quantity'] * $orderInfo['loc_quantity'];
                $orderData['loc_quantity_ret'] = bcmul(bcdiv($whetherScore[1], $orderInfo['quantity'], 2), $orderInfo['loc_quantity'], 2);
                $orderData['whether_hit'] = 1;
                $orderData['status'] = 1;
                $orderData['lottery_code'] = $lotteryCode;
                $pdoDoc = $medoo->update('order', $orderData, ['id' => $orderInfo['id']]);
                if (!$pdoDoc->rowCount()) {
                    throw new \Exception('更新订单数据错误');
                }
                $userQuantity = $medoo->get('user_quantity', 'quantity', [
                    'user_id' => $orderInfo['user_id'],
                    'agent_id' => $orderInfo['agent_id']
                ]);
                $pdoDoc = $medoo->update('user_quantity', [
                    'quantity[+]' => $orderData['loc_quantity_ret']
                ], [
                    'user_id' => $orderInfo['user_id'],
                    'agent_id' => $orderInfo['agent_id'],
                    'quantity' => $userQuantity
                ]);
                if (!$pdoDoc->rowCount()) {
                    throw new \Exception('更新用户数据错误');
                }
                QuantityLogService::push($orderInfo['user_id'], $orderInfo['agent_id'], $orderData['loc_quantity_ret'], $userQuantity + $orderData['loc_quantity_ret'], '封盘结算 订单ID' . $orderInfo['id']);
                $medoo->commit();
            } catch (\Exception $e) {
                echo $e->getMessage();
                $medoo->rollBack();
                return;
            }
            $playerTempData['whether_hit'] = $whetherScore[1];
            $userOrderList[$orderInfo['user_id']][] = $playerTempData;
        });
        $showIssue = mb_strlen($issue) == 11 ? mb_substr($issue, 8, 3) : $issue;
        array_walk($userOrderList, function ($item) use ($issue, $showIssue, $lotteryCode, $agentId, $roomInfo, $medoo) {
            $item['issue'] = $showIssue;
            $item['lottery'] = $lotteryCode;
            if ($roomInfo['whether_water'] == 1) {
                $item['whether_water'] = 1;
                $item['past_sum_quantity'] = $medoo->sum('order', 'quantity', [
                    'agent_id' => $agentId,
                    'user_id' => $item['user_id'],
                    'status' => [1, 0]
                ]);
            } else {
                $item['whether_water'] = 0;
            }
            $item['user_quantity'] = $medoo->get('user_quantity', 'quantity', [
                'user_id' => $item['user_id'],
                'agent_id' => $agentId
            ]);
            art_assign_ws(self::ROOM_STATUS_SETTLE, 'success', $item, 0, (int)ArtWs::uidToWsId($item['user_id']));
        });
        //art_assign_ws(self::ROOM_STATUS_SETTLE, '', $result, $agentId);
        return;
    }

    /**
     * 是否中奖。如果中奖了就返回true和中奖金额
     * @param $lotteryCode
     * @param $orderCode
     * @param $siteCode
     * @param $singleQuantity
     * @param $line
     * @return array
     */
    private static function _whetherScore($lotteryCode, $orderCode, $siteCode, $singleQuantity, $line)
    {
        $result[0] = false;
        $result[1] = 0;
        $hitCode = self::_siteCode($lotteryCode, $siteCode);
//        echo 'hitCode'.$hitCode.PHP_EOL;
//        echo 'orderCode'.$orderCode.PHP_EOL;
        if (strpos($orderCode, $hitCode) === false) {
            return $result;
        }
//        echo '中了'.PHP_EOL;
        $result[0] = true;
        $result[1] = bcmul($singleQuantity, (float)$line, 2);
        return $result;
    }

    /**
     * 位置取号码
     * @param string $code
     * @param string $site
     * @return string
     */
    private static function _siteCode($code, $site)
    {
        $site = str_replace('万', '0', $site);
        $site = str_replace('千', '1', $site);
        $site = str_replace('百', '2', $site);
        $site = str_replace('十', '3', $site);
        $site = str_replace('个', '4', $site);
        $siteLen = mb_strlen($site);
        $resCode = '';
        for ($i = 0; $i < $siteLen; $i++) {
            $resCode .= mb_substr($code, mb_substr($site, $i, 1), 1);
        }
        return $resCode;
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
            $bool = $medoo->update('room', ['status' => 0, 'timerID' => 0], ['agent_id' => $agentInfo['id']])->rowCount();
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
        $medoo->beginTransaction();
        try {
            array_walk($params, function ($item, $key, Medoo $medoo) use ($agentInfo) {
                $pdoDoc = $medoo->update('room_rule', $item, ['agent_id' => $agentInfo['id'], 'class' => $key + 1]);
            }, $medoo);
            $medoo->commit();
        } catch (\Exception $e) {
            $medoo->rollBack();
            art_assign(202, $e->getMessage());
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
        $pdoDoc = $medoo->update('room', $params, ['id' => $roomInfo['id']]);
        if (!$pdoDoc->rowCount()) {
            art_assign(202, '更新失败');
        }
        return [];
    }

    public static function changeSite($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $roomInfo = $medoo->get('room', ['id', 'status'], ['agent_id' => $agentInfo['id']]);
        if (!$roomInfo) {
            art_assign(202, '房间数据异常');
        }
        $pdoDoc = $medoo->update('room', $params, ['id' => $roomInfo['id']]);
        if (!$pdoDoc->rowCount()) {
            art_assign(202, '更新失败');
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
        $roomInfo['rule'] = $medoo->select('room_rule', '*', ['agent_id' => $agentInfo['id']]);
        return $roomInfo;
    }

    /**
     * 创建代理的时候附带初始化所属房间
     * @param $agentId
     */
    public static function create($agentId)
    {
        $medoo = new Medoo();
        $bool = $medoo->has('room', ['agent_id' => $agentId]);
        if ($bool) {
            art_assign(202, '创建房间失败');
        }
        $roomData['agent_id'] = $agentId;
        $roomData['status'] = 0;
        $roomData['timerID'] = 0;
        $roomData['create_time'] = art_d();
        $roomData['update_time'] = art_d();
        $pdoDoc = $medoo->insert('room', $roomData);
        if (!$pdoDoc->rowCount()) {
            art_assign(202, '创建房间信息失败');
        }
        $roomRule['agent_id'] = $agentId;
        $roomRule['line'] = 9.7;
        $roomRule['max'] = 1000;
        $roomRule['eat'] = 0;
        $roomRule['eatNum'] = 20;
        $roomRule['decimal'] = 0;
        $roomRule['status'] = 1;
        $roomRuleAll = [];
        for ($i = 1; $i <= 5; $i++) {
            $roomRule['class'] = $i;
            $roomRuleAll[] = $roomRule;
        }
        $pdoDoc = $medoo->insert('room_rule', $roomRuleAll);
        if (!$pdoDoc->rowCount()) {
            art_assign(202, '创建房间规则信息失败');
        }
        return [];
    }

    public static function webSiteList()
    {
        $medoo = new Medoo();
        $result = $medoo->select('website', '*', ['status' => 1]);
        return $result;
    }

    /**
     * 追码
     * @param $roomInfo
     * @param $agentInfo
     */
    private static function afterPay($roomInfo, $agentInfo)
    {
        $medoo = new Medoo();
        $afterList = $medoo->select('after', [
            'id',
            'user_id',
            'agent_id',
            'message',
            'exp_msg',
            'count',
            'executeds',
            'rate_type',
            'rate',
            'rate_count',
            'halt_profit',
            'halt_loss',
            'order_ids',
            'last_order_ids',
            'profit'
        ], [
            'agent_id' => $agentInfo['id'],
            'status' => 1
        ]);
        array_walk($afterList, function (&$after) use ($roomInfo) {
            $exp_msg = json_decode($after['exp_msg'], true);
            if ($after['count'] <= $after['executeds']) {
                $after['status'] = 0;
                $after['reset_code'] = 0;
                return;
            }

            $after['executeds']++;
            $medoo = new Medoo();

            $map['u.id'] = $after['user_id'];
            $map['agent_id'] = $after['agent_id'];
            $map['u.status'] = [1];
            $map['q.status'] = [1];
            $userInfo = $medoo->get('user(u)',
                [
                    '[><]user_quantity(q)' => ['u.id' => 'user_id'],
                    '[><]agent(a)' => ['q.agent_id' => 'id'],
                ],
                [
                    'u.id',
                    'u.nickname',
                    'headimgurl',
                    'refresh_token',
                    'openid',
                    'q.agent_id',
                ],
                $map
            );
            //用户被删除了就不再追码了
            if (empty($userInfo)){
                $after['status'] = 0;
                $after['reset_code'] = 0;
                return;
            }

            $after['order_ids'] = json_decode($after['order_ids'], true);
            $after['last_order_ids'] = json_decode($after['last_order_ids'], true);

            //如果有上期订单ID 需要判断上期的结果 来处理倍投
            //如果有上期订单ID 需要算进利润 来处理 止损 止亏
            if ($after['last_order_ids'] != []) {
                //这个利润是所有订单的哦
                $orderSlimInfo['profit'] = $medoo->get('order',
                    [
                        'profit' => Medoo::raw('SUM(profit)')
                    ],
                    [
                        'id' => $after['order_ids'],
                        'status' => 1
                    ])['profit'];
                //这个只是上期的是否中奖
                $orderSlimInfo['whether_hit'] = $medoo->get('order',
                    [
                        'whether_hit' => Medoo::raw('SUM(whether_hit)')
                    ],
                    [
                        'id' => $after['last_order_ids'],
                        'status' => 1
                    ])['whether_hit'];
                if ($orderSlimInfo['whether_hit'] > -count($after['last_order_ids'])) {
                    $orderSlimInfo['whether_hit'] = 1;
                } else {
                    $orderSlimInfo['whether_hit'] = -1;
                }

                //止亏 止赢
                //因为之前的盈利 是以代理端的角色来计算的 ，与用户是相反的 所以这里是 -=
                $after['profit'] -= $orderSlimInfo['profit'];
                if ($after['halt_profit'] != 0 and $after['profit'] >= $after['halt_profit']) {
                    $after['reset_code'] = 0;
                    $after['status'] = 0;
                    return;
                }
                if ($after['halt_loss'] != 0 and $after['profit'] <= -$after['halt_loss']) {
                    $after['status'] = 0;
                    $after['reset_code'] = 0;
                    return;
                };
                //倍投处理
                if ($after['rate_type'] != 0) {

                    if ($after['rate_type'] == 1 and $orderSlimInfo['whether_hit'] == 1) {

                        $after['rate_count']++;
                        $exp_msg = self::rate($exp_msg, $after['rate'], $after['rate_count']);

                    } elseif ($after['rate_type'] == 2 and $orderSlimInfo['whether_hit'] == -1) {

                        $after['rate_count']++;
                        $exp_msg = self::rate($exp_msg, $after['rate'], $after['rate_count']);

                    }
                }

            }
            $after['last_order_ids'] = [];
            $resMsg = '';
            array_walk($exp_msg, function ($item) use ($userInfo, $roomInfo, &$after, &$resMsg) {
                $temp = '';
                $orderId = WsService::payOrder($roomInfo, $userInfo, $item, $after['message'], $temp, $after['id']);
                if ($orderId != false) {
                    $after['last_order_ids'][] = $orderId;
                    $after['order_ids'][] = $orderId;
                }
                $resMsg .= ($temp . PHP_EOL . '----------------------' . PHP_EOL);
            });
            $wsId = ArtWs::uidToWsId($after['user_id']);
            if ($wsId !== false && !empty($resMsg)) {
                $resMsg = substr($resMsg, 0, strripos($resMsg, '----------------------'));
                $resMsg = '------自动追码计划注单------' . PHP_EOL . $resMsg;
                art_assign_ws(200, $resMsg, [], 0, $wsId);
            }
        });

        array_walk($afterList, function ($item) use ($medoo) {
            $item['last_order_ids'] = json_encode($item['last_order_ids']);
            $item['order_ids'] = json_encode($item['order_ids']);
            $medoo->update('after', $item, ['id' => $item['id']]);
        });
    }

    /**
     * 计算倍率
     * @param array $expMsg
     * @param $rate
     * @param $count
     * @return array
     */
    private static function rate(array $expMsg, $rate, $count)
    {
        array_walk($expMsg, function (&$item) use ($rate, $count) {
            if (count($item) < 7) {
                return;
            }
            $item[6] = $item[6] * $rate * $count;
            $item[7] = $item[7] * $rate * $count;
        });
        return $expMsg;
    }


}