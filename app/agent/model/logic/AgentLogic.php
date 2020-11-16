<?php


namespace app\agent\model\logic;


use art\request\Request;

class AgentLogic
{
    public static function login()
    {
        $params = Request::only(['nickname','pass']);
        art_validate($params,[
            'nickname'=>'require|length:6,20',
            'pass'=>'require|length:6,20',
        ]);
        return $params;
    }

    public static function sign()
    {
        $params = Request::only([
            'nickname',
            'pass',
            'pass_sec',
            'mobile',
            'pay_card',
            'verify_token',
            'verify_DeCode',
        ]);
        art_validate($params,[
            'nickname|用户名称'=>'require|length:6,20',
            'pass|密码'=>'require|length:6,20',
            'pass_sec|二级密码'=>'require|length:6',
            'mobile|手机号'=>'length:6,11',
            'pay_card|支付信息'=>'length:10,255',
            'verify_token'=>'require',
            'verify_DeCode'=>'require',
        ]);
        $params['expire_time'] = art_d();
        $params['create_time'] = $params['expire_time'];
        $params['update_time'] = $params['expire_time'];
        $params['salt'] = art_set_salt();
        $params['pass'] = art_set_password($params['pass'],$params['salt']);
        $params['pass_sec'] = art_set_password($params['pass_sec'],$params['salt']);
        $params['token'] = art_set_salt(20);
        $params['status'] = 1;
        $params['code'] = art_set_salt(5);
        return $params;
    }

    public static function sendCode()
    {

    }

    public static function userInfo()
    {
        $params = Request::only(['token']);
        return $params;
    }

    public static function change()
    {
        $params = Request::only(['pass_sec','token','pass','pay_card','nickname']);
        art_validate($params,[
            'nickname|用户名称'=>'require|length:6,20',
            'pass|密码'=>'require|length:6,20',
            'pass_sec|二级密码'=>'require|length:6',
            'mobile|手机号'=>'length:6,11',
            'pay_card|支付信息'=>'length:10,255',
        ]);
        return $params;
    }
}