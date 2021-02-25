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

use config\Database;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;

class PDO
{
    protected PDOPool $pools;


    private static $instance;

    private function __construct()
    {
        if (empty($this->pools)) {
            $this->pools = new PDOPool(
                (new PDOConfig())
                    ->withHost(Database::$host)
                    ->withPort(Database::$port)
                    ->withDbName(Database::$dbname)
                    ->withCharset(Database::$charset)
                    ->withUsername(Database::$username)
                    ->withPassword(Database::$password)
                    ->withOptions([\PDO::ATTR_EMULATE_PREPARES=>false]),
                Database::$size
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
