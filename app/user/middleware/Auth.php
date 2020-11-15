<?php


namespace app\user\middleware;


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
    public static function hand():bool
    {
        $passAction = [
            'login',
            'sign',
            'hello2',
            'hello'
        ];
        $action = HttpApp::getActionName();
        if (false !== array_search($action,$passAction)){
            return true;
        }

        $data = Request::only(['token'=>'qwqwq']);
        if (empty($data['token'])){
            throw new HttpException(202,'无权限访问');
        }
        $token = $data['token'];
        $redis  = Redis::getInstance()->getConnection();
        $bool = $redis->get('token_'.$token);
        Redis::getInstance()->close($redis);
        if ($bool){
            return true;
        }
        $medoo = new Medoo();
        $result = $medoo->has('vae_agent',
            [
                'token'=>$token,
                'expire_time[<]'=>art_d()
            ]);
        if (!$result){
            throw new HttpException(202,'无权限访问');
        }
        $redis  = Redis::getInstance()->getConnection();
        $redis->setex('token_'.$token,3600,'true');
        Redis::getInstance()->close($redis);
        return true;
    }
}