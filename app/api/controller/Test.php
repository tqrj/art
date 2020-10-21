<?php


namespace app\api\controller;


use app\BaseController;
use art\db\DB;

class Test extends BaseController
{
    public function hello()
    {
//        $params = Request::only(['pp','cc']);
//        art_validate($params,[
//            'cc'=>'require|mobile'
//        ]);
        $db = new  DB();
        $result = $db->query('SELECT * FROM vae_test');
        art_assign(200,'',$result);

    }
}