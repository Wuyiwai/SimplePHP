<?php


namespace common\cache;

use Redis;

class RedisCache implements CacheInterface
{
    /**
     * @var Redis | array
     */
    private $redis;
    protected $dbId = 0;
    protected $auth;
    protected $options;
    static private $_instance = array();
    private $k;
    private $host;
    private $port;

    public function init()
    {
        if (is_array($this->redis)) {
            $config = require("../config/redis.php");
            $host = $config['host'] ?? 'localhost';
            $port = $config['port'] ?? 6379;
            $database = $config['database'] ?? 0;
            $password = $config['password'] ?? '';
            $options = $config['options'] ?? [];

            $redis = new Redis();
            $redis->connect($host, $port);
            if (!empty($password)) {
                $redis->auth($password);
            }
            $redis->select($database);
            if (!empty($options)) {
                call_user_func_array([$redis, 'setOption'], $options);
            }
            $this->redis = $redis;
        }
        if (!$this->redis instanceof Redis) {
            throw new \Exception('Cache::redis must be either a Redis connection instance.');
        }
    }

    public function __construct()
    {
        $config = require("../config/redis.php");
        $this->redis = new Redis();
        $this->host = $config['host'] ?? 'localhost';
        $this->port = $config['port'] ?? 6379;
        $this->dbId = $config['dbId'] ?? 0;
        $this->auth = $config['password'] ?? '';
        $this->redis->connect($this->host, $this->port);
        if ($config['auth']) {
            $this->auth = $config['auth'];
            $this->redis->auth($this->auth);
        }
    }

    public function getRedis()
    {
        return $this->redis;
    }

    public function buildKey($key)
    {
        if (!is_string($key)) {
            $key = json_encode($key);
        }
        return md5($key);
    }

    public function get($key)
    {
        $key = $this->buildKey($key);
        return $this->redis->get($key);
    }

    public function exists($key)
    {
        $key = $this->buildKey($key);
        return $this->redis->exists($key);
    }

    public function mget($keys)
    {
        for ($index = 0; $index < count($keys); $index++) {
            $keys[$index] = $this->buildKey($keys[$index]);
        }

        return $this->redis->mGet($keys);
    }

    /**
     * @param $key
     * @param $value
     * @param int $duration
     * @return bool
     */
    public function set($key, $value, $duration = 0)
    {
        $key = $this->buildKey($key);
        if ($duration !== 0) {
            $expire = (int) $duration * 1000;
            return $this->redis->set($key, $value, $expire);
        } else {
            return $this->redis->set($key, $value);
        }
    }

    public function mset($items, $duration = 0)
    {
        $failedKeys = [];
        foreach ($items as $key => $value) {
            if ($this->set($key, $value, $duration) === false) {
                $failedKeys[] = $key;
            }
        }

        return $failedKeys;
    }

    public function add($key, $value, $duration = 0)
    {
        if (!$this->exists($key)) {
            return $this->set($key, $value, $duration);
        } else {
            return false;
        }
    }

    public function madd($items, $duration = 0)
    {
        $failedKeys = [];
        foreach ($items as $key => $value) {
            if ($this->add($key, $value, $duration) === false) {
                $failedKeys[] = $key;
            }
        }

        return $failedKeys;
    }

    public function delete($key)
    {
        $key = $this->buildKey($key);
        return $this->redis->delete($key);
    }

    public function flush()
    {
        return $this->redis->flushDb();
    }
}