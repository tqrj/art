<?php


namespace art;


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
        self::$wsTable->column('status', Table::TYPE_INT);
        self::$wsTable->create();

        return new static();
    }

    public static function initPool($poolId)
    {
        self::$wsTable->set($poolId, ['msg' => '', 'status' => 1]);
        Timer::tick(10, function ($timerId, $poolId) {
            $row = self::$wsTable->get($poolId);
            if ($row['status'] == 1) {
                return;
            }
            $msg = $row['msg'];
            array_map(function (Response $ws) use ($msg) {
                $ws->push($msg);
            }, self::$WsObject);
            $row['status'] = 1;
            self::$wsTable->set($poolId,$row);
        }, $poolId);
        Timer::tick(20000,function (){
            array_map(function (Response $ws) {
                $pingFrame = new Frame();
                $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
                $ws->push($pingFrame);
            }, self::$WsObject);
        });
    }

    public static function setWs(Response $ws)
    {
        $wsId = getObjectId($ws);
        self::$WsObject[$wsId] = $ws;
    }

    public static function delWs(Response $ws)
    {
        $wsId = getObjectId($ws);
        unset(self::$WsObject[$wsId]);
    }

    public static function pushMsgAll(string $msg)
    {
        foreach (self::$wsTable  as $key=>$item){
            $item['msg'] = $msg;
            $item['status'] = 0;
            self::$wsTable->set($key,$item);
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