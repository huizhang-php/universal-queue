<?php
/**
 * @CreateTime:   2021/1/3 1:14 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  延迟队列对外暴露的方法
 */

namespace Huizhang\UniversalQueue;

use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use EasySwoole\Component\Singleton;
use Huizhang\UniversalQueue\Core\ConsumerProcess;
use Huizhang\UniversalQueue\Core\Queue;
use Huizhang\UniversalQueue\Exception\UniversalQueueException;
use Swoole\Server;

class UniversalQueue
{
    use Singleton;

    /** @var $config Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $queues = $this->config->getQueues();
        if (empty($queues)) {
            throw new UniversalQueueException('Queues is empty!');
        }

        $this->checkQueues();
    }

    private function checkQueues()
    {
        $queues = $this->config->getQueues();
        /** @var $queue Queue*/
        foreach ($queues as $queue) {
            if ($queue->getCoroutineNum() < 0) {
                throw new UniversalQueueException("The coroutineNum for {$queue->getAlias()} is illegal!");
            }
            if ($queue->getLimit() < 0) {
                throw new UniversalQueueException("The limit for {$queue->getAlias()} is illegal!");
            }
            $class = new \ReflectionClass(get_class($queue->getConsumer()));
            if ('Huizhang\UniversalQueue\Core\ConsumerAbstract' !== $class->getParentClass()->getName()) {
                throw new UniversalQueueException("{$queue->getAlias()} consumers must implement ConsumerInterface!");
            }
        }
    }

    public function attachServer(Server $server)
    {
        /** @var $queue Queue*/
        foreach ($this->config->getQueues() as $queue) {
            $config = new UnixProcessConfig();
            $config->setArg($queue);
            $config->setSocketFile($this->getSock($queue->getAlias()));
            $config->setProcessName("DelayQueue.{$queue->getAlias()}");
            $config->setProcessGroup('DelayQueue');
            $config->setEnableCoroutine(true);
            $server->addProcess((new ConsumerProcess($config))->getProcess());
        }
    }

    private function getSock(string $queueAlias)
    {
        $temp = EASYSWOOLE_ROOT . '/Temp';
        return "{$temp}/DelayQueue.{$queueAlias}.sock";
    }

    public function push(string $queueAlias, string $data)
    {
        $queues = $this->config->getQueues();
        /** @var $queue Queue*/
        $queue = $queues[$queueAlias];
        return $queue->getDriver()->push($queue, $data);
    }

}
