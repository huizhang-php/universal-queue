<?php
/**
 * @CreateTime:   2021/1/17 1:28 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  本地缓存,将从队列中读取的数据先落盘，防止取出后的各种异常情况导致的数据丢失
 */

namespace Huizhang\UniversalQueue\Unit;

use Huizhang\UniversalQueue\Config;
use Huizhang\UniversalQueue\Core\Queue;

class QueueDataCache
{
    public static function init(Queue $queue)
    {
        self::mkdir(QueueDataCache::getCacheDir($queue->getAlias()));
        self::mkdir(QueueDataCache::getLogDir($queue->getAlias()));
        self::createFile($queue);
        self::mergeCannotConsumedFile($queue);
    }

    private static function createFile(Queue $queue)
    {
        for ($i = 0; $i < $queue->getCoroutineNum(); $i++) {
            $file = QueueDataCache::getCoroutineCacheFile($queue->getAlias(), $i);
            if (file_exists($file)) {
                fclose(fopen($file, 'a+'));
            }
        }
    }

    private static function mergeCannotConsumedFile(Queue $queue)
    {
        $targetCacheFile = QueueDataCache::getCoroutineCacheFile($queue->getAlias(), 0);
        $number = $queue->getCoroutineNum();
        while (true) {
            $currentCachefile = QueueDataCache::getCoroutineCacheFile($queue->getAlias(), $number);
            if (!file_exists($currentCachefile)) {
                break;
            }
            QueueDataCache::mergeAtoBAndUnlinkA($currentCachefile, $targetCacheFile);
            ++$number;
        }
    }

    public static function mkdir($dir)
    {
        if (!(is_dir($dir) || @mkdir($dir, 0777))) {
            $dirArr = explode('/', $dir);
            array_pop($dirArr);
            $newDir = implode('/', $dirArr);
            self::mkdir($newDir);
            @mkdir($dir, 0777);
        }
    }

    public static function write(string $file, array $rows)
    {
        $resource = fopen($file, 'a+');
        flock($resource, LOCK_EX);
        foreach ($rows as $row) {
            if (empty($row)) {
                continue;
            }
            fwrite($resource, trim($row) . PHP_EOL);
        }
        flock($resource, LOCK_UN);
    }

    public static function read(string $file, int $size): array
    {
        $resource = fopen($file, 'a+');
        $rows = [];
        while (!feof($resource)) {
            $row = fgets($resource);
            if (empty($row)) {
                continue;
            }
            $rows[] = $row;
        }
        $res = array_slice($rows, 0, $size);
        if (empty($res)) {
            return [];
        }
        return $res;
    }

    public static function rem(string $file, int $size)
    {
        $rows = self::read($file, 99999999);
        $surplus = array_slice($rows, $size);
        file_put_contents($file, implode('', $surplus));
    }

    public static function mergeAtoBAndUnlinkA(string $fileA, string $fileB)
    {
        if (file_exists($fileA) && file_exists($fileB)) {
            $data = self::read($fileA, 99999999999);
            self::write($fileB, $data);
            unlink($fileA);
        }
    }

    public static function getLogDir(string $queueAlias)
    {
        return sprintf(
            '%s/%s/Log'
            , self::getUniversalQueueDir(), $queueAlias
        );
    }

    public static function getCacheDir(string $queueAlias)
    {
        return sprintf(
            '%s/%s/Cache'
            , self::getUniversalQueueDir(), $queueAlias
        );
    }

    public static function getUniversalQueueDir()
    {
        return sprintf(
            '%sUniversalQueue'
            , Config::getInstance()->getTempDir()
        );
    }

    public static function getCoroutineCacheFile(string $queueAlias, string $coroutineNumber)
    {
        return sprintf(
            '%s/%s.log'
            , self::getCacheDir($queueAlias), $coroutineNumber
        );
    }

}
