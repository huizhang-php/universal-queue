<?php
/**
 * @CreateTime:   2021/1/3 4:15 下午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  消费者要实现的接口
 */
namespace Huizhang\UniversalQueue\Core;

abstract class ConsumerAbstract {

    /** @var $queue Queue*/
    public $queue;

    abstract public function deal(array $data);

}
