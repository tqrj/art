<?php


namespace app\user\controller;


use app\user\model\logic\PostLogic;
use app\user\model\service\PostService;

class Post
{
    private $isHttp = true;
    /**
     * 注单列表
     */
    public function posts()
    {
        $params = PostLogic::posts();
        $result = PostService::posts($params);
        art_assign(200,'success',$result);
    }

    public function postBack()
    {

    }
}