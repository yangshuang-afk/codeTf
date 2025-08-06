<?php
namespace utils;
use Closure;
use think\facade\Cache as CacheFacade;


class Cache{

    public static function remember($key, $ttl, Closure $callback)
    {
        $value = CacheFacade::get($key);
        if ($value) {
            return $value;
        }
        CacheFacade::set($key, $value = $callback(), $ttl);
		
        return $value;
    }
}