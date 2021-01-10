<?php
/**
 * @CreateTime:   2021/1/3 1:27 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  延迟队列核心方法
 */
namespace Huizhang\DelayQueue;

use EasySwoole\Component\Singleton;
use EasySwoole\Redis\Redis;
use EasySwoole\Redis\Response;
use EasySwoole\RedisPool\RedisPool;

class Core {

    use Singleton;

    private $scriptSha1;

    public function push(string $redisAlias, string $delayQueueAlias, int $score, string $data)
    {
        return RedisPool::invoke(function (Redis $redis) use ($delayQueueAlias, $score, $data) {
            return $redis->zAdd($delayQueueAlias, $score, $data);
        }, $redisAlias);
    }

    public function pop(string $redisAlias, string $delayQueueAlias, int $score, int $limit): array
    {
        return RedisPool::invoke(function (Redis $redis) use ($delayQueueAlias, $score, $limit) {
            $result = [];
            if (empty($this->scriptSha1)) {
                $script = <<<EOF
local message = redis.call('ZRANGEBYSCORE', KEYS[1], '-inf', ARGV[1], 'LIMIT', 0, {$limit});if #message > 0 then  redis.call('ZREM', KEYS[1], unpack(message));  return message;else  return {};end
EOF;
                $loadResult = $redis->rawCommand(['SCRIPT', 'LOAD', $script]);
                $this->scriptSha1 = $loadResult->getData();
            }
            /** @var $data Response*/
            $data = $redis->rawCommand(['EVALSHA', $this->scriptSha1, 1, $delayQueueAlias, $score]);
            if ($data->getStatus() === 0)
            {
                $result = $data->getData();
            }
            return $result;
        }, $redisAlias);
    }

    public function rem(string $redisAlias, string $delayQueueAlias, string $data)
    {
        return RedisPool::invoke(function (Redis $redis) use ($delayQueueAlias, $data) {
            return $redis->zRem($delayQueueAlias, $data);
        }, $redisAlias);
    }

}
