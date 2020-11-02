<?php


namespace app\api\controller;


use app\BaseController;
use art\db\DB;
use art\helper\Str;
use Swoole\Coroutine\Client;

class Test extends BaseController
{
    /**
     *
     */
    public function hello()
    {
//        $params = Request::only(['pp','cc']);
//        art_validate($params,[
//            'cc'=>'require|mobile'
//        ]);
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check'     => true,
            'package_max_length'    => 81920,
            'package_length_type'   => 'l',
            'package_length_offset' => 0,
            'package_body_offset'   => 0,
        ]);
        if (!$client->connect('39.101.214.137', 9501))
        {
            echo "connect failed. Error: {$client->errCode}\n";
            return;
        }
        $str = [];
        $str[] = '单10';
        $str[] = '12345-12345-12345-12369-2580/0.1';
        $str[] = '万23456千23456除各1';
        $rand = 1;
        $str = $str[$rand];
        $len  = pack('i',strlen($str)+4);
        $client->send($len.$str);
        //sleep(0.5);
        //}
        while (true){
            $result = $client->recv();
            if ($result == false){
                echo $client->errMsg;
                $result = $client->errMsg;
                break;
            }
        }

        $client->close();
        art_assign(200,urldecode($result));
    }
}