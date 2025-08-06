<?php
namespace utils\sms;
use think\facade\Log;

class TencentSmsService
{
	
	/**
	 * 发送短信
	 * @param  array $data [发送参数] $data['mobile'] 发送手机号 $data['code'] 发送验证码
	 * @return  Bool
	 */
	public static function sendSms($data){
		$appid = config('my.tencent_sms_appid');  //短信appid
		$appkey = config('my.tencent_sms_appkey'); //短信appkey
		$templId = config('my.tencent_sms_tempCode');
		$params = [$data['code']];
		$phoneNumber =  $data['mobile'];
		$sign = config('my.tencent_sms_signname');

		$random = rand(100000, 999999);//生成随机数
		$curTime = time();
		$wholeUrl = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms". "?sdkappid=" . $appid . "&random=" . $random;

		// 按照协议组织 post 包体
		$data = new \stdClass();//创建一个没有成员方法和属性的空对象
		$tel = new \stdClass();
		$tel->nationcode = "86";
		$tel->mobile = "".$phoneNumber;
		$data->tel = $tel;
		$data->sig=hash("sha256", "appkey=".$appkey."&random=".$random."&time=".$curTime."&mobile=".$phoneNumber);// 生成签名
		$data->tpl_id = $templId;
		$data->params = $params;
		$data->sign = $sign;
		$data->time = $curTime;

		$result = self::sendCurlPost($wholeUrl, $data);
		
		$result = json_decode($result,true);
		if($result['result'] == '0'){
			return true;
		}else{
			log::error('腾讯云短信发送失败:'.print_r($result,true));
			throw new \Exception('发送失败');
		}
		
	}
	
	
	public static function sendCurlPost($url, $dataObj){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$ret = curl_exec($curl);
		if (false == $ret) {
			// curl_exec failed
			$result = "{ \"result\":" . -2 . ",\"errmsg\":\"" . curl_error($curl) . "\"}";
		} else {
			$rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if (200 != $rsp) {
				$result = "{ \"result\":" . -1 . ",\"errmsg\":\"". $rsp
						. " " . curl_error($curl) ."\"}";
			} else {
				$result = $ret;
			}
		}
		curl_close($curl);
		return $result;
	}
	
}


