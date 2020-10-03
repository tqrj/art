<?php
namespace art;



use Swoole\Coroutine\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

use Swoole\Process\Pool;

require 'vendor/autoload.php';


//多进程管理模块
$pool = new Pool(swoole_cpu_num() + 2);
//让每个OnWorkerStart回调都自动创建一个协程
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    //每个进程都监听9501端口
    $server = new Server('0.0.0.0', '9502' , false, true);
    $server->handle('/', function (Request $request,Response $response) {
        //有优化空间 使用context来管理 变成单例，不用每次加载  已经处理，全部换成静态的方法了 使用context管理上下文
         HttpApp::init($request,$response);
         HttpApp::run();
    });
    $server->handle('/favicon.ico',function (Request $request,Response $response){
        $response->end('');
    });
    $server->handle('/stop', function (Request $request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
$pool->start();