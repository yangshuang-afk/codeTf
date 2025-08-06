<?php
namespace app\admin\model;
use think\Model;

class Adminuser extends Model{

	/**
	 *@description 链接库
	 *@buildcode(true)
	*/
	protected $connection = 'mysql';

	/**
	 *@description 主键
	 *@buildcode(true)
	*/
	protected $pk = 'user_id';

	/**
	 *@description 数据表
	 *@buildcode(true)
	*/
	protected $name = 'admin_user';
    
    
    
    /**
     *@description 关联模型
     *@buildcode(true)
     */
    function role(){
        return $this->hasOne(\app\admin\model\Role::class,'role_id','role_id');
    }
}