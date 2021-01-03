<?php
/**
 * @CreateTime:   2021/1/3 1:23 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  redis接口
 */
namespace Huizhang\DelayQueue;

use EasySwoole\Redis\Redis;

interface RedisConnInterface {

    public function getClient(string $alias): Redis;

}
