<?php
/**
 * @CreateTime:   2021/1/3 1:27 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  redis延迟队列
 */

namespace Huizhang\UniversalQueue\Driver;

use EasySwoole\Redis\Redis;
use EasySwoole\Redis\Response;
use EasySwoole\RedisPool\RedisPool;
use Huizhang\UniversalQueue\Core\Queue;

class RedisDelayQueue implements QueueDriverInterface
{

    private $scriptSha1;

    public function push(Queue $queue, string $data)
    {
        $other = $queue->getOther();
        return RedisPool::invoke(function (Redis $redis) use ($queue, $data) {
            return $redis->zAdd($queue->getAlias(), time(), $data);
        }, $other['redisAlias']);
    }

    public function pop(Queue $queue): array
    {
        $other = $queue->getOther();
        return RedisPool::invoke(function (Redis $redis) use ($queue) {
            $other = $queue->getOther();
            $result = [];
            if (empty($this->scriptSha1)) {
                $script = <<<EOF
local message = redis.call('ZRANGEBYSCORE', KEYS[1], '-inf', ARGV[1], 'LIMIT', 0, {$queue->getLimit()});if #message > 0 then  redis.call('ZREM', KEYS[1], unpack(message));  return message;else  return {};end
EOF;
                $loadResult = $redis->rawCommand(['SCRIPT', 'LOAD', $script]);
                $this->scriptSha1 = $loadResult->getData();
            }
            /** @var $data Response */
            $data = $redis->rawCommand(['EVALSHA', $this->scriptSha1, 1, $queue->getAlias(), time() - $other['delayTime']]);
            if ($data->getStatus() === 0) {
                $result = $data->getData();
            }
            return $result;
        }, $other['redisAlias']);
    }

}
