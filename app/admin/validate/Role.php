<?php
namespace app\admin\validate;
use think\validate;

class Role extends validate{

	/**
	 *@description 验证器规则
	 *@buildcode(true)
	*/
	protected $rule = [
		'name'=>['require'],
	];

	/**
	 *@description 错误提示
	 *@buildcode(true)
	*/
	protected $message = [
		'name.require'=>'角色名称不能为空',
	];

	/**
	 *@description 验证场景
	 *@buildcode(true)
	*/
	protected $scene  = [
		'add'=>['name'],
		'update'=>['name'],
	];



}