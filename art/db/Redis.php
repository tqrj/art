<?php

declare(strict_types=1);
/**
 * This file is part of Simps.
 *
 * @link     https://simps.io
 * @document https://doc.simps.io
 * @license  https://github.com/simple-swoole/simps/blob/master/LICENSE
 */
namespace art\db;

use config\RedisConf;
use RuntimeException;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

class Redis
{
    protected $pools;

    private static $instance;

    private function __construct()
    {
        if (empty($this->pools)) {
            //$this->config = array_replace_recursive($this->config, $config);
            $this->pools = new RedisPool(
                (new RedisConfig())
                    ->withHost(RedisConf::$host)
                    ->withPort(RedisConf::$port)
                    ->withAuth(RedisConf::$auth)
                    ->withDbIndex(RedisConf::$dbIndex)
                    ->withTimeout(RedisConf::$timeOut),
                RedisConf::$size
            );
        }
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pools->get();
    }

    public function close($connection = null)
    {
        $this->pools->put($connection);
    }

}
