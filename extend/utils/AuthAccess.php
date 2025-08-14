<?php
namespace utils;
use think\facade\Db;

//获取权限节点
class AuthAccess
{
    
	//权限系统获取菜单
	public function getNodeMenus($app_id,$pid){
		$appname = Db::name("application")->where("app_id",$app_id)->value("app_dir");
		$field = 'menu_id,title,controller_name,status,icon,sortid,url';
		$list = Db::name('menu')->field($field)->where(['app_id'=>$app_id,'pid'=>$pid])->order('sortid asc')->select()->toArray();
		if($list){
			foreach($list as $key=>$val){
			    if (app('http')->getName() =='admin' || in_array($val['url'] ? $val['url'] : $appname.'/'.$val['controller_name'],session($appname.'.access'))) {
    				$menus[$key]['sortid'] = $val['sortid'];
    				$menus[$key]['access'] = $val['url'] ? $val['url'] : $appname.'/'.$val['controller_name'];
    				$menus[$key]['title'] = $val['url'] ? $val['title'].'('.$val['url'].')' : $val['title'].' '.'(/'.$appname.'/'.$val['controller_name'].')';
    				$sublist = Db::name('menu')->field($field)->where(['app_id'=>$app_id,'pid'=>$val['menu_id']])->order('sortid asc')->select()->toArray();
    				if($sublist){
    					if($this->getFuns($val,$appname)){
    						$menus[$key]['children'] = array_merge($this->getFuns($val,$appname),$this->getNodeMenus($app_id,$val['menu_id'],$appname));
    					}else{
    						$menus[$key]['children'] = $this->getNodeMenus($app_id,$val['menu_id'],$appname);
    					}
    				}else{
    					$funs = $this->getFuns($val,$appname);
    					$funs && $menus[$key]['children'] = $funs;
    				}
			    }
			}
			return array_values($menus);
		}
	}
	
	//获取菜单方法
	public function getFuns($info,$appname){
		$list = Db::name('action')->field('name,action_name,type')->where('menu_id',$info['menu_id'])->order('sortid asc')->select()->toArray();
		
		$array = [];
		foreach($list as $k=>$v){
			if($v['type'] == 3){
				array_push($list,['name'=>$v['name'].'详情','action_name'=>'get'.ucfirst($v['action_name']).'Info']);
			}
		}
		
		if($list){
			foreach($list as $key=>$val){
				$funs[$key]['title'] = $val['name'].' ('.$appname.'/'.$info['controller_name'].'/'.$val['action_name'].')';
				$funs[$key]['access'] = '/'.$appname.'/'.$info['controller_name'].'/'.$val['action_name'].'.html';
			}
			return array_values($funs);
		}
	}

}
