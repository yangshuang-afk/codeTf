<?php
namespace app\admin\controller;
use think\exception\ValidateException;
use app\admin\model\Actionremarks as ActionremarksModel;
use think\facade\Db;

class Actionremarks extends Admin{



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

			$field = 'id,action_id,content,description,create_time,menu_id';
			$query = ActionremarksModel::field($field);

			if(isset($param['id']) && !empty($param['id'])) {
				$query = $query->where("id", $param['id']);
			}
			if(isset($param['menu_id']) && $param['menu_id'] != null) {
				$query = $query->where("menu_id", $param['menu_id']);
			}
			$orderby = ($param['sort'] && $param['order']) ? $param['sort'].' '.$param['order'] : 'id desc';

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
		$postField = 'id,';
		$data = $this->request->only(explode(',',$postField),'post');

		if(!$data['id']){
			throw new ValidateException ('参数错误');
		}
		ActionremarksModel::update($data);

		return json(['status'=>200,'msg'=>'操作成功']);
	}


	/**
	 *@description 添加
	 *@buildcode(true)
	*/
	public function add(){
		$postField = 'action_id,content,description,create_time,menu_id';
		$data = $this->request->only(explode(',',$postField),'post');

		$validate = new \app\admin\validate\Actionremarks;
		if(!$validate->scene('add')->check($data)){
			throw new ValidateException ($validate->getError());
		}

		$data['create_time'] = time();

		try{
			$res = ActionremarksModel::insertGetId($data);
		}catch(\Exception $e){
			throw new ValidateException($e->getMessage());
		}
		return json(['status'=>200,'data'=>$data['id'],'msg'=>'添加成功']);
	}


	/**
	 *@description 修改
	 *@buildcode(true)
	*/
	public function update(){
		$postField = 'id,action_id,content,description,create_time,menu_id';
		$data = $this->request->only(explode(',',$postField),'post');

		$validate = new \app\admin\validate\Actionremarks;
		if(!$validate->scene('update')->check($data)){
			throw new ValidateException ($validate->getError());
		}

		$data['create_time'] = !empty($data['create_time']) ? strtotime($data['create_time']) : '';

		try{
			ActionremarksModel::update($data);
		}catch(\Exception $e){
			throw new ValidateException($e->getMessage());
		}
		return json(['status'=>200,'msg'=>'修改成功']);
	}


	/**
	 *@description 修改信息之前查询信息
	 *@buildcode(true)
	*/
	function getUpdateInfo(){
		$id =  $this->request->post('id', '', 'serach_in');
		if(!$id){
			throw new ValidateException ('参数错误');
		}
		$field = 'id,action_id,content,description,create_time,menu_id';

		$res = ActionremarksModel::field($field)->findOrEmpty($id);
		if($res->isEmpty()){
			throw new ValidateException ('信息不存在');
		}
		return json(['status'=>200,'data'=>$res]);
	}


	/**
	 *@description 删除
	 *@buildcode(true)
	*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx){
			throw new ValidateException ('参数错误');
		}
		ActionremarksModel::destroy(['id'=>explode(',',$idx)],true);
		return json(['status'=>200,'msg'=>'操作成功']);
	}


	/**
	 *@description 查看详情
	 *@buildcode(true)
	*/
	function detail(){
		$id =  $this->request->post('id', '', 'serach_in');
		if(!$id){
			throw new ValidateException ('参数错误');
		}
		$field = 'id,action_id,content,description,create_time,menu_id';
		$res = ActionremarksModel::field($field)->findOrEmpty($id);

		if($res->isEmpty()){
			throw new ValidateException ('信息不存在');
		}

		return json(['status'=>200,'data'=>$res]);
	}


}