<?php

//单点登录

namespace listen;
use think\facade\Session;
use think\facade\Cache;

class Sso
{
	
    public function handle($data){
		if(!config("my.multiple_login")){
			$key = md5(request()->server('HTTP_HOST').trim($data['username']).trim($data['password']));
			$sessionId = Session::getId();
			if(config("session.type") == "cache" && config("cache.default") == "redis"){
				$redis = new \Redis();
				$redis->connect(config("cache.stores.redis.host"), config("cache.stores.redis.port"));
				$sessionList = $redis->hGetAll($key);
				foreach($sessionList as $k=>$v){
					$redis->delete($k);
				}
				$redis->hSet($key, $sessionId, '');
			}else{
				$sessionList = Cache::get($key);
				foreach($sessionList as $k=>$v){
					@unlink(app()->getRootPath().'/runtime/session/sess_'.$v);
				}
				Cache::push($key, $sessionId);
			}
		}
    }
}