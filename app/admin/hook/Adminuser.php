<?php
namespace app\admin\hook;
use think\exception\ValidateException;
use think\facade\Db;

class Adminuser{



	//删除用户前置钩子
    function beforDelete($data){
		if(in_array(1,explode(',',$data))){
			throw new ValidateException('超级用户禁止删除');
		}
	}


}