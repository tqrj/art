<?php


namespace app\traits;


use Swoole\Coroutine\Client;

class Lottery
{
    const LOTTERY_TYPE_OLD = 1001;
    const LOTTERY_TYPE_now = 2001;
    const LOTTERY_TYPE_check = 3001;

    /**
     * @param $str
     * @return array|false
     */
    public static function parseExp($str)
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
        if (!$client->connect('172.26.125.80', 9501))
        {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        $len  = pack('i',strlen($str)+4);
        $client->send($len.$str);
        $data = $client->recv();
        $client->close();
        if ($data == ''){
            echo $client->errMsg;
            return [];
        }
        //$data = urldecode(mb_substr($data,4));
        $data = iconv("gb2312//IGNORE","utf-8",mb_substr($data,4));
        $temps = explode('rn',(string)$data);
        array_walk($temps,function ($item) use(&$result){
            $temp = explode('<-->',$item);
            if (!is_array($temp) or count($temp) != 9 or $temp[0] != 1){
                return;
            }
            $result[] = $temp;
        });
        return $result;
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
            'package_length_type'   => 'l',
            'package_length_offset' => 0,
            'package_body_offset'   => 0,
        ]);
        if (!$client->connect('172.26.125.80', 9502))
        {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        if ($type  == self::LOTTERY_TYPE_OLD){
            $str =self::LOTTERY_TYPE_OLD;
        }elseif ($type == self::LOTTERY_TYPE_now){
            $str = self::LOTTERY_TYPE_now;
        }elseif ($type == self::LOTTERY_TYPE_check){
            $str =self::LOTTERY_TYPE_check.'|'.$code;
        }
        $len  = pack('i',strlen($str)+4);
        $client->send($len.$str);
        $result = $client->recv();
        $client->close();
        if ($result == false){
            echo $client->errMsg;
            return false;
        }
        $result = iconv("gb2312//IGNORE","utf-8",mb_substr($result,4));
        if($result == '查询失败'){
            return false;
        }
        if ($type == self::LOTTERY_TYPE_now){
            $result = explode(',',$result);
        }elseif($type == self::LOTTERY_TYPE_OLD){
            $result = explode('|',$result);
        }
        return $result;
    }
}