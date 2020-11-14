<?php


namespace app\agent\model\service;


use art\db\Medoo;
use art\db\Redis;

class AgentService
{


    public static function login($params)
    {
        $medoo = new Medoo();
        $userInfo = $medoo->get('agent',[
            'id',
            'nickname',
            'pass',
            'salt',
            'token',
            'expire_time',
            'status',
            'quantity'
        ],['nickname'=>$params['nickname']]);
        if (empty($userInfo)){
            art_assign(202,'用户信息错误');
        }
        if (art_set_password($params['pass'],$userInfo['salt']) != $userInfo['pass']){
            art_assign(202,'账号或密码错误');
        }
        if (strtotime($userInfo['expire_time']) < time()){
            art_assign(202,'账号已过期');
        }
        if ($userInfo['status'] ==0){
            art_assign(202,'账号已禁用');
        }
        unset($userInfo['salt']);
        return $userInfo;
    }

    public static function sign($params)
    {
        $redis = Redis::getInstance()->getConnection();
        if (is_null($redis)) {
            art_assign(202, '未知异常');
        }
        $medoo = new Medoo();
        $deCode = $redis->get('verify_' . $params['verify_token']);
        if ($deCode == '' or $params['verify_DeCode'] != $deCode) {
            Redis::getInstance()->close($redis);
            art_assign(202, '验证码错误');
        }
        $redis->del('verify_' . $params['verify_DeCode']);
        Redis::getInstance()->close($redis);
        unset($params['verify_DeCode'], $params['verify_token']);
        $has = $medoo->has('agent', ['nickname' => $params['nickname']]);
        if ($has) {
            art_assign(202, '该用户名已注册');
        }
        $result = $medoo->insert('agent', $params);
        if (!$result) {
            art_assign(202, '注册失败');
        }
        return [];
    }

    public static function sendCode()
    {
        $redis = Redis::getInstance()->getConnection();
        if (is_null($redis)) {
            art_assign(202, '未知异常');
        }
        $data = art_verify();
        $data['verify_token'] = art_set_salt(10);
        $redis->set('verify_' . $data['verify_token'], $data['deCode'], 60);
        unset($data['deCode']);
        Redis::getInstance()->close($redis);
        return $data;
    }

    public static function userInfo($params)
    {
        $medoo = new Medoo();
        $userInfo = $medoo->get('agent',['*'],['token'=>$params['token']]);
        unset($userInfo['salt']);
        art_assign(200,'success',$userInfo);
    }

    public static function change($params)
    {

    }
}