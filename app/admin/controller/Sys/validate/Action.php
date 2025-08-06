<?php 
namespace app\admin\controller\Sys\validate;
use think\validate;
use think\facade\Db;


class Action extends validate {


	protected $rule = [
		'type'=> 'checkType',
		'name'=> ['require'],
		'action_name'=> ['require'],
	];
	

	protected $message = [
		'name.require'=>'方法名不能为空',
		'action_name.require'=>'方法英文名不能为空',
	];

	
	//创建方法检测
    protected function checkType($value, $rule, $data=[])
    {
		$msg = '';
		if($data['type'] == 1){
			if(!empty($data['left_tree_sql'])){
				$count = Db::name('field')->where('sql',$data['left_tree_sql'])->count();
				if($count == 0){
					$msg = '侧栏sql与对应字段sql不匹配，请检查!';
				}
			}
			return $msg ? $msg : true;
		}elseif(in_array($data['type'],[6,7,8,9,13,53])){
			if(empty($data['fields'])){
				$msg = '请勾选操作字段';
			}
			
			if(count($data['fields']) > 1){
				$msg = '只能设置一个字段';
			}
			
			if($data['type'] == 7){
				if($data['status_val'] === ''){
					$msg = '请配置状态修改值';
				}
			}
			return $msg ? $msg : true;
		}elseif(in_array($data['type'],[15,16])){
			if(empty($data['jump'])){
				$msg = '请设置跳转地址';
			}
			return $msg ? $msg : true;
		}elseif($data['type'] == 50){
			$loginInfo = $data['other_config'];
			if(empty($loginInfo['login_type'])){
				$msg = '请选择登录方式';
			}
			if($loginInfo['login_type'] == 1){
				if(empty($loginInfo['userField']) && empty($loginInfo['pwdField'])){
					$msg = '请设置登录字段';
				}
			}
			if($loginInfo['login_type'] == 2){
				if(empty($loginInfo['smsField'])){
					$msg = '请设置手机号字段';
				}
			}
			if(empty($data['fields'])){
				$msg = '请勾选返回字段';
			}
			return $msg ? $msg : true;
		}elseif($data['type'] == 51){
			$sms = $data['other_config'];
			if(empty($sms['sms_partenr'])){
				$msg = '请选择短信平台';
			}
			return $msg ? $msg : true;
		}else{
			return true;
		}
    }
	
	
	


}

