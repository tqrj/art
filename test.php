<?php
require 'vendor/autoload.php';
use app\traits\Lottery;

Co\run(function () {


    $n = 0;
    while ($n < 100000) {
        \Co\System::sleep(0.02);
        $n++;
        $result = Lottery::getCode(Lottery::LOTTERY_TYPE_OLD);
        echo $result.PHP_EOL;
    }
    echo 'ojbk处理完毕'.PHP_EOL;
});