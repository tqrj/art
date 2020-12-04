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

    public static function only(array $keys): array
    {
        $request = Context::get('request');
        $frame = Context::get('frame');
        $post = $request->post;
        $get = $request->get;
        $wsData = !is_null($frame)?$wsData = $frame->data:true;
        !is_array($wsData) ? $wsData = [] : true;
        is_null($post) ? $post = [] : true;
        is_null($get) ? $get = [] : true;
        $params = array_merge($get, $post,$wsData);
        $result = [];
        array_walk($keys, function ($item, $key) use ($params, &$result) {
            if (is_int($key) && array_key_exists($item, $params)) {
                $result[$item] = $params[$item];
            } elseif (!is_int($key)) {
                array_key_exists($key, $params)?$result[$key] = $params[$key]:$result[$key] = $item;
            }
        });
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