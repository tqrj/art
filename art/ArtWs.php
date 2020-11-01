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
        self::$wsMsgTable->column('status', Table::TYPE_INT);
        self::$wsMsgTable->create();

        self::$wsGroupTable = new Table(1024);
        self::$wsGroupTable->column('wsId',Table::TYPE_INT);
        self::$wsGroupTable->column('group',Table::TYPE_STRING,40);
        self::$wsGroupTable->column('type',Table::TYPE_INT);
        self::$wsGroupTable->column('status',Table::TYPE_INT);
        self::$wsGroupTable->create();
        return new static();
    }

    public static function joinPool($poolId)
    {
        self::$wsMsgTable->set($poolId, ['msg' => '','sender'=>0,'recver'=>0,'status' => 1]);
        //self::$wsGroupTable->set($poolId, ['wsId' => -1,'group'=>'','type'=>0,'status' => 1]);
        //消息处理
        Timer::tick(10, function ($timerId, $poolId) {
            $row = self::$wsMsgTable->get($poolId);
            if ($row['status'] == 1 or empty($row['msg'])) {
                return;
            }
            foreach (self::$wsObject as $key=>$ws){
                if ($key === $row['sender']){
                    continue;
                }elseif ($row['recver']== -1 ){
                    $ws->push($row['msg']);
                    continue;
                }elseif ($row['recver'] === $key){
                    $ws->push($row['msg']);
                }
            }
            $row['status'] = 1;
            self::$wsMsgTable->set($poolId,$row);
        }, $poolId);
        //群组处理 加入 退出
/*        Timer::tick(10,function ($timeId,$poolId){
            $row = self::$wsGroupTable->get($poolId);
            if ($row['status'] == 1 or empty($row['group'])) {
                return;
            }
            if ($row['type'] === 1){
                self::$wsGroup[$row['group']][] = $row['wsId'];
            }else{
                unset(self::$wsGroup[$row['group']][$row['wsId']]);
            }
        });*/
        //so心跳
        Timer::tick(30000,function (){
            array_map(function (Response $ws) {
                $pingFrame = new Frame();
                $pingFrame->opcode = WEBSOCKET_OPCODE_TEXT;
                $ws->push($pingFrame);
            }, self::$wsObject);
        });
    }

    public static function setWs(Response $ws):int
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
     * @param int $selfWsId 群发不填写收信者ID即可
     * @param int $recvId 指定发送发填写收信ID
     */
    public static function pushMsg(string $message,int $selfWsId = -1,int $recvId = -1)
    {
        foreach (self::$wsMsgTable  as $key=>$item){
            go(function () use($key,$item,$message,$selfWsId,$recvId){
                //死循环，注意
                while ($item['status'] === 0){
                    System::sleep(0.05);
                    $item = self::$wsMsgTable->get($key);
                }
                $item['msg'] = $message;
                $item['sender'] = $selfWsId;
                $item['recver'] = $recvId;
                $item['status'] = 0;
                self::$wsMsgTable->set($key,$item);
            });
        }
    }


//    public static function createGroup()
//    {
//
//    }


    public static function joinGroup()
    {

    }

    public static function leaveGroup()
    {

    }


    public static function getGroup():array
    {

    }

    public static function hasGroup()
    {

    }


    public static function getWsTable(): Table
    {
        return self::$wsMsgTable;
    }

    public static function getWsObjects():array
    {
        return self::$wsObject;
    }
}