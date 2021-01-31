<?php


namespace app\traits;


use Swoole\Coroutine\Client;

trait Tape
{
    private static int $CODE_LOGIN_TAPE = 1003;
    private static int $CODE_GET_ISSUE = 3003;
    private static int $CODE_GET_QUANTITY = 2003;
    private static int $CODE_PAY_ORDER = 4003;
    private static int $CODE_RE_ORDER = 5003;
    private static int $CODE_CLOSE = 6003;
    private static string $HOST = '10.53.55.1';
    private static int $PORT = 9503;


    public static function loginTape($agentId, $siteUrl, $siteCode, $siteUser, $sitePass, $siteType, &$resMsg = '')
    {
        $result = [];
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check' => true,
            'package_max_length' => 81920,
            'package_length_type' => 'l',
            'package_length_offset' => 0,
            'package_body_offset' => 0,
        ]);
        if (!$client->connect(self::$HOST, self::$PORT)) {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        $data['code'] = self::$CODE_LOGIN_TAPE;
        $data['data']['agentId'] = $agentId;
        $data['data']['siteUrl'] = $siteUrl;
        $data['data']['siteCode'] = $siteCode;
        $data['data']['siteUser'] = $siteUser;
        $data['data']['sitePass'] = $sitePass;
        $data['data']['siteType'] = $siteType;
        $data = json_encode($data);

        //涉及中文 不要随意改用 mb
        $len = pack('i', strlen($data) + 4);
        $client->send($len . $data);
        $result = $client->recv();
        $client->close();
        if ($result == '') {
            echo $client->errMsg;
            return [];
        }
        //涉及中文 不要随意改用 mb
        $result = iconv("gb2312//IGNORE", "utf-8", substr($result, 4));
        $result = json_decode($result, true);
        //var_dump($result);
        //echo '登录网盘返回结果'.$result['msg'].PHP_EOL;
        $resMsg = $result['msg'];
        if ($result['code'] != 200) {
            return false;
        }
        return true;
    }

    public static function getIssueTape($agentId, &$issue)
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check' => true,
            'package_max_length' => 81920,
            'package_length_type' => 'l',
            'package_length_offset' => 0,
            'package_body_offset' => 0,
        ]);
        if (!$client->connect(self::$HOST, self::$PORT)) {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        $data['code'] = self::$CODE_GET_ISSUE;
        $data['data']['agentId'] = $agentId;

        $data = json_encode($data);

        //涉及中文 不要随意改用 mb
        $len = pack('i', strlen($data) + 4);
        $client->send($len . $data);
        $result = $client->recv();
        $client->close();
        if ($result == '') {
            echo $client->errMsg;
            return [];
        }
        //涉及中文 不要随意改用 mb
        $result = iconv("gb2312//IGNORE", "utf-8", substr($result, 4));
        $result = json_decode($result, true);
        //var_dump($result);
        //echo '获取期号返回结果'.$result['msg'].PHP_EOL;
        $issue = $result['msg'];
        if ($result['code'] != 200) {
            return false;
        }
        return true;
    }

    public static function getQuantityTape($agentId, &$quantity)
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check' => true,
            'package_max_length' => 81920,
            'package_length_type' => 'l',
            'package_length_offset' => 0,
            'package_body_offset' => 0,
        ]);
        if (!$client->connect(self::$HOST, self::$PORT)) {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        $data['code'] = self::$CODE_GET_QUANTITY;
        $data['data']['agentId'] = $agentId;

        $data = json_encode($data);

        //涉及中文 不要随意改用 mb
        $len = pack('i', strlen($data) + 4);
        $client->send($len . $data);
        $result = $client->recv();
        $client->close();
        if ($result == '') {
            echo $client->errMsg;
            return [];
        }
        //涉及中文 不要随意改用 mb
        $result = iconv("gb2312//IGNORE", "utf-8", substr($result, 4));
        $result = json_decode($result, true);
        //var_dump($result);
        //echo '取网盘余额返回结果'.$result['msg'].PHP_EOL;
        $quantity = $result['msg'];
        if ($result['code'] != 200) {
            return false;
        }
        return true;
    }

    public static function payOrderTape($agentId, $issue, $playMethod, $playSite, $playCode, $singleQuantity, $quantity, &$orderCode)
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check' => true,
            'package_max_length' => 655360,
            'package_length_type' => 'l',
            'package_length_offset' => 0,
            'package_body_offset' => 0,
        ]);
        if (!$client->connect(self::$HOST, self::$PORT)) {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        $data['code'] = self::$CODE_PAY_ORDER;
        $data['data']['agentId'] = $agentId;
        $data['data']['issue'] = $issue;
        $data['data']['play_method'] = $playMethod;
        $data['data']['play_site'] = $playSite;
        $data['data']['play_code'] = $playCode;
        $data['data']['single_quantity'] = $singleQuantity;
        $data['data']['quantity'] = $quantity;

        $data = json_encode($data);

        //涉及中文 不要随意改用 mb
        $len = pack('i', strlen($data) + 4);
        $client->send($len . $data);
        $result = $client->recv();
        $client->close();
        if ($result == '') {
            echo $client->errMsg;
            return [];
        }
        //涉及中文 不要随意改用 mb
        $result = iconv("gb2312//IGNORE", "utf-8", substr($result, 4));
        $result = json_decode($result, true);
        //var_dump($result);
        echo '飞单返回结果' . $result['msg'] . PHP_EOL;
        $orderCode = $result['msg'];
        if ($result['code'] != 200) {
            return false;
        }
        return true;
    }

    public static function reOrderTape($agentId, $issue, $orderNo)
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check' => true,
            'package_max_length' => 81920,
            'package_length_type' => 'l',
            'package_length_offset' => 0,
            'package_body_offset' => 0,
        ]);
        if (!$client->connect(self::$HOST, self::$PORT)) {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        $data['code'] = self::$CODE_RE_ORDER;
        $data['data']['agentId'] = $agentId;
        $data['data']['issue'] = $issue;
        $data['data']['orderNo'] = $orderNo;

        $data = json_encode($data);

        //涉及中文 不要随意改用 mb
        $len = pack('i', strlen($data) + 4);
        $client->send($len . $data);
        $result = $client->recv();
        $client->close();
        if ($result == '') {
            echo $client->errMsg;
            return [];
        }
        //涉及中文 不要随意改用 mb
        $result = iconv("gb2312//IGNORE", "utf-8", substr($result, 4));
        $result = json_decode($result, true);
        //var_dump($result);
        echo '退单返回结果' . $result['msg'] . PHP_EOL;
        if ($result['code'] != 200) {
            return false;
        }
        return true;
    }

    public static function closeTape($agentId)
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_length_check' => true,
            'package_max_length' => 81920,
            'package_length_type' => 'l',
            'package_length_offset' => 0,
            'package_body_offset' => 0,
        ]);
        if (!$client->connect(self::$HOST, self::$PORT)) {
            echo "connect failed. Error: {$client->errCode}\n";
            return false;
        }
        $data['code'] = self::$CODE_CLOSE;
        $data['data']['agentId'] = $agentId;

        $data = json_encode($data);

        //涉及中文 不要随意改用 mb
        $len = pack('i', strlen($data) + 4);
        $client->send($len . $data);
        $result = $client->recv();
        $client->close();
        if ($result == '') {
            echo $client->errMsg;
            return [];
        }
        //涉及中文 不要随意改用 mb
        $result = iconv("gb2312//IGNORE", "utf-8", substr($result, 4));
        $result = json_decode($result, true);
        //var_dump($result);
        echo '退单返回结果' . $result['msg'] . PHP_EOL;
        if ($result['code'] != 200) {
            return false;
        }
        return true;
    }
}