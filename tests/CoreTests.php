<?php

namespace Huizhang\DelayQueue\Tests;

use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use PHPUnit\Framework\TestCase;
use Huizhang\DelayQueue\Core;

/**
 * @CreateTime:   2021/1/11 12:25 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  延迟队列核心方法单测
 */
class CoreTests extends TestCase
{

    public const REDIS_ALIAS = 'test';
    public const DELAY_QUEUE_ALIAS = 'queue_test1';

    public function testPush()
    {
        $this->initRedisPool();
        $score = $this->getTime();
        $res = Core::getInstance()->push(self::REDIS_ALIAS, self::DELAY_QUEUE_ALIAS, $score, 123);
        $this->assertEquals($res, 1);
    }

    public function testPop()
    {
        $res = Core::getInstance()->pop(self::REDIS_ALIAS, self::DELAY_QUEUE_ALIAS, time()-3, 1);
        $this->assertEquals($res, ['123']);
    }

    public function testRem()
    {
        $res = Core::getInstance()->rem(self::REDIS_ALIAS, self::DELAY_QUEUE_ALIAS, 123);
        $this->assertEquals($res, 0);
        $score = $this->getTime();
        Core::getInstance()->push(self::REDIS_ALIAS, self::DELAY_QUEUE_ALIAS, $score, 123);
        $res = Core::getInstance()->rem(self::REDIS_ALIAS, self::DELAY_QUEUE_ALIAS, 123);
        $this->assertEquals($res, 1);
    }

    private function getTime()
    {
        return strtotime('2021-01-01 00:00:00');
    }

    protected function initRedisPool(): void
    {
        $redisConfig = new RedisConfig();
        RedisPool::getInstance()->register(
            $redisConfig,
            self::REDIS_ALIAS
        );
    }

}
