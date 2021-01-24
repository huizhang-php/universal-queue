# EasySwoole 通用队列组件

- 支持消费数据先落盘防止异常丢失数据
- 支持队列数据消费日志保留
- 支持基于Redis延迟队列
- 支持基于Redis的队列
- 支持MemcacheQ
- 支持Kafka(正在开发)

> 后续会支持更多消息中间件的消费驱动

### 定义消费者

````php
<?php
namespace App\DelayQueue;

use Huizhang\UniversalQueue\Core\ConsumerAbstract;

class DelayQueue1 extends ConsumerAbstract
{

    public $queue;

    public function deal(array $data)
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
use Huizhang\UniversalQueue\Driver\RedisDelayQueue;
use Huizhang\UniversalQueue\Driver\RedisQueue;
use Huizhang\UniversalQueue\Driver\MemcacheQ;
use Huizhang\UniversalQueue\UniversalQueue;
use App\DelayQueue\DelayQueue1;

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
        $config = \Huizhang\UniversalQueue\Config::getInstance()
            ->setQueues([
                // redis 延迟队列
                'redis_delay_queue' => [
                    'limit' => 3, // 每个协程取出的最大消息数
                    'driver' => new RedisDelayQueue(), // 队列驱动
                    'consumer' => new DelayQueue1(), // 消费者
                    'coroutineNum' => 1, // 协程数
                    'retainLogNum' => 3, // 消费日志最大保存个数(以小时分割)
                    'driverConfig' => [
                        'redisAlias' => 'redis1', // 延迟队列redis所需配置
                        'delayTime' => 3 // 延迟时间
                    ]
                ],
                // redis 队列
                'redis_queue' => [
                    'limit' => 3, // 每个协程取出的最大消息数
                    'driver' => new RedisQueue(), // 队列驱动
                    'consumer' => new DelayQueue1(), // 消费者
                    'coroutineNum' => 1, // 协程数
                    'retainLogNum' => 3, // 消费日志最大保存个数(以小时分割)
                    'driverConfig' => [
                        'redisAlias' => 'redis1'
                    ]
                ],
                // memcacheq
                'mcq' => [
                    'limit' => 3, // 每个协程取出的最大消息数
                    'driver' => new MemcacheQ(), // 队列驱动
                    'consumer' => new DelayQueue1(), // 消费者
                    'coroutineNum' => 1, // 协程数
                    'retainLogNum' => 3, // 消费日志最大保存个数(以小时分割)
                    'driverConfig' => [
                        'servers' => [
                            '0.0.0.0:11211:3',
                            '0.0.0.0:11211:3',
                        ]
                    ]
                ],
            ]);
        UniversalQueue::getInstance($config)->attachServer(ServerManager::getInstance()->getSwooleServer());
    }

}

````

### 生产消息 

````php
    UniversalQueue::getInstance()->push('redis_delay_queue', 123);
````

### 驱动

1. redis 延迟队列

`use Huizhang\UniversalQueue\Driver\RedisDelayQueue;`
 
2. redis 队列

`use Huizhang\UniversalQueue\Driver\RedisQueue;`

3. MemcacheQ 

`use Huizhang\UniversalQueue\Driver\MemcacheQ;`

