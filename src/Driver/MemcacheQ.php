<?php
/**
 * @CreateTime:   2021/1/23 4:28 下午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  memcacheq driver
 */

namespace Huizhang\UniversalQueue\Driver;

use Huizhang\Memcache\Memcache;
use Huizhang\Memcache\Config;
use Huizhang\UniversalQueue\Core\Queue;

class MemcacheQ implements QueueDriverInterface
{

    public function pop(Queue $queue): array
    {
        $client = $this->getClient($queue);
        $result = [];
        for ($i = 0; $i < $queue->getLimit(); $i++) {
            $res = $client->get($queue->getAlias());
            if (empty($res->getData())) {
                break;
            }
            $result[] = $res->getData()[$queue->getAlias()];
        }
        return $result;
    }

    public function push(Queue $queue, string $data)
    {
        $client = $this->getClient($queue);
        return $client->set($queue->getAlias(), $data);
    }

    private function getClient(Queue $queue)
    {
        $config = new Config();
        $driverConfig = $queue->getDriverConfig();
        $servers = [];
        foreach ($driverConfig['servers'] as $item) {
            $servers[] = explode(':', $item);
        }
        $config->setServers($servers);
        return new Memcache($config);
    }
}

