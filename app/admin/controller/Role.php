<?php
namespace app\admin\controller;
use think\exception\ValidateException;
use app\admin\model\Role as RoleModel;
use think\facade\Db;

class Role extends Admin{



	/**
	 *@description 数据列表
	 *@buildcode(true)
	*/
	function index(){
		if (!$this->request->isPost()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$page = $this->request->post('page', 1, 'intval');
			$param = $this->request->post();

			$field = 'role_id,name,status,description';
			$query = RoleModel::field($field);

			if(isset($param['role_id']) && !empty($param['role_id'])) {
				$query = $query->where("role_id", $param['role_id']);
			}
			if(isset($param['name']) && $param['name'] != null) {
				$query = $query->where("name", $param['name']);
			}
			if(isset($param['status']) && $param['status'] != null) {
				$query = $query->where("status", $param['status']);
			}
			$orderby = ($param['sort'] && $param['order']) ? $param['sort'].' '.$param['order'] : 'role_id desc';

			$res =$query->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();

			$data['status'] = 200;
			$data['data'] = $res;
			return json($data);
		}
	}


	/**
	 *@description 修改排序开关
	 *@buildcode(true)
	*/
	function updateExt(){
		$postField = 'role_id,status';
		$data = $this->request->only(explode(',',$postField),'post');

		if(!$data['role_id']){
			throw new ValidateException ('参数错误');
		}
		RoleModel::update($data);

		return json(['status'=>200,'msg'=>'操作成功']);
	}


	/**
	 *@description 添加
	 *@buildcode(false)
	*/
	public function add(){
		$postField = 'name,status,description,access';
		$data = $this->request->only(explode(',',$postField),'post');

		$validate = new \app\admin\validate\Role;
		if(!$validate->scene('add')->check($data)){
			throw new ValidateException ($validate->getError());
		}
		
		if(!in_array('Home',$data['access'])){
			array_push($data['access'],'Home');
		}
		$data['access'] = implode(',',$data['access']);

		try{
			$res = RoleModel::insertGetId($data);
		}catch(\Exception $e){
			throw new ValidateException($e->getMessage());
		}
		return json(['status'=>200,'data'=>$res,'msg'=>'添加成功']);
	}


	/**
	 *@description 修改
	 *@buildcode(false)
	*/
	public function update(){
		$postField = 'role_id,name,status,description,access';
		$data = $this->request->only(explode(',',$postField),'post');

		$validate = new \app\admin\validate\Role;
		if(!$validate->scene('update')->check($data)){
			throw new ValidateException ($validate->getError());
		}
		
		if(!in_array('Home',$data['access'])){
			array_push($data['access'],'Home');
		}
		$data['access'] = implode(',',$data['access']);

		try{
			RoleModel::update($data);
		}catch(\Exception $e){
			throw new ValidateException($e->getMessage());
		}
		return json(['status'=>200,'msg'=>'修改成功']);
	}


	/**
	 *@description 修改信息之前查询信息
	 *@buildcode(false)
	*/
	function getUpdateInfo(){
		$id =  $this->request->post('role_id', '', 'serach_in');
		if(!$id){
			throw new ValidateException ('参数错误');
		}
		$field = 'role_id,name,status,description,access';

		$res = RoleModel::field($field)->findOrEmpty($id);
		if($res->isEmpty()){
			throw new ValidateException ('信息不存在');
		}
		
		$res['access'] = explode(',',$res['access']);

		return json(['status'=>200,'data'=>$res]);
	}


	/**
	 *@description 删除
	 *@buildcode(false)
	*/
	function delete(){
		$idx =  $this->request->post('role_id', '', 'serach_in');
		if(!$idx){
			throw new ValidateException ('参数错误');
		}
		if(in_array(1,explode(',',$data))){
			throw new ValidateException('超级用户分组禁止删除');
		}
		RoleModel::destroy(['role_id'=>explode(',',$idx)],true);
		return json(['status'=>200,'msg'=>'操作成功']);
	}


}