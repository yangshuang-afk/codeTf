<?php

//��Ѷ��oss�ϴ�

namespace utils\oss;
use think\facade\Log;
use Qcloud\Cos\Client;


class TencentOssService
{
	
	/**
	 * ��Ѷ��oss�ϴ�
	 * @param  array $tempFile ����ͼƬ·��
	 * @return string ͼƬ�ϴ����ص�url��ַ
	 */
	public static function upload($file,$filepath){
		$cosClient = new Client([
			'region' => config('my.tencent_oss_region'),
			'schema' => config('my.tencent_oss_schema'),
			'credentials'=>[
				'secretId' => config('my.tencent_oss_secretId'),
				'secretKey' => config('my.tencent_oss_secretKey')
			]
		]);
			
		$data = [
			'Bucket' => config('my.tencent_oss_bucket'),
			'Key' => $filepath,
			'Body' => fopen($file->getPathname(),'rb'),
			'ServerSideEncryption' => 'AES256'
		];
	
		try{
			$result = $cosClient->putObject($data);
			$result = $result->toArray();
			if(isset($result['Key']) && !empty($result['Location'])){
				return config('my.tencent_oss_schema').'://'.$result['Location'];
			}
		}catch(\Exception $e){
			log::error('��Ѷ��oss����'.print_r($e->getMessage(),true));
			throw new \Exception('�ϴ�ʧ��');
		}
		
	}
	
}
