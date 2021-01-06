<?php
require 'vendor/autoload.php';

use app\traits\Lottery;

$n = 0;
while ($n < 100000) {
    \Co\System::sleep(0.01);
    $n++;
    \Co\run(function () {
        $result = Lottery::getCode(Lottery::LOTTERY_TYPE_OLD);
    });

}
echo 'ojbk处理完毕' . PHP_EOL;