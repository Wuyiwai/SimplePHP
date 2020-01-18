<?php


namespace common\cache;


interface CacheInterface
{
    public function buildKey($key);

    public function get($key);

    public function exists($key);

    public function mget($keys);

    public function set($key, $value, $duration = 0);

    public function mset($items, $durations = 0);

    public function add($key, $value, $duration = 0);

    public function madd($items, $durations = 0);

    public function delete($key);

    public function flush();
}