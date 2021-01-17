<?php
/**
 * @CreateTime:   2021/1/3 12:59 下午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  每个延迟队列的配置信息
 */

namespace Huizhang\UniversalQueue\Core;

use EasySwoole\Component\Singleton;
use EasySwoole\Spl\SplBean;
use Huizhang\UniversalQueue\Driver\QueueDriverInterface;

class Queue extends SplBean
{
    use Singleton;

    protected $alias;
    protected $limit = 100;
    protected $consumer;
    protected $driver;
    protected $coroutineNum = 3;
    protected $other = [];

    public function getCoroutineNum()
    {
        return $this->coroutineNum;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getConsumer(): ConsumerAbstract
    {
        return $this->consumer;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function getOther(): array
    {
        return $this->other;
    }

    public function getDriver(): QueueDriverInterface
    {
        return $this->driver;
    }

}
