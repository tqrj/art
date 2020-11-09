<?php


namespace art\lock;


use art\db\Redis;
use Co\System;

class ArtLock
{
    private string $lockKey;
    private bool $lockStatus = false;
    private int $outTime;
    private int $microTime;

    /**
     * @param string $lockKey
     * @param int $outTime 这个超时是防止redis 死锁没有释放redis ex所设置的超时
     * @return bool
     */
    public function lock(string $lockKey,int $outTime):bool
    {
        if ($this->lockStatus){
            return true;
        }
        $redis = Redis::getInstance()->getConnection();
        if (is_null($redis)){
            return false;
        }
        $this->lockKey = $lockKey;
        do{
            $this->lockStatus = $redis->set('ArtLock' . $this->lockKey, 'lock', ['nx', 'ex' => $outTime]);
            if (false == $this->lockStatus){
                System::sleep(0.02);
            }
        }while(!$this->lockStatus);
        $this->microTime = microtime(true);
        $this->outTime = $outTime;
        Redis::getInstance()->close($redis);
        return $this->lockStatus;
    }

    public function unLock():bool
    {
        if (false == $this->lockStatus){
            return false;
        }
        $redis = Redis::getInstance()->getConnection();
        if (is_null($redis)){
            return false;
        }
        if (microtime(true) - $this->microTime >= $this->outTime){
            Redis::getInstance()->close($redis);
            $this->lockStatus = false;
            return $this->lockStatus;
        };
        //我这种写法唯一缺陷，这里可能会有可能在下一个锁获取到情况出现误删除 这种机率应该很小的~后面如果有需要考虑再换成lua
        //换成lua 上面的超时判断和下面的del放一起~
        $bool = $redis->del('ArtLock' . $this->lockKey);
        Redis::getInstance()->close($redis);
        return $bool;
    }

    private function tryLock()
    {

    }
}