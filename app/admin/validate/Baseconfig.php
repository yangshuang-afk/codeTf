<?php
namespace app\admin\validate;
use think\validate;

class Baseconfig extends validate{

	/**
	 *@description 验证器规则
	 *@buildcode(true)
	*/
	protected $rule = [
		'site_title'=>['require'],
	];

	/**
	 *@description 错误提示
	 *@buildcode(true)
	*/
	protected $message = [
		'site_title.require'=>'站点名称不能为空',
	];

	/**
	 *@description 验证场景
	 *@buildcode(true)
	*/
	protected $scene  = [
		'index'=>['site_title'],
	];



}