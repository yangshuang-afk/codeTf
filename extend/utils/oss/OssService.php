<?php

namespace utils\oss;


class OssService
{
	
	/**
	 * oss开始上传
	 * @param  string tmpInfo 图片临时文件信息
	 * @return string oss返回图片完整路径
	 */
	public static function OssUpload($file,$rename){
		if($rename == 2){
			$filepath = app('http')->getName().'/'.date(config('my.upload_subdir')).'/'.$file->getOriginalName(); //上传路径
		}else{
			$filepath = app('http')->getName().'/'.date(config('my.upload_subdir')).'/'.doOrderSn('000').'.'.$file->extension(); //上传路径
		}
		
		switch(config('my.oss_default_type')){
			case 'ali';
				$url = \utils\oss\AliOssService::upload($file,$filepath);	//七牛云上传
			break;
			
			case 'qiniuyun';
				$url = \utils\oss\QnyOssService::upload($file,$filepath);	//阿里上传
			break;
			
			case 'tencent';
				$url = \utils\oss\TencentOssService::upload($file,$filepath);	//阿里上传
			break;
		}
		return $url;
	}
	
	
	
}
