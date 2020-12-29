<?php


namespace app\admin\controller;


use app\agent\model\service\RoomService;
use art\db\Medoo;
use art\request\Request;
use mysql_xdevapi\Exception;

class Agent
{
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
        $medoo = new Medoo();
        if (!empty($params['keyWord'])) {
            $map['nickname[~]'] = $params['keyWord'] . '%';
        }
//        if (!empty($params['agentId'])){
//            $map['id'] = $params['agentId'];
//        }
        $map['LIMIT'] = [$params['page'], $params['limit']];
        $map['ORDER']=['id'=>'DESC'];
        $map['status'] = [1,0];
        $result = $medoo->select('agent',['id','nickname','status','token','code','create_time','expire_time'],$map);
        array_walk($result,function (&$item) use ($medoo){
            $item['profit'] = $medoo->sum('order','quantity', [
               'agent_id'=>$item['id'],
               'status'=>[1,0]
           ]);
           $item['userCount'] = $medoo->count('user_quantity','id',[
               'agent_id'=>$item['id'],
               'status'=>[1,0]
           ]);
        });
        art_assign(200,'success',$result);
    }

    public function status()
    {
        $params = Request::only(['agentId','status']);
        art_validate($params,['agentId'=>'require|number','status'=>'require|between:-1,1']);
        $medoo = new Medoo();
        $pdoDoc =  $medoo->update('agent',['status'=>$params['status']],['id'=>$params['agentId']]);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'更新失败');
        }
        art_assign(200,'更新成功');
    }

    public function change()
    {
        $params = Request::only([
            'agentId',
            'nickname',
            'pass',
            'code',
            'expire_time'
        ]);
        art_validate($params,[
            'agentId'=>'require|number',
            'nickname|用户名称'=>'length:6,20',
            'pass|密码'=>'length:6,20',
            'code|邀请码'=>'length:4,10',
            'expire_time'=>'length:4,40'
        ]);
        $data = $params;
        unset($data['agentId']);
        $medoo = new Medoo();
        $pdoDoc =  $medoo->update('agent',$data,['id'=>$params['agentId']]);
        if (!$pdoDoc->rowCount()){
            art_assign(202,'更新失败');
        }
        art_assign(200,'更新成功');
    }

    public function add()
    {
        $params = Request::only([
            'nickname',
            'pass',
            'pass_sec',
            'mobile',
            'pay_card',
            'expire_time',
            'closeTime',
            'reTime',
            'notice'=>'',
            'notice_top'=>'',
            'notice_close'=>'',
            'notice_help'=>''
        ]);
        art_validate($params,[
            'nickname|用户名称'=>'require|length:6,20',
            'pass|密码'=>'require|length:6,20',
            'pass_sec|二级密码'=>'require|length:6',
            'mobile|手机号'=>'length:6,11',
            'pay_card|备注信息'=>'length:10,255',
            'expire_time'=>'require',
            'closeTime|封盘时间'=>'require',
            'reTime|退单时间'=>'require',
            'notice|开盘提示'=>'require',
            'notice_top|顶部提示'=>'require',
            'notice_close|封盘信息'=>'require',
            'notice_help|帮助信息'=>'require'
        ]);
        $params['create_time'] = art_d();
        $params['update_time'] = art_d();
        $params['salt'] = art_set_salt();
        $params['pass'] = art_set_password($params['pass'],$params['salt']);
        $params['pass_sec'] = art_set_password($params['pass_sec'],$params['salt']);
        $params['token'] = art_set_salt(20);
        $params['status'] = 1;
        $params['code'] = art_set_salt(5);
        $medoo = new Medoo();
        $has = $medoo->has('agent', ['nickname' => $params['nickname']]);
        if ($has) {
            art_assign(202, '该用户名已注册');
        }
        $medoo->beginTransaction();
        try {
            $result = $medoo->insert('agent', $params);
            if (!$result->rowCount()) {
                throw new \Exception('加入Agent数据失败');
            }
            RoomService::create($medoo->id());
            $medoo->commit();
        }catch (\Exception $e){
            art_assign(202, $e->getMessage());
            $medoo->rollBack();
        }
        return art_assign(200,'添加成功');
    }

}