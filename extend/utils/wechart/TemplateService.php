<?php
//模板消息
namespace utils\wechart;
use EasyWeChat\Factory;
use think\facade\Log;

class TemplateService
{ 
	
	
	/**
	 * 微信公众号模板消息
	 */
	public static function sendOfficialTempLateMsg($data,$template_id){
		$app = Factory::officialAccount(config('my.official_accounts'));
		
		$param['touser']		= $data['openid'];
		$param['template_id']	= $template_id;
		$param['url']			= $data['url'];
		$param['data']			= $data['body'];
		
		try{
			$res = $app->template_message->send($param);
		}catch(\Exception $e){
			throw new ValidateException ($e->getMessage);
		}
		
		return $res;
	}
	
	
	/**
	 * 微信公众号订阅消息
	 */
	public static function sendSubscription($data,$template_id){
		$app = Factory::officialAccount(config('my.official_accounts'));
		
		$param['touser']		= $data['openid'];
		$param['template_id']	= $template_id;
		$param['url']			= $data['url'];
		$param['scene']			= $data['scene'];
		$param['data']			= $data['body'];
		
		try{
			$res = $app->template_message->sendSubscription($param);
		}catch(\Exception $e){
			throw new ValidateException ($e->getMessage);
		}
		
		return $res;
	}
	
	
	/**
	 * 小程序模板消息
	 */
	public function sendMiniTempLateMsg($data){
		$app = Factory::miniProgram(config('my.mini_program'));
		
		$param['touser']		= $data['openid'];
		$param['template_id']	= config('my.mini_template_id');
		$param['url']			= $data['url'];
		$param['form_id']		= $data['form_id'];
		$param['data']			= $data['body'];
		
		try{
			$res = $app->template_message->send($param);
		}catch(\Exception $e){
			throw new ValidateException ($e->getMessage);
		}
		
		return $res;
	}
	
    
}
