<?php
namespace art;

use Swoole\Coroutine\Server;
use Swoole\Process\Pool;
use Swoole\Redis;

require 'vendor/autoload.php';


//多进程管理模块
$pool = new Pool(2);
//让每个OnWorkerStart回调都自动创建一个协程
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    //每个进程都监听9501端口
    $server = new \Swoole\Coroutine\Http\Server('47.242.23.154', '9501' , false, true);

    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
$pool->start();