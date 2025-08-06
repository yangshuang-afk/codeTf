<?php
namespace app\admin\validate;
use think\validate;

class Actionremarks extends validate{

	/**
	 *@description 验证器规则
	 *@buildcode(true)
	*/
	protected $rule = [
		'action_id'=>['regex'=>'/^[0-9]*$/'],
		'content'=>['require'],
		'description'=>['require'],
		'menu_id'=>['regex'=>'/^[0-9]*$/'],
	];

	/**
	 *@description 错误提示
	 *@buildcode(true)
	*/
	protected $message = [
		'action_id.regex'=>'action_id格式错误',
		'content.require'=>'代码内容不能为空',
		'description.require'=>'功能描述不能为空',
		'menu_id.regex'=>'所属菜单格式错误',
	];

	/**
	 *@description 验证场景
	 *@buildcode(true)
	*/
	protected $scene  = [
		'add'=>['action_id','content','description','menu_id'],
		'update'=>['action_id','content','description','menu_id'],
	];



}