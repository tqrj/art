<?php

namespace art;


use art\exception\ClassNotFoundException;
use art\exception\HttpException;
use Co\System;
use Swoole\Coroutine\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

use Swoole\Process\Pool;
use Swoole\Table;

require 'vendor/autoload.php';



ArtWs::init();
//多进程管理模块
$pidPool = new Pool(swoole_cpu_num() * 2 + 2);
//让每个OnWorkerStart回调都自动创建一个协程
$pidPool->set(['enable_coroutine' => true]);
$pidPool->on('workerStart', function ($pidPool,int $id) {
    //通过 getProcess 然后创建子进程，然后监听
    //每个进程都监听9502端口
    $server = new Server('0.0.0.0', '9502', false, true);
    $server->handle('/', function (Request $request,Response $response) {
        //有优化空间 使用context来管理 变成单例，不用每次加载  已经处理，全部换成静态的方法了 cocomposer require --dev "eaglewu/swoole-ide-helper:dev-master"使用context管理上下文
        try {
            HttpApp::init($request, $response);
            HttpApp::run();
            HttpApp::end();
        } catch (HttpException $e) {
            art_assign($e->getStatusCode(), $e->getMessage());
        } catch (ClassNotFoundException $e) {
            art_assign(404, $e->getMessage());
        }
    });

    //websocket部分
    ArtWs::joinPool($id);
    $server->handle('/so',function (Request $request,Response $ws){
        $bool = $ws->upgrade();
        if ($bool == false){
            return;
        }
        $wsId = ArtWs::setWs($ws);
        echo $wsId;
        while (true){
            $frame = $ws->recv();
            if ($frame === ''){
                ArtWs::delWs($ws);
                $ws->close();
                break;
            } else if ($frame === false) {
                ArtWs::delWs($ws);
                echo "error : " . swoole_last_error() . "\n";
                break;
            } elseif($frame->opcode == WEBSOCKET_OPCODE_TEXT){
                try {
                    WsApp::init($request,$ws,$frame);
                    WsApp::run();
                    WsApp::end();
                } catch (HttpException $e) {
                    art_assign($e->getStatusCode(), $e->getMessage());
                } catch (ClassNotFoundException $e) {
                    art_assign(404, $e->getMessage());
                }
                //ArtWs::pushMsg($frame->data,$wsId,2);
                //$ws->push();
            }
        }
    });
    $server->handle('/favicon.ico', function (Request $request, Response $response) {
        $response->end('');
    });
    $server->start();
});
$pidPool->on('workerStop', function ($pidPool, $id) {
    print_r($id . '进程退出了' . PHP_EOL);
});
$pidPool->start();