<?php


use Swoole\Process\Pool;
class eq{
    static $a;
}
eq::$a = 2;

require 'vendor/autoload.php';
//多进程管理模块
$pidPool = new Pool(swoole_cpu_num() + 2);
//让每个OnWorkerStart回调都自动创建一个协程
$pidPool->set(['enable_coroutine' => true]);
$pidPool->on('workerStart', function ($pidPool, $id) {
    echo eq::$a.PHP_EOL;
});
$pidPool->on('workerStop', function ($pidPool, $id) {
    print_r($id . '进程退出了' . PHP_EOL);
});
$pidPool->start();