<?php


namespace app\traits;


use Swoole\Coroutine\Client;

class Lottery
{
    const LOTTERY_TYPE_OLD = '1001';
    const LOTTERY_TYPE_now = '2001';
    const LOTTERY_TYPE_check = '3001';

    /**
     * @param $str
     * @return string
     */
    public static function parseExp($str)
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check'     => true,
            'package_max_length'    => 81920,
            'package_length_type'   => 'L',
            'package_length_offset' => 0,
            'package_body_offset'   => 0,
        ]);
        if (!$client->connect('172.26.125.80', 9501))
        {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        $str = urlencode($str);
        $len  = pack('i',strlen($str)+4);
        $client->send($len.$str);
        $result = $client->recv();
        $client->close();
        if ($result == ''){
            echo $client->errMsg;
            return false;
        }
        $result = urldecode(mb_substr($result,4));
        if ($result === '识别失败'){
            return false;
        }
        return (string)$result;
    }

    /**
     * @param $type
     * @param string $code
     * @return bool|false|string|string[]
     */
    public static function getCode($type,$code='')
    {
        $str  = '';
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check'     => true,
            'package_max_length'    => 81920,
            'package_length_type'   => 'L',
            'package_length_offset' => 0,
            'package_body_offset'   => 0,
        ]);
        if (!$client->connect('172.26.125.80', 9502))
        {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        if ($type  == self::LOTTERY_TYPE_OLD){
            $str = urlencode(self::LOTTERY_TYPE_OLD);
        }elseif ($type == self::LOTTERY_TYPE_now){
            $str = urlencode(self::LOTTERY_TYPE_now);
        }elseif ($type == self::LOTTERY_TYPE_check){
            $str = urlencode(self::LOTTERY_TYPE_check.'|'.$code);
        }
        $len  = pack('i',strlen($str)+4);
        $client->send($len.$str);
        $result = $client->recv();
        $client->close();
        if ($result == false){
            echo $client->errMsg;
            return false;
        }
        $result = mb_substr($result,4);
        if($result == '%E6%9F%A5%E8%AF%A2%E5%A4%B1%E8%B4%A5'){
            return false;
        }
        if ($type == self::LOTTERY_TYPE_now){
            $result = explode(',',urldecode($result));
        }elseif($type == self::LOTTERY_TYPE_OLD){
            $result = explode('|',urldecode($result));
        }
        return $result;
    }
}