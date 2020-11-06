<?php


namespace app\traits;



use Swoole\Coroutine\Http\Client;

trait Wx
{
    public static $appID = 'wx501e585371b26022';
    public static $appSecret = 'e5f7856b13ef5ff01313d11ffaed65df';

    /**
     * @param string $code
     * @return mixed|null
     */
    public static function getAccessToken(string $code)
    {
        $client = new Client('api.weixin.qq.com',80,true);
        $client->get("/sns/oauth2/access_token?appid=".self::$appID."&secret=".self::$appSecret."&code=".$code."&grant_type=authorization_code");
        $client->close();
        if ($client->statusCode != 200){
            return null;
        }
        $result = json_decode($client->body,true);
        return $result;
    }

    /**
     * @param string $refresh_token
     * @return mixed|null
     */
    public static function refreshToken(string $refresh_token)
    {
        $client = new Client('api.weixin.qq.com',80,true);
        $url = "/sns/oauth2/refresh_token?appid=".self::$appID."&grant_type=refresh_token&refresh_token=".$refresh_token;
        $client->get($url);
        $client->close();
        if ($client->statusCode != 200){
            return null;
        }
        $result = json_decode($client->body,true);
        return $result;
    }


    /**
     * @param string $accessToken
     * @param string $OPENID
     * @return mixed|null
     */
    public static function getUserInfo(string $accessToken,string $OPENID)
    {
        $client = new Client('api.weixin.qq.com',80,true);
        $url = "/sns/userinfo?access_token=".$accessToken."&openid=".$OPENID."&lang=zh_CN";
        $client->get($url);
        $client->close();
        if ($client->statusCode != 200){
            return null;
        }
        $result = json_decode($client->body,true);
        return $result;
    }
}