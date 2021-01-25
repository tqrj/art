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
            'package_max_length'    => 655360,
            'package_length_type'   => 'l',
            'package_length_offset' => 0,
            'package_body_offset'   => 0,
        ]);
        if (!$client->connect('172.26.125.80', 9501))
        {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        //涉及中文 不要随意改用 mb
        $len  = pack('i',strlen($str)+4);
        $client->send($len.$str);
        $data = $client->recv();
        $client->close();
        if ($data == ''){
            echo $client->errMsg;
            return [];
        }
        //涉及中文 不要随意改用 mb
        $data = iconv("gb2312//IGNORE","utf-8",substr($data,4));
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
            return [];
        }
        if ($type  == self::LOTTERY_TYPE_OLD){
            $str = self::LOTTERY_TYPE_OLD;
        }elseif ($type == self::LOTTERY_TYPE_now){
            $str = self::LOTTERY_TYPE_now;
        }elseif ($type == self::LOTTERY_TYPE_check){
            $str = self::LOTTERY_TYPE_check.'|'.$code;
        }
        //涉及中文 不要随意改用 mb
        $len  = pack('i',strlen($str)+4);
        $client->send($len.$str);
        $result = $client->recv();
        $client->close();
        if ($result == false){
            echo $client->errMsg;
            return [];
        }
        //涉及中文 不要随意改用 mb
        $result = iconv("gb2312//IGNORE","utf-8",substr($result,4));
        if($result == '查询失败'){
            return [];
        }
        if ($type == self::LOTTERY_TYPE_now){
            $result = self::nowDe($result);
        }elseif($type == self::LOTTERY_TYPE_OLD){
            $result = self::oldDe($result);
        }
        return $result;
    }

    private static function nowDe($data)
    {
        $result = [];
        $dataArr = explode(',',$data);
        if (count($dataArr)!=5){
            return $dataArr;
        }
        $result['nowIssue'] = mb_strlen($dataArr[0]) == 11 ? mb_substr($dataArr[0],8,3):$dataArr[0];
        $temp = mb_strpos($dataArr[1],' ') + 1;
        $result['nowShowTime'] = mb_substr($dataArr[1],$temp,mb_strripos($dataArr[1],':') - $temp);
        $result['lastShowTime'] = mb_substr($dataArr[2],$temp,mb_strripos($dataArr[2],':') - $temp);
        $result['lastIssue'] = mb_strlen($dataArr[3]) == 11 ? mb_substr($dataArr[3],8,3):$dataArr[3];
        $result['lastCode'] = mb_strlen($dataArr[4]) == 11 ? mb_substr($dataArr[4],8,3):$dataArr[4];
        $result['raw'] = $dataArr;
        return $result;
    }

    private static function oldDe($data)
    {
        $result = [];
        $dataArr = explode('|',$data);
        array_walk($dataArr,function ($item)use(&$result){
            $itemArr = explode(',',$item);
            if (count($itemArr) != 3){
                return;
            }
            $temp = mb_strpos($itemArr[0],' ') + 1;
            $tempArr['time'] = $itemArr[0];
            $tempArr['showTime'] =  mb_substr($itemArr[0],$temp,mb_strripos($itemArr[0],':') - $temp);
            $tempArr['issue'] = $itemArr[1];
            $tempArr['showIssue'] = mb_strlen($itemArr[1]) == 11 ? mb_substr($itemArr[1],8,3):$itemArr[1];
            $tempArr['arrCode'] = mb_str_split($itemArr[2],1);
            $tempArr['code'] = $itemArr[2];
            $result[] = $tempArr;
        });
        return $result;
    }
}