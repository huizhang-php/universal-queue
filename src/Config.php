<?php
/**
 * @CreateTime:   2021/1/3 1:18 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  延迟队列所需配置
 */

namespace Huizhang\DelayQueue;

use EasySwoole\Component\Singleton;
use EasySwoole\Spl\SplBean;

class Config extends SplBean
{

    use Singleton;

    private $queues;
    private $redisClient;
    private $sockDIR;

    protected function initialize(): void
    {
        if (empty($this->sockDIR)) {
            $this->sockDIR = sys_get_temp_dir();
        }
    }

    public function getQueues()
    {
        return $this->queues;
    }

    public function setQueues(array $queues): self
    {
        foreach ($queues as $alias => $queue) {
            $queue['alias'] = $alias;
            $this->queues[$queue['alias']] = new Queue($queue);
        }
        return $this;
    }

    public function getRedisClient(): ?RedisConnInterface
    {
        return $this->redisClient;
    }

    public function setRedisClient(RedisConnInterface $redisClient): void
    {
        $this->redisClient = $redisClient;
    }

    public function getSockDIR(): string
    {
        return $this->sockDIR;
    }

    public function setSockDIR(string $sockDIR): void
    {
        $this->sockDIR = $sockDIR;
    }

}
