<?php


namespace app\admin\controller;


use art\db\Medoo;
use art\request\Request;

class WebSite
{
    public function list()
    {
        $params = Request::only([
            'page',
            'limit',
//            'agentId',
//            'keyWord'
        ]);
        art_validate($params,[
            'limit'=>'require|between:5,50',
            'page'=>'require|between:0,999',
//            'keyword'=>'length:1,12'
        ]);
        $params['page'] *= $params['limit'];
        $map['LIMIT'] = [$params['page'], $params['limit']];
        $map['ORDER']=['id'=>'DESC'];
        $map['status'] = [1,0];
        $medoo = new Medoo();
        $result = $medoo->select('website','*',$map);
        art_assign(200,'success',$result);
    }

    public function change()
    {
        $params = Request::only(['id','title','status']);
        art_validate($params,[
            'id'=>'require|number',
            'title'=>'require',
            'status'=>'require|between:-1,1'
        ]);
        $medoo = new Medoo();
        $pdoDoc = $medoo->update('website',$params,['id'=>$params['id']]);
        if(!$pdoDoc->rowCount()){
            art_assign(202,'更新失败');
        }
        art_assign(200,'更新成功');
    }

    public function add()
    {
        $params = Request::only(['title','status']);
        art_validate($params,['title'=>'require','status'=>'require']);
        $medoo = new Medoo();
        $pdoDoc = $medoo->insert('website',$params);
        if(!$pdoDoc->rowCount()){
            art_assign(202,'添加失败');
        }
        art_assign(200,'添加成功');
    }

}