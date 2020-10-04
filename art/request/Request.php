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
         $params = [];
         if (!empty($post) && !empty($get)){
             $params = array_merge($get,$post);
         }elseif (!empty($post)){
             $params = $post;
         }elseif(!empty($get)){
             $params = $get;
         }
         $result = [];
         if (empty($params)){
             return $result;
         }
         array_walk($keys,function ($item) use($params,&$result)
         {
             if (array_key_exists($item,$params)){
                 $result[$item] = $params[$item];
             }
         });
         return $result;
    }

}