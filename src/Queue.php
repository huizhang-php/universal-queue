<?php
/**
 * @CreateTime:   2021/1/3 12:59 下午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  每个延迟队列的配置信息
 */
namespace Huizhang\DelayQueue;

use EasySwoole\Spl\SplBean;

class Queue extends SplBean
{

    protected $alias;
    protected $redisAlias;
    protected $limit;
    protected $class;
    protected $delayTime;
    protected $coroutineNum;

    public function getRedisAlias(): string
    {
        return $this->redisAlias;
    }

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

    public function getClass()
    {
        return $this->class;
    }

    public function getDelayTime()
    {
        return $this->delayTime;
    }

}
