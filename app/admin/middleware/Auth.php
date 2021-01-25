<?php


namespace app\admin\middleware;


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
        $passAction = ['login'];
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
        $authInfo = $redis->get('admin_token_' . $token);
        Redis::getInstance()->close($redis);
        if (false !== $authInfo) {
            Context::put('authInfo', unserialize($authInfo));
            return true;
        }
        $medoo = new Medoo();
        $result = $medoo->get('admin', ['id', 'pwd', 'salt', 'nickname',  'status'],['token' => $token]);
        if (!$result) {
            throw new HttpException(202, '账户过期或Token错误');
        }
        $redis = Redis::getInstance()->getConnection();
        $redis->setex('token_' . $token, 3600, serialize($result));
        Redis::getInstance()->close($redis);
        Context::put('authInfo', $result);
        return true;
    }


}