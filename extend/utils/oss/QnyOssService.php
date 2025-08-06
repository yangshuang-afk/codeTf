<?php

//七牛云oss上传

namespace utils\oss;
use think\facade\Log;

class QnyOssService
{
	
	/**
	 * @param  array $tempFile 本地图片路径
	 * @return string 图片上传返回的url地址
	 */
	public static function upload($file,$filepath){
		$auth = new \Qiniu\Auth(config('my.qny_oss_accessKey'), config('my.qny_oss_secretKey'));
		$upToken = $auth->uploadToken(config('my.qny_oss_bucket'));
		$uploadMgr = new \Qiniu\Storage\UploadManager();
		$ret = $uploadMgr->putFile($upToken, $filepath,$file->getPathname());
		if(!$ret[0]['key']){
			log::error('七牛云oss错误：'.print_r($ret,true));
			throw new \Exception('上传失败');			
		}
		return config('my.qny_oss_domain').'/'.$ret[0]['key'];
	}
    
}
