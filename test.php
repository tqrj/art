<?php

Co\run(function () {


    $n = 0;
    while ($n < 100000) {
        \Co\System::sleep(0.01);
        $n++;
        go(function () {
            $client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
            $client->set(array(
                'open_length_check' => true,
                'package_max_length' => 1024*40,
                'package_length_type' => 'l',
                'package_length_offset' => 0,
                'package_body_offset' => 0,
            ));
            if (!$client->connect('39.101.214.137', 9501)) {
                echo "connect failed. Error: {$client->errCode}\n";
                return;
            }
            $str = [];
            $str[] = '单10';
            $str[] = '12345-12345-12345-12369-2580/0.1';
            $str[] = '万23456千23456除各1';
            $rand = mt_rand(0, 2);
            $str = urlencode($str[$rand]);
            $len = pack('i', strlen($str) + 4);
            $client->send($len . $str);
            //sleep(0.5);
            //}
            $result = $client->recv();
            if ($result == false) {
                echo $client->errMsg;
            } else {
                echo urldecode(mb_substr($result, 4)) . PHP_EOL;
            }
            $client->close();
        });
    }
    echo 'ojbk处理完毕'.PHP_EOL;
});