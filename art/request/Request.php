<?php


namespace art\request;


use art\context\Context;

class Request
{

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    public static function only(array $keys):array
    {
         $request = Context::get('request');
         $post = $request->post;
         $get = $request->get;
         is_null($post)?$post=[]:true;
         is_null($get)?$get=[]:true;
         $params = array_merge($get,$post);
         $result = [];

         array_walk($keys,function ($item,$value) use($params,&$result)
         {
             if (array_key_exists($item,$params)){
                 $result[$item] = $params[$item];
             }elseif(!empty($value)){
                 $result[$item] = $value;
             }
         });
         var_dump($result);
         return $result;
    }

    /**
     * @return \Swoole\Http\Request
     */
    public static function getRequest()
    {
        return Context::get('request');
    }

}