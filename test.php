<?php
require 'vendor/autoload.php';

use app\traits\Lottery;

$n = 0;
while ($n < 100000) {
    $n++;
    \Co\run(function () {
        $str[] = '单10';
        $str[] = '12345-12345-12345-12369-2580/0.1';
        $str[] = '万23456千23456除各1';
        $result = Lottery::parseExp($str[mt_rand(0,2)]);
    });

}
echo 'ojbk处理完毕' . PHP_EOL;