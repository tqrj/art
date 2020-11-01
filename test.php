<?php
function int4(int $i)
{
    return pack('I',$i);
}

Co\run(function(){


    $n=0;
    while ($n < 100000){
        \Co\System::sleep(0.05);
        $n++;
        go(function (){
            $client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
            $client->set(array(
                'open_length_check'     => true,
                'package_max_length'    => 81920,
                'package_length_type'   => 'L',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
            ));
            if (!$client->connect('39.101.214.137', 9501))
            {
                echo "connect failed. Error: {$client->errCode}\n";
                return;
            }
            $str = [];
            $str[] = '单10';
            $str[] = '双50';
            $str[] = '万23456千23456除各1';
            $rand = mt_rand(0,2);
            $str = $str[$rand];
            $len  = pack('i',strlen($str)+4);
            $client->send($len.$str);
            //sleep(0.5);
            //}
            $result = $client->recv();
            if ($result == false){
                echo $client->errMsg;
            }else{
                echo $result.PHP_EOL;
            }
            $client->close();
        });
    }

});