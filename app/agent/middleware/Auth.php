<?php


namespace app\agent\middleware;


use art\context\Context;
use art\db\Medoo;
use art\db\Redis;
use art\exception\HttpException;
use art\HttpApp;
use art\request\Request;

class Auth
{

    /**
     * @return bool
     */
    public static function hand(): bool
    {
        $passAction = ['sendCode', 'sign', 'login'];
        $action = HttpApp::getActionName();
        if (false !== array_search($action, $passAction)) {
            return true;
        }
        $data = Request::only(['token']);
        if (empty($data['token'])) {
            throw new HttpException(202, '无权限访问');
        }
        $token = $data['token'];
        $redis = Redis::getInstance()->getConnection();
        $authInfo = $redis->get('token_' . $token);
        Redis::getInstance()->close($redis);
        if (false !== $authInfo) {
            Context::put('authInfo', unserialize($authInfo));
            return true;
        }
        $medoo = new Medoo();
        $result = $medoo->get('agent', ['id', 'pass', 'pass_sec','code', 'salt', 'nickname', 'quantity', 'status', 'expire_time',],['token' => $token, 'expire_time[>]' => art_d()]);
        if (!$result) {
            throw new HttpException(202, '账户过期或Token错误');
        }
        $result['expire_time'] = strtotime($result['expire_time']);
        $redis = Redis::getInstance()->getConnection();
        $redis->setex('token_' . $token, 3600, serialize($result));
        Redis::getInstance()->close($redis);
        Context::put('authInfo', $result);
        return true;
    }


}