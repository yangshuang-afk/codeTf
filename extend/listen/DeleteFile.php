<?php
namespace listen;

use think\facade\Db;
use OSS\OssClient;
use OSS\Core\OssException;
use think\facade\Log;


class DeleteFile
{
    
	function handle($file){
		if(!is_array($file)){
			$file = [$file];
		}
		
		foreach($file as $k=>$v){
			$disk = $this->getinfo($v);
			switch($disk){
				case 'local':
					if(strpos($v,'ttp')){
						$domain = Db::name('base_config')->where('name','domain')->value('data');
						$v = str_replace($domain,'',$v);
					}
					@unlink(public_path().$v);
				break;
				
				
				case 'ali':
					$filename  = str_replace(config('my.ali_oss_endpoint').'/','',$v);
					$point = 'http://oss-cn-beijing.aliyuncs.com';
					$ossClient = new OssClient(config('my.ali_oss_accessKeyId'), config('my.ali_oss_accessKeySecret'),$point);
					try {
						$ossClient->deleteObject(config('my.ali_oss_bucket'),$filename);
					}catch(OssException $e) {
						log::error('阿里oss错误：'.print_r($e->getMessage(),true));
					}	
				break;
				
				
				case 'qiniuyun':
					$filename  = str_replace(config('my.qny_oss_domain').'/','',$v);
					$auth = new \Qiniu\Auth(config('my.qny_oss_accessKey'), config('my.qny_oss_secretKey'));
					$config = new \Qiniu\Config();
					$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
					$err = $bucketManager->delete(config('my.qny_oss_bucket'), $filename);
					if($err) {
						log::error('七牛oss错误：'.print_r($err,true));
					}
				break;
			}
		}
	}
	
	
	private function getinfo($file){
		$disk = Db::name('file')->where('filepath',$file)->value('disk');
		if(!$disk){
			$disk = 'local';
		}
		return $disk;
	}
	
	
}