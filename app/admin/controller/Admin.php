<?php


namespace app\admin\controller;


use art\db\Medoo;
use art\request\Request;

class Admin
{
    public function login()
    {
        $params = Request::only([
            'username',
            'pwd'
        ]);
        art_validate($params,[
            'username'=>'require',
            'pwd'=>'require'
        ]);
        $medoo = new Medoo();
        $adminInfo = $medoo->get('admin',['id','username','pwd','salt','token','status'],[
            'username'=>$params['username'],
            'status'=>1
        ]);
        if (art_set_password($params['pwd'],$adminInfo['salt']) != $adminInfo['pwd']){
            art_assign(202,'账户或密码错误');
        }
        unset($adminInfo['salt'],$adminInfo['pwd']);
        art_assign(200,'success',$adminInfo);
    }

    public function change()
    {
        $params = Request::only(['']);
    }


}