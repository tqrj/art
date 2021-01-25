<?php


namespace app\admin\controller;


use art\db\Medoo;
use art\request\Request;

class User
{
    private $isHttp = true;

    public function list()
    {
        $params = Request::only([
            'page',
            'limit',
//            'agentId',
            'keyWord'
        ]);
        art_validate($params,[
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
            'keyword'=>'length:1,12'
        ]);
        $params['page'] *= $params['limit'];
        if (!empty($params['keyWord'])) {
            $map['nickname[~]'] = $params['keyWord'] . '%';
        }
//        if (!empty($params['agentId'])){
//            $map['id'] = $params['agentId'];
//        }
        $map['LIMIT'] = [$params['page'], $params['limit']];
        $map['ORDER']=['id'=>'DESC'];
        $map['status'] = [1,0];
        $medoo = new Medoo();
        $result = $medoo->select('user',['id','nickname','status','token','create_time','headimgurl'],$map);
        art_assign(200,'success',$result);
    }

    public function status()
    {
        $params = Request::only(['userId','status']);
        art_validate($params,['userId'=>'require|number','status'=>'require|between:0,1']);
        $medoo = new Medoo();
        $pdoDoc =  $medoo->update('user',['status'=>$params['status']],['id'=>$params['userId']]);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'更新失败');
        }
        art_assign(200,'更新成功');
    }
}