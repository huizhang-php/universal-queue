<?php
/**
 * @CreateTime:   2021/1/3 1:20 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  默认redis
 */
namespace Huizhang\DelayQueue;

use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

class DefaultRedisClient implements RedisConnInterface {

    public  function getClient(string $alias): Redis
    {
        // TODO: Implement getConn() method.
        return RedisPool::defer($alias);
    }

}

