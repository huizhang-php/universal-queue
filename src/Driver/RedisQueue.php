<?php
/**
 * @CreateTime:   2021/1/18 1:42 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  Redis队列驱动
 */

namespace Huizhang\UniversalQueue\Driver;

use EasySwoole\Redis\Redis;
use EasySwoole\Redis\Redis as Connection;
use EasySwoole\Redis\Response;
use EasySwoole\RedisPool\RedisPool;
use Huizhang\UniversalQueue\Core\Queue;

class RedisQueue implements QueueDriverInterface
{

    private $scriptSha1;

    public function pop(Queue $queue): array
    {
        $other = $queue->getOther();
        return RedisPool::invoke(function (Redis $redis) use ($queue) {
            $result = [];
            if (empty($this->scriptSha1)) {
                $script = <<<EOF
local message = redis.call('LRANGE', '{$queue->getAlias()}', 0, {$queue->getLimit()});if #message > 0 then  redis.call('LTRIM', '{$queue->getAlias()}', #message, -1);  return message;else  return {};end
EOF;
                $loadResult = $redis->rawCommand(['SCRIPT', 'LOAD', $script]);
                $this->scriptSha1 = $loadResult->getData();
            }
            /** @var $data Response */
            $data = $redis->rawCommand(['EVALSHA', $this->scriptSha1, 0]);
            if ($data->getStatus() === 0) {
                $result = $data->getData();
            }
            return $result;
        }, $other['redisAlias']);
    }

    public function push(Queue $queue, string $data)
    {
        $other = $queue->getOther();
        return RedisPool::invoke(function (Connection $connection) use ($queue, $data) {
            return $connection->lPush($queue->getAlias(), $data);
        }, $other['redisAlias']);
    }
}
