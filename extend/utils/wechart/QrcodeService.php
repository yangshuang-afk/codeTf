<?php
namespace utils\wechart;
use EasyWeChat\Factory;


class QrcodeService
{
	
	
	private static $qrcode_dir = './uploads/qrcode';
	
	/**
	 * 带参数的小程序二维码生成
	 * @param  array $data
	 * @param  data.width 二维码宽度
	 * @param  data.scene 二维码参数
	 * @param  data.page 小程序页面地址
	 * @param  data.filename 生成的图片文件名
	 * @return string  返回文件名   
	 */
	public static function createMiniQrcode($data){
		$app = Factory::miniProgram(config('my.mini_program'));
		
		$width = empty($data['width']) ? $data['width'] : 600;
		$response = $app->app_code->getUnlimit($data['scene'], [
			'page'  => $data['page'],
			'width' => $width,
		]);
		
		if (!is_dir(self::$qrcode_dir)){
			mkdir(self::$qrcode_dir);
		}

		// 保存小程序码到文件
		if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
			try{
				$filename = $response->saveAs(self::$qrcode_dir, $data['filename']);
			}catch(\Exception $e){
				throw new ValidateException ($e->getMessage());
			}
			return ltrim(self::$qrcode_dir.'/'.$filename,'.');
		}
	}
	
	/**
	 * 带参数的公众号二维码生成
	 * @param  array $data
	 * @param  data.scene 二维码参数
	 * @param  data.filename 生成的图片文件名
	 * @return string  返回文件名   
	 */
	public static function createOfficeQrcode($data){
		$app = Factory::officialAccount(config('my.official_accounts'));
		$result = $app->qrcode->forever($data['scene']);
		$url = $app->qrcode->url($result['ticket']);
		
		if (!is_dir(self::$qrcode_dir)){
			mkdir(self::$qrcode_dir);
		}
		$filename = self::$qrcode_dir.'/'.$data['filename'];
		$ch = curl_init($url);
		$fp = fopen($filename, 'w');

		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		$result = curl_exec($ch);

		curl_close($ch);
		fclose($fp);
		
		return ltrim($filename,'.');
	}
	
	
    
} 
