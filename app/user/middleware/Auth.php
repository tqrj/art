<?php


namespace app\user\middleware;


use art\context\Context;
use art\db\Medoo;
use art\db\Redis;
use art\exception\HttpException;
use art\HttpApp;
use art\request\Request;
use Swoole\Http\Response;

class Auth
{

    /**
     * @return bool
     */
    public static function hand(): bool
    {
        $passAction = ['auth'];
        $action = HttpApp::getActionName();
        if (false !== array_search($action, $passAction)) {
            return true;
        }
        $data = Request::only(['token','agent_id']);
        if (empty($data['token']) or empty($data['agent_id'])) {
            throw new HttpException(202, '无权限访问',[]);
        }
        $token = $data['token'];
        $agent_id = $data['agent_id'];
        $redis = Redis::getInstance()->getConnection();
        $authInfo = $redis->get('user_' . $token.'_'.$agent_id);
        Redis::getInstance()->close($redis);
        if (false !== $authInfo) {
            Context::put('authInfo', unserialize($authInfo));
            return true;
        }
        $medoo = new Medoo();
        $map['token'] = $token;
        $map['agent_id'] = $agent_id;
        $map['u.status'] = [1];
        $map['q.status'] = [1];
        $map['expire_time[>]']= art_d();
        $result = $medoo->get('user(u)',
            [
                '[><]user_quantity(q)'=>['u.id'=>'user_id'],
                '[><]agent(a)'=>['agent_id'=>'a.id'],
            ],
            [
                'u.id',
                'nickname',
                'headimgurl',
                'refresh_token',
                'openid',
                'agent_id',
            ],
            $map
            );
        if (!$result) {
            throw new HttpException(202, '账户过期或Token错误');
        }
        $redis = Redis::getInstance()->getConnection();
        $redis->setex('user_' . $token.'_'.$agent_id, 120, serialize($result));
        Redis::getInstance()->close($redis);
        Context::put('authInfo', $result);
        return true;
    }
}