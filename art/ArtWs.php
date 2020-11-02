<?php


namespace art;


use Co\System;
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
        self::$wsMsgTable->column('group', Table::TYPE_STRING, 40);
        self::$wsMsgTable->column('status', Table::TYPE_INT);
        self::$wsMsgTable->create();

        self::$wsGroupTable = new Table(1024);
        self::$wsGroupTable->column('wsId', Table::TYPE_INT);
        self::$wsGroupTable->column('group', Table::TYPE_STRING, 40);
        self::$wsGroupTable->column('type', Table::TYPE_INT);
        self::$wsGroupTable->column('status', Table::TYPE_INT);
        self::$wsGroupTable->create();
        return new static();
    }

    public static function joinPool($poolId)
    {
        self::$wsMsgTable->set($poolId, ['msg' => '', 'sender' => 0, 'recver' => 0, 'group' => '', 'status' => 1]);
        self::$wsGroupTable->set($poolId, ['wsId' => -1, 'group' => '', 'type' => 0, 'status' => 1]);
        //消息处理
        Timer::tick(10, function ($timerId, $poolId) {
            $row = self::$wsMsgTable->get($poolId);
            if ($row['status'] == 1 or empty($row['msg'])) {
                return;
            }
            foreach (self::$wsObject as $wsId => $ws) {
                var_dump($row);
                if ($wsId === $row['sender']) {
                    continue;//不发送到自己
                } elseif ($row['recver'] == -1 && empty($row['group'])) {
                    $ws->push($row['msg']);//全部发送
                    continue;
                } elseif ($row['recver'] === $wsId) {
                    $ws->push($row['msg']);//指定收信ID
                } elseif (!empty(self::$wsGroup[$row['group']][$wsId])) {
                    $ws->push($row['msg']);//指定收信群组
                }
            }
            $row['status'] = 1;
            self::$wsMsgTable->set($poolId, $row);
        }, $poolId);
        //群组处理 加入 退出
        Timer::tick(10, function ($timeId, $poolId) {
            $row = self::$wsGroupTable->get($poolId);
            if ($row['status'] == 1 or empty($row['group'])) {
                return;
            } elseif ($row['type'] === 1) {
                self::$wsGroup[$row['group']][] = $row['wsId'];
            } else {
                unset(self::$wsGroup[$row['group']][$row['wsId']]);
            }
            $row['status'] = 1;
            self::$wsGroupTable->set($poolId, $row);
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

    public static function setWs(Response $ws): int
    {
        $wsId = getObjectId($ws);
        self::$wsObject[$wsId] = $ws;
        return $wsId;
    }

    public static function delWs(Response $ws)
    {
        $wsId = getObjectId($ws);
        unset(self::$wsObject[$wsId]);
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
        foreach (self::$wsGroupTable as $poolId => $item) {
            go(function () use ($poolId, $item, $wsId, $group) {

                while ($item['status'] === 0) {
                    System::sleep(0.05);
                    $item = self::$wsMsgTable->get($poolId);
                }
                $item['wsId'] = $wsId;
                $item['type'] = 1;
                $item['group'] = $group;
                $item['status'] = 0;
                self::$wsGroupTable->set($poolId, $item);
            });
        }
    }

    public static function leaveGroup(int $wsId, string $group)
    {
        foreach (self::$wsGroupTable as $poolId => $item) {
            go(function () use ($poolId, $item, $wsId, $group) {

                while ($item['status'] === 0) {
                    System::sleep(0.05);
                    $item = self::$wsMsgTable->get($poolId);
                }
                $item['wsId'] = $wsId;
                $item['type'] = 0;
                $item['group'] = $group;
                $item['status'] = 0;
                self::$wsGroupTable->set($poolId, $item);
            });
        }
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
}