<?php

namespace Compatibuddy\Caches;

interface CacheInterface {
    public function get($key);
    public function set($key, $value);
    public function fetch();
    public function commit();
    public function clear($keys);
}