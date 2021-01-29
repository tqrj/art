<?php


namespace app\traits;


use Swoole\Coroutine\Client;

trait Tape
{
    private int $CODE_LOGIN_TAPE = 1003;


    public function LoginTape($agentId,$siteUrl,$siteCode,$siteUser,$sitePass,$siteType)
    {
        $result = [];
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check'     => true,
            'package_max_length'    => 81920,
            'package_length_type'   => 'l',
            'package_length_offset' => 0,
            'package_body_offset'   => 0,
        ]);
        if (!$client->connect('10.53.55.1', 9503))
        {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        $data['code'] = $this->CODE_LOGIN_TAPE;
        $data['data']['agentId'] = $agentId;
        $data['data']['siteUrl'] = $siteUrl;
        $data['data']['siteCode'] = $siteCode;
        $data['data']['siteUser'] = $siteUser;
        $data['data']['sitePass'] = $sitePass;
        $data['data']['siteType'] = $siteType;
        $data = json_encode($data);

        //涉及中文 不要随意改用 mb
        $len  = pack('i',strlen($data)+4);
        $client->send($len.$data);
        $data = $client->recv();
        $client->close();
        if ($data == ''){
            echo $client->errMsg;
            return [];
        }
        //涉及中文 不要随意改用 mb
        $data = iconv("gb2312//IGNORE","utf-8",substr($data,4));
    }
}