# DelayQueue(延迟队列)

- 利用Redis(有序列表)+Lua实现延迟队列
- 利用自定义进程实现各延迟队列的隔离
- 利用协程实现并发消费

### 定义消费者

````php
<?php
namespace App\DelayQueue;

use Huizhang\DelayQueue\ConsumerAbstract;

class DelayQueue1 extends ConsumerAbstract
{

    public $queue;

    public function init()
    {

    }

    public function deal(array $data)
    {

    }

    public function onException(\Throwable $e, array $data)
    {

    }

}

````
### 服务注册 

````php
<?php
namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Redis\Config\RedisConfig;
use Huizhang\DelayQueue\DelayQueue;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
        $redisConfig = new RedisConfig();
        \EasySwoole\RedisPool\RedisPool::getInstance()->register(
            $redisConfig,
            'redis1'
        );

        $config = \Huizhang\DelayQueue\Config::getInstance()
            ->setQueues([
                // 延迟队列别名
                'test' => [
                    'redisAlias' => 'redis1', // 所使用的redis
                    'limit' => 100, // 每个协程取出的最大消息数
                    'class' => '\\App\\DelayQueue\\DelayQueue1', // 消费者
                    'delayTime' => 3, // 延迟时间
                    'coroutineNum' => 1 // 协程数
                ],
                'test2' => [
                    'redisAlias' => 'redis1',
                    'limit' => 100,
                    'class' => '\\App\\DelayQueue\\DelayQueue2',
                    'delayTime' => 3,
                    'coroutineNum' => 2
                ]
            ]);
        DelayQueue::getInstance($config)->attachServer(ServerManager::getInstance()->getSwooleServer());
    }

}
````

### 方法

````php
    DelayQueue::getInstance()->push('test', 123);
    DelayQueue::getInstance()->rem('test', 123);
````
