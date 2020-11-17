<?php


namespace app\agent\controller;


use app\BaseController;
use app\traits\Lottery;
use app\traits\Wx;
use art\db\DB;
use art\lock\ArtLock;
use art\ws\ArtWs;
use art\db\Medoo;
use art\request\Request;
use Co\System;

class Test extends BaseController
{

    private $isHttp = true;
//    private $isWs = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function lock()
    {
        $lock = new ArtLock();
        $bool= $lock->lock('dd',10);
        if ($bool == false){
            echo '我没进去'.PHP_EOL;
            art_assign(202,'进来失败了');
            return;
        }
        echo '我进来了';
        System::sleep(9);
        $bool = $lock->unLock();
        if ($bool){
            echo '我出来了'.$bool.PHP_EOL;
        }else{
            echo '我出来失败了'.$bool.PHP_EOL;
        }
        art_assign(200,'ojbk');
    }

    public function test3()
    {
        //https://loiy.net/post/566.html
        //https://medoo.in/api/where
//        $db = new DB();
//        $bool = $db->query("SELECT * FROM vae_test WHERE id = :id",[':id'=>1]);
//        $bool = $db->insert("INSERT INTO vae_test (test) values (:test)",[':test'=>1]);
        $model = new Medoo();
        $bool = $model->get('agent','*',['id'=>[2,3]]);
/*        $bool = $model->select('vae_test',['id','test'],['id'=>[20,21,30]]);

        $bool = $model->debug()->select('vae_test',
            ['id','nickname'],
            ['nickname[~]'=>['我%']
            ]);
        //SELECT `id`,`nickname` FROM `vae_test` WHERE (`nickname` LIKE '我%')
        $bool = $model->debug()->select('vae_test',
            ['[><]vae_user'=>['id']],
            ['vae_test.nickname']
        );
        $bool = $model->debug()->select('vae_test(t)',

            ['[><]vae_user(u)'=>['id']],
            ['t.nickname']
        );//别名 用括号括起来声明
        //SELECT `vae_test`.`nickname` FROM `vae_test` INNER JOIN `vae_user` USING (`id`)*/

        art_assign(200,'success',$bool);
    }

    public function test()
    {
        $params = Request::only(['code']);
        if (empty($params['code'])){
            art_assign(202,'code授权错误');
        }
        $result = Wx::getAccessToken($params['code']);
        if (empty($result['access_token'])){
            art_assign(202,'获取token错误',$result);
        }
        $result = Wx::getUserInfo($result['access_token'],$result['openid']);
        art_assign(200,'success',$result,'http://wailitoo.com/');
    }

    public function test4()
    {
        art_assign(200,'success',[],'http://wailitoo.com/');
    }

    /**
     *
     */
    public function hello()
    {
        $result = Lottery::getCode(Lottery::LOTTERY_TYPE_OLD);
        art_assign(200,'success',$result);
    }

    public function hello2()
    {
        $result = Lottery::getCode(Lottery::LOTTERY_TYPE_now);
        art_assign(200,'success',$result);
    }

    /**
     *
     */
    public function hello1()
    {
/*        $str = [];
        $str[] = '单10';
        $str[] = '12345-12345-12345-12369-2580/0.1';
        $str[] = '万23456千23456除各1';
        $str = $str[mt_rand(0,2)];*/
        $str = Request::only(['code'])['code'];
        $result = Lottery::parseExp($str);
        art_assign(200,$result);
    }

    public function test1()
    {
        $msg = $this->frame->data['msg'];
        ArtWs::joinGroup($this->response->artWsId,'test1');
        art_assign(200,$msg,[],'',0,0,'test1');
    }

    public function test2()
    {
        $msg = $this->frame->data['msg'];
        ArtWs::joinGroup($this->response->artWsId,'test2');
        art_assign(200,$msg,[],'',0,0,'test2');
    }
}