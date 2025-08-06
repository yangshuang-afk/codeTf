<?php
namespace app\admin\controller;
use think\exception\ValidateException;
use app\admin\model\Uploadconfig as UploadconfigModel;
use think\facade\Db;

class Uploadconfig extends Admin{



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

			$field = 'id,title,upload_replace,thumb_status,thumb_width,thumb_height,thumb_type';
			$query = UploadconfigModel::field($field);

			$param = $this->request->post();
			if(isset($param['id']) && !empty($param['id'])) {
				$query = $query->where("id", $param['id']);
			}
			if(isset($param['upload_replace']) && $param['upload_replace'] != null) {
				$query = $query->where("upload_replace", $param['upload_replace']);
			}
			if(isset($param['thumb_status']) && $param['thumb_status'] != null) {
				$query = $query->where("thumb_status", $param['thumb_status']);
			}
			if(isset($param['thumb_type']) && $param['thumb_type'] != null) {
				$query = $query->where("thumb_type", $param['thumb_type']);
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
		$postField = 'id,upload_replace,thumb_status';
		$data = $this->request->only(explode(',',$postField),'post');

		if(!$data['id']){
			throw new ValidateException ('参数错误');
		}
		UploadconfigModel::update($data);

		return json(['status'=>200,'msg'=>'操作成功']);
	}


	/**
	 *@description 添加
	 *@buildcode(true)
	*/
	public function add(){
		$postField = 'title,upload_replace,thumb_status,thumb_width,thumb_height,thumb_type';
		$data = $this->request->only(explode(',',$postField),'post');

		$validate = new \app\admin\validate\Uploadconfig;
		if(!$validate->scene('add')->check($data)){
			throw new ValidateException ($validate->getError());
		}

		try{
			$res = UploadconfigModel::insertGetId($data);
		}catch(\Exception $e){
			throw new ValidateException($e->getMessage());
		}
		return json(['status'=>200,'data'=>$res,'msg'=>'添加成功']);
	}


	/**
	 *@description 修改
	 *@buildcode(true)
	*/
	public function update(){
		$postField = 'id,title,upload_replace,thumb_status,thumb_width,thumb_height,thumb_type';
		$data = $this->request->only(explode(',',$postField),'post');

		$validate = new \app\admin\validate\Uploadconfig;
		if(!$validate->scene('update')->check($data)){
			throw new ValidateException ($validate->getError());
		}

		try{
			UploadconfigModel::update($data);
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
		$field = 'id,title,upload_replace,thumb_status,thumb_width,thumb_height,thumb_type';

		$res = UploadconfigModel::field($field)->findOrEmpty($id);
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
		UploadconfigModel::destroy(['id'=>explode(',',$idx)],true);
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
		$field = 'id,title,upload_replace,thumb_status,thumb_width,thumb_height,thumb_type';
		$res = UploadconfigModel::field($field)->findOrEmpty($id);

		if($res->isEmpty()){
			throw new ValidateException ('信息不存在');
		}

		return json(['status'=>200,'data'=>$res]);
	}


}