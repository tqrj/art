<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace art\exception;

use art\context\Context;
use Exception;

/**
 * HTTP异常
 */
class HttpException extends \RuntimeException
{
    private $statusCode;
    private $data;
    private $location;
    private $selfWsId;
    private $recvId;
    private $wsGroup;

    public function __construct(int $statusCode, string $message = '',$data = [],string $location = '',int $selfWsId = 0, int $recvId = 0, string $wsGroup = '', Exception $previous = null)
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->location = $location;
        $this->selfWsId = $selfWsId;
        $this->recvId = $recvId;
        $this->wsGroup = $wsGroup;

        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getSelfWsId()
    {
        return $this->selfWsId;
    }

    public function getRecvId()
    {
        return $this->recvId;
    }

    public function getWsGroup()
    {
        return $this->wsGroup;
    }

}
