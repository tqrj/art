<?php


namespace app\agent\controller;

use app\agent\model\logic\ProfitLogic;
use app\agent\model\service\ProfitService;

/**
 * Class Profit 上下分管理以及盈利查看
 * @package app\agent\controller
 */
class Profit
{
    private $isHttp = true;

    public function payApplyList()
    {
        $params = ProfitLogic::payApplyList();
        $result = ProfitService::payApplyList($params);
        art_assign(200,'success',$result);
    }

    public function reBackApplyList()
    {
        $params = ProfitLogic::reBackApplyList();
        $result = ProfitService::reBackApplyList($params);
        art_assign(200,'success',$result);
    }

    public function passApply()
    {
        $params = ProfitLogic::passApply();
        $result = ProfitService::passApply($params);
        art_assign(200,'success',$result);
    }

    public function rejectApply()
    {
        $params = ProfitLogic::rejectApply();
        $result = ProfitService::rejectApply($params);
        art_assign(200,'success',$result);
    }

    public function profitList()
    {
        $result = ProfitService::profitList();
        art_assign(200,'success',$result);
    }

    public function profitDetailList()
    {
        $params = ProfitLogic::profitDetailList();
        $result = ProfitService::profitDetailList($params);
        art_assign(200,'success',$result);
    }


}