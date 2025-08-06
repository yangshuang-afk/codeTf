<?php
namespace listen;
use think\facade\Db;

class LoginLog
{
	//写入登录日志
    public function handle($data){
        $data['application_name'] = app('http')->getName();
		$data['username'] = $data["username"];
		$data['url'] = request()->url(true);
		$data['ip'] = request()->ip();
		$data['useragent'] = request()->server('HTTP_USER_AGENT');
		$data['create_time'] = time();
		$data['type'] = 1;
		
		Db::name('log')->insert($data);
    }
}