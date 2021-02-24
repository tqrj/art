<?php


namespace app\user\model\service;


use app\traits\Wx;
use art\context\Context;
use art\db\Medoo;
use art\exception\HttpException;

class UserService
{
    /**
     * @param $params
     * @return array|bool|mixed
     */
    public static function auth($params)
    {

        $arr_state = explode('/', base64_decode($params['state']));
        if (count($arr_state) != 2) {
            art_assign(202, 'state参数错误');
        }
        if (empty($params['code'])) {
            art_assign(202, 'code授权错误');
        }
        $result = Wx::getAccessToken($params['code']);
        if (empty($result['access_token'])) {
            art_assign(202, '获取token错误', $result);
        }
        $result = Wx::getUserInfo($result['access_token'], $result['openid']);
        if (empty($result['openid']) or !is_array($result) or count($result) < 8) {
            art_assign(202, '获取用户资料异常');
        }

        $medoo = new Medoo();
        $agentInfo = $medoo->get('agent', ['id', 'code', 'status', 'expire_time'], [
            'code' => $arr_state[1],
            'status' => 1,
            'expire_time[>]' => art_d()
        ]);
        if (empty($agentInfo)) {
            art_assign(202, '未知异常',[],'https://www.baidu.com');
        }
        $userInfo = $medoo->get('user', [
            'id',
            'nickname',
            'status',
            'refresh_token',
            'openid',
            'headimgurl',
            'token'
        ], [
            'openid' => $result['openid'],
        ]);
        if (!empty($userInfo) && $userInfo['status'] != 1) {
            art_assign(202, '用户被封停',[],'https://www.baidu.com');
        }
        $time = art_d();
        $userInfoQuantity = [];
        $medoo->beginTransaction();
        try {
            if (empty($userInfo)) {
                $userInfo = [];
                $userInfo['token'] = art_set_salt(20);
                $userInfo['openid'] = $result['openid'];
                $userInfo['refresh_token'] = '';
                $userInfo['headimgurl'] = $result['headimgurl'];
                $userInfo['create_time'] = $time;
                $userInfo['update_time'] = $time;
                $userInfo['nickname'] = $result['nickname'];
                $userInfo['status'] = 1;
                $pdoDoc = $medoo->insert('user', $userInfo);
                if (!$pdoDoc->rowCount()) {
                    throw new \Exception('添加用户出错');
                }
                $userInfo['id'] = $medoo->id();
                $userInfoQuantity = self::makeUserQuantity($userInfo, $agentInfo);
                $pdoDoc = $medoo->insert('user_quantity', $userInfoQuantity);
                if (!$pdoDoc->rowCount()) {
                    throw new \Exception('添加用户信息出错');
                }
                $userInfoQuantity['id'] = $medoo->id();
            } else {
                $userInfoQuantity = $medoo->get('user_quantity', [
                    'id',
                    'user_id',
                    'agent_id',
                    'quantity',
                    'type',
                    'status'
                ], [
                    'user_id' => $userInfo['id'],
                    'agent_id' => $agentInfo['id'],
                ]);
                if (!empty($userInfoQuantity) && $userInfoQuantity['status'] != 1){
                    throw new \Exception('用户被封停');
                }
                if (empty($userInfoQuantity)) {
                    $userInfoQuantity = self::makeUserQuantity($userInfo, $agentInfo);
                    $pdoDoc = $medoo->insert('user_quantity', $userInfoQuantity);
                    if (!$pdoDoc->rowCount()) {
                        throw new \Exception('添加用户信息出错');
                    }
                    $userInfoQuantity['id'] = $medoo->id();
                }
            }
            if ($userInfo['headimgurl'] !== $result['headimgurl']) {
                $medoo->update('user', ['headimgurl' => $result['headimgurl']], ['id' => $userInfo['id']]);
            }
            $medoo->commit();
        } catch (\Exception $e) {
            $medoo->rollBack();
            art_assign(202, $e->getMessage(),[],'https://www.baidu.com');
        }
        //$userInfo['quantity'] = $userInfoQuantity;
        return 'https://'.$arr_state[0].'/auth?'.base64_encode('token='.$userInfo['token'].'&agent='.$agentInfo['id']);
    }

    private static function makeUserQuantity($userInfo, $agentInfo): array
    {
        $time = art_d();
        $userInfoQuantity['user_id'] = $userInfo['id'];
        $userInfoQuantity['agent_id'] = $agentInfo['id'];
        $userInfoQuantity['type'] = 1;
        $userInfoQuantity['status'] = 1;
        $userInfoQuantity['create_time'] = $time;
        $userInfoQuantity['update_time'] = $time;
        return $userInfoQuantity;
    }

    public static function info($params)
    {
        $medoo = new Medoo();
        $map['token'] = $params['token'];
        $map['agent_id'] = $params['agent_id'];
        $map['u.status'] = [1];
        $map['q.status'] = [1];
        $result = $medoo->get('user(u)',
            ['[><]user_quantity(q)'=>['u.id'=>'user_id']],
            [
                'u.id',
                'nickname',
                'headimgurl',
//                'refresh_token',
                'openid',
                'quantity',
                'q.status',
                'type'
            ],
            $map
        );
        if (!$result) {
            throw new HttpException(202, '账户过期或Token错误');
        }
        return $result;
    }

    public static function pay($params): array
    {
        $userInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $has = $medoo->has('points',[
            'user_id'=>$userInfo['id'],
            'agent_id'=>$userInfo['agent_id'],
            'status'=>0
        ]);
        if ($has){
            art_assign(202,'您有请求还没有处理请耐心等待!');
        }
        $data['user_id'] = $userInfo['id'];
        $data['agent_id'] = $userInfo['agent_id'];
        $data['status']=0;
        $data['create_time'] = art_d();
        $data['type'] = 1;
        $data['quantity'] = $params['quantity'];
        $pdoDoc = $medoo->insert('points',$data);
        if(!$pdoDoc->rowCount()){
            art_assign(202,'上分异常');
        }
        return [];
    }

    public static function reBack($params): array
    {
        $userInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $has = $medoo->has('points',[
            'user_id'=>$userInfo['id'],
            'agent_id'=>$userInfo['agent_id'],
            'status'=>0
        ]);
        if ($has){
            art_assign(202,'您有请求还没有处理请耐心等待!');
        }
        $medoo->beginTransaction();
        try {
            $userQuantityInfo = $medoo->get('user_quantity',[
                'id',
                'quantity'
            ],[
                'user_id'=>$userInfo['id'],
                'agent_id'=>$userInfo['agent_id'],
            ]);
            if (empty($userQuantityInfo)){
                throw new \Exception('积分数据异常');
            }
            if ((float)$userQuantityInfo['quantity'] < $params['quantity']){
                throw new \Exception('下分异常');
            }
            $pdoDoc = $medoo->update('user_quantity',['quantity[-]'=>$params['quantity']],[
                'user_id'=>$userInfo['id'],
                'agent_id'=>$userInfo['agent_id'],
                'quantity'=>(float)$userQuantityInfo['quantity']
            ]);
            if (!$pdoDoc->rowCount()){
                throw new \Exception('下分异常');
            }
            $data['user_id'] = $userInfo['id'];
            $data['agent_id'] = $userInfo['agent_id'];
            $data['status']=0;
            $data['create_time'] = art_d();
            $data['type'] = -1;
            $data['quantity'] = $params['quantity'];
            $pdoDoc = $medoo->insert('points',$data);
            if(!$pdoDoc->rowCount()){
                throw new \Exception('退分异常');
            }
            $medoo->commit();
        }catch (\Exception $e){
            $medoo->rollBack();
            art_assign(202,$e->getMessage());
        }
        return [];
    }

    public static function payList($params)
    {
        $userInfo = Context::get('authInfo');
        $medoo = new Medoo();
        return $medoo->select('points','*',[
            'agent_id'=>$userInfo['agent_id'],
            'user_id'=>$userInfo['id'],
            'type'=>[1,-1],
            'LIMIT'=>[$params['page'],$params['limit']],
            'ORDER'=>['id'=>'DESC']
            ]);
    }

    public static function reBackList($params)
    {
        $userInfo = Context::get('authInfo');
        $medoo = new Medoo();
        return $medoo->select('points','*',[
            'agent_id'=>$userInfo['agent_id'],
            'user_id'=>$userInfo['id'],
            'type'=>-1,
            'LIMIT'=>[$params['page'],$params['limit']],
            'ORDER'=>['id'=>'DESC']
        ]);
    }


    public static function baseConfig($agentId)
    {
//        $userInfo = Context::get('authInfo');
        $medoo = new Medoo();
        return $medoo->get('room',['title','notice_top','notice_help','whether_water','whether_closeInfo'],['agent_id'=>$agentId]);
    }
}