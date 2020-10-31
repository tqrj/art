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

    private static Table $wsTable;

    private static array $WsObject = [];

    public static function init()
    {
        if (!empty(self::$wsTable)) {
            return false;
        }
        self::$wsTable = new Table(1024);
        self::$wsTable->column('msg', Table::TYPE_STRING, 1024 * 10);
        self::$wsTable->column('sender', Table::TYPE_INT);
        self::$wsTable->column('recver', Table::TYPE_INT);
        self::$wsTable->column('status', Table::TYPE_INT);
        self::$wsTable->create();

        return new static();
    }

    public static function initPool($poolId)
    {
        self::$wsTable->set($poolId, ['msg' => '','sender'=>0,'recver'=>0,'status' => 1]);
        Timer::tick(10, function ($timerId, $poolId) {
            $row = self::$wsTable->get($poolId);
            if ($row['status'] == 1 or empty($row['msg'])) {
                return;
            }
            foreach (self::$WsObject as $key=>$ws){
//                if ($key === $row['sender']){
//                    continue;
//                }elseif(empty($row['recver'])){
//                    $ws->push($row['msg']);
//                }elseif($row['recver'] === $key){
//                    $ws->push($row['msg']);
//                }
                $ws->push($row['msg']);
            }
            $row['status'] = 1;
            self::$wsTable->set($poolId,$row);
        }, $poolId);
        Timer::tick(50000,function (){
            array_map(function (Response $ws) {
                $pingFrame = new Frame();
                $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
                $ws->push($pingFrame);
            }, self::$WsObject);
        });
    }

    public static function setWs(Response $ws):int
    {
        $wsId = getObjectId($ws);
        self::$WsObject[$wsId] = $ws;
        return $wsId;
    }

    public static function delWs(Response $ws)
    {
        $wsId = getObjectId($ws);
        unset(self::$WsObject[$wsId]);
    }

    /**
     * 发送消息
     * 群发不填写收信者ID即可
     * 指定发送发填写收信ID
     * @param string $message
     * @param int $selfWsId
     * @param int $recvId
     */
    public static function pushMsg(string $message,int $selfWsId = 0,int $recvId = 0)
    {
        foreach (self::$wsTable  as $key=>$item){
            go(function () use($key,$item,$message,$selfWsId,$recvId){
                //死循环，注意

                while ($item['status'] === 0){
                    System::sleep(0.05);
                    $item = self::$wsTable->get($key);
                }
                $item['msg'] = $message;
                $item['sender'] = $selfWsId;
                $item['recver'] = $recvId;
                $item['status'] = 0;
                self::$wsTable->set($key,$item);
            });
        }
    }

    public static function getWsTable(): Table
    {
        return self::$wsTable;
    }

    public static function getWsObjects():array
    {
        return self::$WsObject;
    }
}