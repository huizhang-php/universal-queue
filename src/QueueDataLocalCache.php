<?php
/**
 * @CreateTime:   2021/1/17 1:28 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  本地缓存,将从队列中读取的数据先落盘，防止取出后的各种异常情况导致的数据丢失
 */

namespace Huizhang\DelayQueue;

use EasySwoole\Component\Singleton;

class QueueDataLocalCache
{
    use Singleton;

    public function write(string $file, array $rows)
    {
        $resource = fopen($file, 'a');
        if (flock($resource, LOCK_EX)) {
            foreach ($rows as $row) {
                fwrite($resource, $row . PHP_EOL);
                flock($resource, LOCK_UN);
            }
        }
    }

    public function read(string $file, int $size): array
    {
        $resource = fopen($file, 'a+');
        $content = file_get_contents($resource);
        $rows = explode("\n", $content);
        $res = array_slice($rows, 0, $size);
        if (empty($res)) {
            return $res;
        }
        return [];
    }

    public function rem(string $file, int $size)
    {
        $resource = fopen($file, 'a+');
        $content = file_get_contents($resource);
        $rows = explode("\n", $content);
        $surplus = array_slice($rows, $size - 1);
        if (!empty($surplus)) {
            file_put_contents($resource, implode("\n", $surplus));
        }
    }

    public function mergeAtoBAndUnlinkA(string $fileA, string $fileB)
    {
        if (file_exists($fileA) && file_exists($fileB)) {
            $data = $this->read($fileA, 99999999999);
            $this->write($fileB, $data);
            unlink($fileA);
        }
    }

}
