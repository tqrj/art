<?php
function int4(int $i)
{
    return pack('I',$i);
}

Co\run(function(){
    $client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
    $client->set(array(
        'open_length_check'     => true,
        'package_max_length'    => 81920,
        'package_length_type'   => 'L',
        'package_length_offset' => 0,
        'package_body_offset'   => 0,
    ));
    if (!$client->connect('39.101.214.137', 9501))
    {
        echo "connect failed. Error: {$client->errCode}\n";
        return;
    }
    while (true){
//        $result = $client->recv();
//        if ($result == false){
//            echo $client->errMsg;
//            break;
//        }
//        echo $result.PHP_EOL;
//        $str = 'qwq';
//        $len  = pack('i',strlen($str)+4);
//        $client->send($len.$str);
        sleep(0.5);
    }
    $client->close();
});