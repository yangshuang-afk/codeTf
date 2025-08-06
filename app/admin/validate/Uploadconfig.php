<?php
namespace app\admin\validate;
use think\validate;

class Uploadconfig extends validate{

	/**
	 *@description 验证器规则
	 *@buildcode(true)
	*/
	protected $rule = [
		'title'=>['require'],
	];

	/**
	 *@description 错误提示
	 *@buildcode(true)
	*/
	protected $message = [
		'title.require'=>'配置名称不能为空',
	];

	/**
	 *@description 验证场景
	 *@buildcode(true)
	*/
	protected $scene  = [
		'add'=>['title'],
		'update'=>['title'],
	];



}