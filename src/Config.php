<?php
/**
 * @CreateTime:   2021/1/3 1:18 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  延迟队列所需配置
 */

namespace Huizhang\UniversalQueue;

use EasySwoole\Component\Singleton;
use EasySwoole\Spl\SplBean;
use Huizhang\UniversalQueue\Core\Queue;

class Config extends SplBean
{

    use Singleton;

    private $queues;
    private $tempDir = EASYSWOOLE_ROOT . '/Temp/';

    public function getTempDir()
    {
        return $this->tempDir;
    }

    public function setTempDir(string $tempDir)
    {
        $this->tempDir = $tempDir;
        return $this;
    }

    public function getQueues()
    {
        return $this->queues;
    }

    public function setQueues(array $queues): self
    {
        $number = 0;
        foreach ($queues as $alias => $queue) {
            $queue['alias'] = $alias;
            $this->queues[$queue['alias']] = new Queue($queue);
            ++$number;
        }
        return $this;
    }

}
