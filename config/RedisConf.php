<?php


namespace config;


class RedisConf
{
    /** @var string */
    public static $host = '127.0.0.1';

    /** @var int */
    public static $port = 6379;

    /** @var int */
    public static $dbIndex = 1;

    /** @var string */
    public static $auth = '';

    public static $timeOut = 1;

    public static $size = 64;
}