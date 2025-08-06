<?php
//设置菜单
namespace utils\wechart;
use EasyWeChat\Factory;
use think\exception\ValidateException;


class MenuService
{
	
	//获取公众号菜单
	public static function getMenu(){
		$app = Factory::officialAccount(config('my.official_accounts'));
		try{
			$list = $app->menu->list();
		}catch(\Exception $e){
			throw new ValidateException ($e->getMessage());
		}
		return $list;
	}
	
	//设置菜单
	public static function setMenu($buttons){
		$app = Factory::officialAccount(config('my.official_accounts'));
		try{
			$res = $app->menu->create($buttons);
		}catch(\Exception $e){
			throw new ValidateException ($e->getMessage());
		}
		return $res;
	}
	
    
}
