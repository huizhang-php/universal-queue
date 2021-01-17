<?php

namespace Huizhang\UniversalQueue\Driver;

use Huizhang\UniversalQueue\Core\Queue;

/**
 * @CreateTime:   2021/1/17 11:27 下午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  队列驱动接口
 */
interface QueueDriverInterface
{
    public function pop(Queue $queue, int $limit): array;

    public function push(Queue $queue, string $data);
}

