<?php


namespace app\agent\middleware;


use art\db\Medoo;
use art\exception\HttpException;

class Auth
{

    public static function Auth($token):bool
    {

        $medoo = new Medoo();
        $result = $medoo->get('agent',['id','token','status','expire_time'],
            [
                'token'=>$token,
                'expire_time[<]'=>art_d()
            ]);
        if (!$result){
            throw new HttpException(202,'无权限访问');
        }
        return true;
    }


}