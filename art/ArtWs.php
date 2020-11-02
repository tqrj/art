<?php


namespace art;


use Co\System;
use Swoole\Atomic;
use Swoole\Http\Response;
use Swoole\Table;
use Swoole\Timer;
use Swoole\WebSocket\Frame;


//把消息放在table
//key为当前进程ID
//msg pool status
//进程定时读当前进程的发完状态改为1，投递消息的时候看状态，如果待处理就等待
//问题就是这个表要维护，尽量避免遍历
class ArtWs
{
    private function __construct()
    {

    }

    private function __clone()
    {

    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    private static Atomic $wsAtomic;
    //通过swoole的 table来实现一个进程通信
    //进程为键名
    private static Table $wsMsgTable;

    //进程为键名
    private static Table $wsGroupTable;

    private static array $wsGroup;

    private static array $wsObject = [];

    public static function init()
    {
        if (!empty(self::$wsMsgTable)) {
            return false;
        }

        self::$wsMsgTable = new Table(1024);
        self::$wsMsgTable->column('msg', Table::TYPE_STRING, 1024 * 10);
        self::$wsMsgTable->column('sender', Table::TYPE_INT);
        self::$wsMsgTable->column('recver', Table::TYPE_INT);
        self::$wsMsgTable->column('status', Table::TYPE_INT);
        self::$wsMsgTable->create();

        self::$wsAtomic = new Atomic();
        return new static();
    }

    public static function joinPool($poolId)
    {
        self::$wsMsgTable->set($poolId, ['msg' => '', 'sender' => 0, 'recver' => 0, 'status' => 1]);
        //消息处理
        Timer::tick(10, function ($timerId, $poolId) {
            $row = self::$wsMsgTable->get($poolId);
            if ($row['status'] == 1 or empty($row['msg'])) {
                return;
            }
            foreach (self::$wsObject as $wsId => $ws) {
                if ($wsId === $row['sender']) {
                    continue;//不发送到自己
                } elseif ($row['recver'] == -1) {
                    $ws->push($row['msg']);//全部发送
                    continue;
                } elseif ($row['recver'] === $wsId) {
                    $ws->push($row['msg']);//指定收信ID
                }
            }
            $row['status'] = 1;
            self::$wsMsgTable->set($poolId, $row);
        }, $poolId);
        //so心跳
        Timer::tick(15000, function () {
            array_map(function (Response $ws) {
                $pingFrame = new Frame();
                $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
                $ws->push($pingFrame);
            }, self::$wsObject);
        });
    }

    public static function setWs(Response &$ws): int
    {
        $wsId = self::getWsId();
        $ws->artWsId = $wsId;
        self::$wsObject[$wsId] = $ws;
        return $wsId;
    }

    public static function delWs(Response $ws)
    {
        unset(self::$wsObject[$ws->artWsId]);
    }

    /**
     * 发送消息
     * @param string $message 消息内容
     * @param int $selfWsId 自己的发信ID 不传的话会通知到自己
     * @param int $recvId 指定发送发填写收信ID
     * @param string $group 发送指定群组
     */
    public static function pushMsg(string $message, int $selfWsId = -1, int $recvId = -1, string $group = '')
    {
        foreach (self::$wsMsgTable as $key => $item) {
            go(function () use ($key, $item, $message, $selfWsId, $recvId, $group) {
                //死循环，注意
                while ($item['status'] === 0) {
                    System::sleep(0.05);
                    $item = self::$wsMsgTable->get($key);
                }
                $item['msg'] = $message;
                $item['sender'] = $selfWsId;
                $item['recver'] = $recvId;
                $item['group'] = $group;
                $item['status'] = 0;
                self::$wsMsgTable->set($key, $item);
            });
        }
    }

    public static function joinGroup(int $wsId, string $group)
    {

    }

    public static function leaveGroup(int $wsId, string $group)
    {


    }


    public static function getGroup(): array
    {

    }

    public static function hasGroup()
    {

    }


    public static function getWsTable(): Table
    {
        return self::$wsMsgTable;
    }

    public static function getWsObjects(): array
    {
        return self::$wsObject;
    }

    private static function getWsId()
    {
        return self::$wsAtomic->add();
    }
}