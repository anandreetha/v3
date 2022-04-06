<?php

namespace Multiple\Core\Misc;

use Jenner\RedisSentinel\Sentinel;
use Phalcon\Session\Exception;
use Redis;

class RedisSentinel
{
    private $hosts;

    private $master_name;

    public function __construct($hosts, $master_name = 'master')
    {
        $this->hosts = $hosts;
        $this->master_name = $master_name;
    }

    public function getRedisMasterAddress()
    {
        $sentinel = new Sentinel();
        $has_connected = false;

        // Try the sentinel hosts until one connects
        foreach ($this->hosts as $sentinel_host) {
            $has_connected = $sentinel->connect($sentinel_host['host'], $sentinel_host['port']);
            if ($has_connected) {
                break;
            }
        }

        // Check if any hosts succeeded in connecting
        if (!$has_connected) {
            throw new Exception('Unable to connect to a Redis sentinel');
        }

        return $sentinel->getMasterAddrByName($this->master_name);
    }

    public function openRedisConnection()
    {
        $address = $this->getRedisMasterAddress();
        $redis = new Redis();
        if (!$redis->connect($address['ip'], $address['port'])) {
            throw new Exception('Unable to connect to master Redis instance');
        }

        return $redis;
    }

    public function __toString()
    {
        return 'redis sentinel: ' . $this->master_name . ', ' . $this->hosts;
    }
}
