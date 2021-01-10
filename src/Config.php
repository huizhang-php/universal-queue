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
    private $mem='1024M';

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

    public function getMem(): string
    {
        return $this->mem;
    }

    public function setMem(string $mem): self
    {
        $this->mem = $mem;
        return $this;
    }

}
