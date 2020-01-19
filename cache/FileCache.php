<?php


namespace common\cache;


class FileCache implements CacheInterface
{
    public $cachePath;

    public function __construct()
    {
        $this->cachePath = ROOT_PATH . '/runtime/cache/';
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
        $cacheFile = $this->cachePath . $key;

        if (@filemtime($cacheFile) > time()) {
            return unserialize(@file_get_contents($cacheFile));
        } else {
            return false;
        }
    }

    public function exists($key)
    {
        $key = $this->buildKey($key);
        $cacheFile = $this->cachePath . $key;

        return @filemtime($cacheFile) > time();
    }

    public function mget($keys)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$keys] = $this->get($key);
        }
        return $results;
    }

    public function set($key, $value, $duration = 0)
    {
        $key = $this->buildKey($key);
        $cacheFile = $this->cachePath . $key;

        $value = serialize($value);

        if (file_put_contents($cacheFile, $value, LOCK_EX) !== false) {
            if ($duration <= 0) {
                $duration = 31536000; //1 year
            }
            return touch($cacheFile, $duration + time());
        } else {
            return false;
        }
    }

    public function mset($items, $durations = 0)
    {
        $failedKeys = [];
        foreach ($items as $key => $value) {
            if ($this->set($key, $value, $durations) === false) {
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

    public function madd($items, $durations = 0)
    {
        $failedKeys = [];
        foreach ($items as $key => $value) {
            if ($this->add($key, $value, $durations) === false) {
                $failedKeys[] = $key;
            }
        }
        return $failedKeys;
    }

    public function delete($key)
    {
        $key = $this->buildKey($key);
        $cacheFile = $this->cachePath . $key;
        return unlink($cacheFile);
    }

    public function flush()
    {
        $dir = @dir($this->cachePath);

        while (($file = $dir->read()) !== false) {
            if ($file !== '.' && $file !== '..') {
                unlink($this->cachePath . $file);
            }
        }

        $dir->close();
    }
}