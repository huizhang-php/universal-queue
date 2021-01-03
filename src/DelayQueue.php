<?php
/**
 * @CreateTime:   2021/1/3 1:14 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  延迟队列
 */

namespace Huizhang\DelayQueue;

use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use EasySwoole\Component\Singleton;
use Swoole\Server;

class DelayQueue
{
    use Singleton;

    /** @var $config Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $queues = $this->config->getQueues();
        if (empty($queues)) {
            throw new \DelayQueueException('Queues is empty!');
        }

        if (!($this->config->getRedisClient() instanceof RedisConnInterface)) {
            $this->config->setRedisClient(new DefaultRedisClient());
        }
    }

    public function attachServer(Server $server)
    {
        $config = new UnixProcessConfig();
        $config->setArg($this->config->getQueues());
        $config->setSocketFile($this->getSock('test', 0));
        $config->setProcessName('DelayQueue');
        $config->setProcessGroup('DelayQueue');
        $config->setEnableCoroutine(true);
        $server->addProcess((new ConsumerProcess($config))->getProcess());
    }

    private function getSock(string $queueAlias, $i)
    {
        return "{$this->config->getSockDIR()}/DelayQueue.{$queueAlias}.{$i}.sock";
    }

    public function push(string $alias, string $data)
    {
        $queues = $this->config->getQueues();
        /** @var $queue Queue */
        $queue = $queues[$alias];
        return Core::getInstance()->push($queue->getRedisAlias(), $queue->getAlias(), time(), $data);
    }

    public function rem(string $alias, string $data)
    {
        $queues = $this->config->getQueues();
        /** @var $queue Queue */
        $queue = $queues[$alias];
        return Core::getInstance()->rem($queue->getRedisAlias(), $queue->getAlias(), $data);
    }

}
