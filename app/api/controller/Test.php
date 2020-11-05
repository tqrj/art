<?php


namespace app\api\controller;


use app\BaseController;
use art\ArtWs;
use art\db\DB;
use art\helper\Str;
use Swoole\Coroutine\Client;

class Test extends BaseController
{

    private $isWs = true;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function hello()
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check'     => true,
            'package_max_length'    => 81920,
            'package_length_type'   => 'l',
            'package_length_offset' => 0,
            'package_body_offset'   => 0,
        ]);
        if (!$client->connect('172.26.125.80', 9501))
        {
            echo "connect failed. Error: {$client->errCode}\n";
            return;
        }

        $str = [];
        $str[] = '单10';
        $str[] = '12345-12345-12345-12369-2580/0.1';
        $str[] = '万23456千23456除各1';
        $rand = 1;
        $str = urlencode($str[$rand]);
        $len  = pack('i',strlen($str)+4);
        $client->send($len.$str);
        $result = $client->recv();
        if ($result == false){
            echo $client->errMsg;
            $result = $client->errMsg;
        }
        $client->close();
        art_assign(200,urldecode(mb_substr($result,4)));
    }

    public function test1()
    {
        $msg = $this->frame->data['msg'];
        ArtWs::joinGroup($this->response->artWsId,'test1');
        art_assign(200,$msg,[],'',0,0,'test1');
    }

    public function test2()
    {
        $msg = $this->frame->data['msg'];
        ArtWs::joinGroup($this->response->artWsId,'test2');
        art_assign(200,$msg,[],'',0,0,'test2');
    }
}