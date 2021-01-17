<?php
/**
 * @CreateTime:   2021/1/3 1:21 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  消费进程
 */

namespace Huizhang\UniversalQueue\Core;

use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use Huizhang\UniversalQueue\Unit\QueueDataCache;
use Swoole\Coroutine;
use Swoole\Coroutine\Socket;

class ConsumerProcess extends AbstractUnixProcess
{

    public function __construct(UnixProcessConfig $config)
    {
        /** @var $queue Queue */
        $queue = $config->getArg();
        QueueDataCache::getInstance()->init($queue);
        parent::__construct($config);
    }

    public function run($arg)
    {
        /** @var $queue Queue */
        $queue = $arg;
        $queue->getConsumer()->init();
        for ($i = 0; $i < $queue->getCoroutineNum(); $i++) {
            $queue->getConsumer()->queue = $queue;
            Coroutine::create(function () use ($queue, $i) {
                $cacheFile = QueueDataCache::getCacheFile($queue->getAlias(), $i);
                while (true) {
                    try {
                        $data = QueueDataCache::read($cacheFile, $queue->getLimit());
                        if (empty($data)) {
                            $data = $queue->getDriver()->pop($queue);
                        }

                        QueueDataCache::write($cacheFile, $data);

                        if (!empty($data)) {
                            $queue->getConsumer()->deal($data);
                            QueueDataCache::rem($cacheFile, count($data));
                        }
                    } catch (\Throwable $e) {
                        break;
                    }
                    Coroutine::sleep(0.01);
                }
            });
        }
        return parent::run($arg); // TODO: Change the autogenerated stub
    }

    function onAccept(Socket $socket)
    {
        // TODO: Implement onAccept() method.
    }
}
