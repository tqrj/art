<?php


namespace app\user\controller;


use app\BaseController;
use app\traits\Lottery;
use app\traits\Wx;
use art\ws\ArtWs;
use art\db\Medoo;
use art\request\Request;
use Swoole\Coroutine\Client;

class Test extends BaseController
{

    private $isHttp = true;
//    private $isWs = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function test3()
    {
        //https://loiy.net/post/566.html
        //https://medoo.in/api/where
//        $db = new DB();
//        $bool = $db->query("SELECT * FROM vae_test WHERE id = :id",[':id'=>1]);
//        $bool = $db->insert("INSERT INTO vae_test (test) values (:test)",[':test'=>1]);
        $model = new Medoo();
//        $bool = $model->select('vae_test',['id','test'],['id'=>[20,21,30]]);

/*        $bool = $model->debug()->select('vae_test',
            ['id','nickname'],
            ['nickname[~]'=>['我%']
            ]);
        //SELECT `id`,`nickname` FROM `vae_test` WHERE (`nickname` LIKE '我%')*/
/*        $bool = $model->debug()->select('vae_test',
            ['[><]vae_user'=>['id']],
            ['vae_test.nickname']
        );*/
/*        $bool = $model->debug()->select('vae_test(t)',
            ['[><]vae_user(u)'=>['id']],
            ['t.nickname']
        );//别名 用括号括起来声明*/
        //SELECT `vae_test`.`nickname` FROM `vae_test` INNER JOIN `vae_user` USING (`id`)

        art_assign(200,'success',$bool);
    }

    public function test()
    {
        $params = Request::only(['code']);
        if (empty($params['code'])){
            art_assign(202,'code授权错误');
            return;
        }
        $result = Wx::getAccessToken($params['code']);
        if (empty($result['access_token'])){
            art_assign(202,'获取token错误',$result);
            return;
        }
        $result = Wx::getUserInfo($result['access_token'],$result['openid']);
        art_assign(200,'success',$result);
    }

    /**
     *
     */
    public function hello()
    {
        $result = Lottery::getCode(Lottery::LOTTERY_TYPE_OLD);
        art_assign(200,$result);
    }
    public function hello2()
    {
        $result = Lottery::getCode(Lottery::LOTTERY_TYPE_now);
        art_assign(200,$result);
    }

    /**
     *
     */
    public function hello1()
    {
        $str = [];
        $str[] = '单10';
        $str[] = '12345-12345-12345-12369-2580/0.1';
        $str[] = '万23456千23456除各1';
        $str = $str[mt_rand(0,2)];
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