<?php


namespace app\agent\middleware;


use art\db\Medoo;
use art\db\Redis;
use art\exception\HttpException;
use art\request\Request;

class Auth
{

    public static function hand():bool
    {
        $token = Request::only(['token'=>'q'])['token'];
        if (empty($token)){
            throw new HttpException(202,'无权限访问');
        }
        $redis  = Redis::getInstance()->getConnection();
        $bool = $redis->get('token_'.$token);
        Redis::getInstance()->close($redis);
        if (!is_null($bool)){
            return true;
        }
        $medoo = new Medoo();
        $result = $medoo->has('agent',['id','token','status','expire_time'],
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