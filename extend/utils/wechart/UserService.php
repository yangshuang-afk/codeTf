<?php
namespace utils\wechart;
use EasyWeChat\Factory;
use think\exception\ValidateException;

class UserService
{
	
	//公众号授权登录
	public static function officeAuth($redirect_url,$snsapi='snsapi_userinfo'){
		$app = Factory::officialAccount(config('my.official_accounts'));
		$data = request()->get();
		if(!isset($data['code'])){
			$redirectUrl = $app->oauth->scopes([$snsapi])->redirect($redirect_url);
			header("Location: {$redirectUrl}");
		}else{
			try{
				$user = $app->oauth->userFromCode($data['code']);
			}catch(\Exception $e){
				throw new ValidateException ($e->getMessage);
			}
			return $user->toArray();
		}
	}
	
	/**
	 * 获取用户openid信息
	 */
	public static function getOpenId($code){
		if(empty($code)){
			throw new ValidateException ('code不能为空!');
		}
		$app = Factory::miniProgram(config('my.mini_program'));
		try{
			$res = $app->auth->session($code);
		}catch(\Exception $e){
			throw new ValidateException ($e->getMessage());
		}
		return $res;
	}
	
	
	/**
	 * 获取小程序手机号信息
	 * @description 小程序传入 code、iv、encryptedData  先通过code获取 session_key  然后在解码出手机号信息
	 */
	public static function getMobile($data){
		if(empty($data['code'])){
			throw new ValidateException ('code不能为空!');
		}
		if(empty($data['iv'])){
			throw new ValidateException ('iv不能为空!');
		}
		if(empty($data['encryptedData'])){
			throw new ValidateException ('encryptedData不能为空!');
		}
		$app = Factory::miniProgram(config('my.mini_program'));
		try{
			$session = self::getOpenId($data['code']);
			$decryptedData = $app->encryptor->decryptData($session['session_key'],$data['iv'],$data['encryptedData']);
		}catch(\Exception $e){
			throw new ValidateException ($e->getMessage());
		}
		$res = [];
		$res['phoneNumber'] = $decryptedData['phoneNumber'];
		$res['openid'] = $session['openid'];
		return $res;
	}
	
	
    
}
