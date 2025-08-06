<?php
namespace app\admin\validate;
use think\validate;

class Adminuser extends validate{

	/**
	 *@description 验证器规则
	 *@buildcode(true)
	*/
	protected $rule = [
		'name'=>['require'],
		'user'=>['require'],
		'pwd'=>['require'],
		'role_id'=>['require'],
		'status'=>['require'],
	];

	/**
	 *@description 错误提示
	 *@buildcode(true)
	*/
	protected $message = [
		'name.require'=>'用户姓名不能为空',
		'user.require'=>'登录账号不能为空',
		'pwd.require'=>'用户密码不能为空',
		'role_id.require'=>'所属角色不能为空',
		'status.require'=>'账号状态不能为空',
	];

	/**
	 *@description 验证场景
	 *@buildcode(true)
	*/
	protected $scene  = [
		'add'=>['name','user','role_id','status'],
		'update'=>['name','user','role_id','status'],
		'resetPwd'=>[''],
	];



}