<?php


namespace app\agent\model\service;

use art\context\Context;
use art\db\Medoo;
use Carbon\Carbon;

/**
 * Class Profit 上下分管理以及盈利查看
 * @package app\agent\controller
 */
class ProfitService
{


    public static function payApplyList($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $result = $medoo->select('points','*',[
            'agent_id'=>$agentInfo['id'],
            'status'=>0,
            'type'=>1,
            'LIMIT'=>[$params['page'],$params['limit']],
            'ORDER'=>['id'=>'DESC']
        ]);
        return $result;
    }

    public static function reBackApplyList($params)
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $result = $medoo->select('points','*',[
            'agent_id'=>$agentInfo['id'],
            'status'=>0,
            'type'=>-1,
            'LIMIT'=>[$params['page'],$params['limit']],
            'ORDER'=>['id'=>'DESC']
        ]);
        return $result;
    }

    public static function passApply($params)
    {
        $medoo = new Medoo();
        $agentInfo = Context::get('authInfo');
        $applyInfo = $medoo->get('points','*',[
            'id'=>$params['id'],
            'agent_id'=>$agentInfo['id'],
            'status'=>0
        ]);
        if (!$applyInfo){
            art_assign(202,'数据异常');
        }
        $applyInfo['type'] == 1?$mark='上分请求处理通过':$mark='下分请求处理通过';
        $applyInfo['quantity'] = $applyInfo['type'] == 1?abs($applyInfo['quantity']):-$applyInfo['quantity'];
        $medoo->beginTransaction();
        try {
            QuantityLogService::push($agentInfo['user_id'],$agentInfo['id'],$applyInfo['quantity'],$mark);
            $pdoDoc = $medoo->update('user_quantity',['quantity[+]'=>$applyInfo['quantity']],[
                'user_id'=>$applyInfo['user_id'],
                'agent_id'=>$agentInfo['id']
            ]);
            if (!$pdoDoc->rowCount()){
                throw new \Exception($pdoDoc->errorInfo());
            }
            $pdoDoc = $medoo->update('points',['status'=>1],[
                'id'=>$params['id'],
                'agent_id'=>$agentInfo['id'],
                'status'=>0
            ]);
            if (!$pdoDoc->rowCount()){
                throw new \Exception('更新错误');
            }
            $medoo->commit();
        }catch (\Exception $e){
            $medoo->rollBack();
            art_assign(202,$e->getMessage());
        }
        return [];
    }

    public static function rejectApply(array $params)
    {
        $medoo = new Medoo();
        $agentInfo = Context::get('authInfo');
        $applyInfo = $medoo->get('points',[
            'id'=>$params['id'],
            'agent_id'=>$agentInfo['id'],
            'status'=>0
        ]);
        if (!$applyInfo){
            art_assign(202,'数据异常');
        }
        $medoo->beginTransaction();
        try {
            $pdoDoc = $medoo->update('points',['status'=>1,'update_time'=>art_d()],[
                'id'=>$params['id'],
                'agent_id'=>$agentInfo['id'],
                'status'=>0
            ]);
            if (!$pdoDoc->rowCount()){
                throw new \Exception('更新错误');
            }
            $medoo->commit();
        }catch (\Exception $e){
            $medoo->rollBack();
            art_assign(202,$e->getMessage());
        }
        return [];
    }

    /**
     * 盈利统计每周
     * @return array
     */
    public static function profitList()
    {
        $agentInfo = Context::get('authInfo');
        $medoo = new Medoo();
        $max = $medoo->max('order',['agent_id'=>$agentInfo['id']],'create_time');
        $min = $medoo->min('order',['agent_id'=>$agentInfo['id']],'create_time');
        $maxCar = Carbon::parse($max,'Asia/Shanghai');
        $minCar = Carbon::parse($min,'Asia/Shanghai');
        $betArt = [];
        $result = [];
        $n=0;
        do{
            $betArt[$n][0] = $minCar->toDateTimeString();
            $minCar->addDays(7);
            //如果第一次匹配或者最后一次匹配 开始时间加7之后已经等于大于了 那么就以结束时间加一天为准。
            $minCar->lt($maxCar)? $betArt[$n][1] = $minCar->toDateTimeString():$betArt[$n][1] = $maxCar->addDay()->toDateTimeString();
            $n+=1;
        }while($minCar->lt($maxCar));
        array_walk($betArt,function ($item,$key,Medoo $medoo) use (&$result,$agentInfo){
            $result[$key]['orderCount'] = $medoo->count('order','id',[
                'agent_id'=>$agentInfo['id'],
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['playCodeCount'] = $medoo->sum('order','play_code_count',[
                'agent_id'=>$agentInfo['id'],
                'status'=>1,
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['quantityCount'] = $medoo->sum('order','quantity',[
                'agent_id'=>$agentInfo['id'],
                'status'=>1,
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['flyQuantityRetCount'] = $medoo->sum('order','fly_quantity_ret',[
                'agent_id'=>$agentInfo['id'],
                'status'=>1,
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['locQuantityRetCount'] = $medoo->sum('order','loc_quantity_ret',[
                'agent_id'=>$agentInfo['id'],
                'status'=>1,
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['timeStartShow'] = date('n-j',strtotime($item[0]));
            $result[$key]['timeStart'] = $item[0];
            $result[$key]['timeEndShow'] = date('n-j',strtotime($item[1]));
            $result[$key]['timeEnd'] = $item[1];
        },$medoo);
        return $result;
    }

    /**
     * 盈利统计每天
     * @param $params
     * @return array
     */
    public static function profitDetailList($params)
    {
        $medoo = new Medoo();
        $agentInfo = Context::get('authInfo');
        $minCar = Carbon::parse($params['timeStart'],'Asia/Shanghai');
        $maxCar = Carbon::parse($params['timeEnd'],'Asia/Shanghai');
        $betArt = [];
        $result = [];
        $n=0;
        do{
            $betArt[$n][0] = $minCar->toDateTimeString();
            $minCar->addDay();
            //如果第一次匹配或者最后一次匹配 开始时间加7之后已经等于大于了 那么就以结束时间加一天为准。
            $minCar->lt($maxCar)? $betArt[$n][1] = $minCar->toDateTimeString():$betArt[$n][1] = $maxCar->addDay()->toDateTimeString();
            $n+=1;
        }while($minCar->lt($maxCar));
        array_walk($betArt,function ($item,$key,Medoo $medoo) use (&$result,$agentInfo){
            $result[$key]['orderCount'] = $medoo->count('order','id',[
                'agent_id'=>$agentInfo['id'],
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['playCodeCount'] = $medoo->sum('order','play_code_count',[
                'agent_id'=>$agentInfo['id'],
                'status'=>1,
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['quantityCount'] = $medoo->sum('order','quantity',[
                'agent_id'=>$agentInfo['id'],
                'status'=>1,
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['flyQuantityRetCount'] = $medoo->sum('order','fly_quantity_ret',[
                'agent_id'=>$agentInfo['id'],
                'status'=>1,
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['locQuantityRetCount'] = $medoo->sum('order','loc_quantity_ret',[
                'agent_id'=>$agentInfo['id'],
                'status'=>1,
                'create_time[<>]'=>[$item[0],$item[1]]
            ]);
            $result[$key]['timeStartShow'] = date('n-j',strtotime($item[0]));
            $result[$key]['timeStart'] = $item[0];
            $result[$key]['timeEndShow'] = date('n-j',strtotime($item[1]));
            $result[$key]['timeEnd'] = $item[1];
        },$medoo);
        return $result;
    }
}