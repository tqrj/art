<?php

namespace art;


use art\exception\ClassNotFoundException;
use art\exception\HttpException;
use Swoole\Coroutine\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;

use Swoole\Process\Pool;

require 'vendor/autoload.php';


//多进程管理模块
$pool = new Pool(swoole_cpu_num() + 2);
//让每个OnWorkerStart回调都自动创建一个协程
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    $pdoPool = new PDOPool((new PDOConfig)
        ->withHost('127.0.0.1')
        ->withPort(3306)
        // ->withUnixSocket('/tmp/mysql.sock')
        ->withDbName('art')
        ->withCharset('utf8mb4')
        ->withUsername('art')
        ->withPassword('dc4fJmEXaZffiAEs')
    );
    print_r($pdoPool);
    //每个进程都监听9501端口
    $server = new Server('0.0.0.0', '9502', false, true);
    $server->handle('/', function (Request $request, Response $response) {
        //有优化空间 使用context来管理 变成单例，不用每次加载  已经处理，全部换成静态的方法了 使用context管理上下文
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
    $server->handle('/favicon.ico', function (Request $request, Response $response) {
        $response->end('');
    });
    $server->start();
});
$pool->on('WorkerStop', function ($pool, $id) {
    print_r($id . '进程退出了' . PHP_EOL);
});
$pool->start();