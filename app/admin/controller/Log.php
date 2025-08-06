<?php
namespace app\admin\controller;
use think\exception\ValidateException;
use app\admin\model\Log as LogModel;
use think\facade\Db;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Log extends Admin{



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

			$field = 'id,application_name,username,url,ip,create_time,type';
			$query = LogModel::field($field);

			$param = $this->request->post();
			if(isset($param['id']) && !empty($param['id'])) {
				$query = $query->where("id", $param['id']);
			}
			if(isset($param['username']) && $param['username'] != null) {
				$query = $query->where("username", $param['username']);
			}
			if(isset($param['create_time']) && $param['create_time'] != null) {
				$query = $query->whereBetween('create_time', [strtotime($param['create_time'][0]),strtotime($param['create_time'][1])]);
			}
			if(isset($param['type']) && $param['type'] != null) {
				$query = $query->where("type", $param['type']);
			}
			$orderby = ($param['sort'] && $param['order']) ? $param['sort'].' '.$param['order'] : 'id desc';

			$res =$query->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();

			$data['status'] = 200;
			$data['data'] = $res;
			return json($data);
		}
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
		LogModel::destroy(['id'=>explode(',',$idx)],true);
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
		$field = 'id,application_name,username,url,ip,useragent,content,errmsg,create_time,type';
		$res = LogModel::field($field)->findOrEmpty($id);

		if($res->isEmpty()){
			throw new ValidateException ('信息不存在');
		}

		return json(['status'=>200,'data'=>$res]);
	}


	/**
	 *@description 导出
	 *@buildcode(true)
	*/
	function dumpdata(){
		$page = $this->request->param('page', 1, 'intval');
		$limit = config('my.dumpsize') ? config('my.dumpsize') : 1000;

		$param = $this->request->post();

		$field = 'application_name,username,url,ip,useragent,content,errmsg,create_time,type';

		$query = LogModel::field($field);

		if(isset($param['id']) && !empty($param['id'])) {
			$query = $query->whereIn("id", $param['id']);
		}
		if(isset($param['username']) && $param['username'] != null) {
			$query = $query->where("username", $param['username']);
		}
		if(isset($param['create_time']) && $param['create_time'] != null) {
			$query = $query->whereBetween('create_time', [strtotime($param['create_time'][0]),strtotime($param['create_time'][1])]);
		}
		if(isset($param['type']) && $param['type'] != null) {
			$query = $query->where("type", $param['type']);
		}
		$orderby = ($param['sort'] && $param['order']) ? $param['sort'].' '.$param['order'] : 'id desc';

		$res =$query->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();

		foreach($res['data'] as $key=>$val){
			$res['data'][$key]['create_time'] = !empty($val['create_time']) ? date('Y-m-d H:i:s',$val['create_time']) : '';
			$res['data'][$key]['type'] = getItemVal($val['type'],'[{"key":"登录日志","val":"1","label_color":"info"},{"key":"操作日志","val":"2","label_color":"warning"},{"key":"异常日志","val":"3","label_color":"danger"}]');
			unset($res['data'][$key]['id']);
		}

		$data['status'] = 200;
		$data['header'] = explode(',','应用名,用户名,请求url,客户端ip,浏览器信息,请求内容,异常信息,创建时间,类型');
		$data['percentage'] = ceil($page * 100/ceil($res['total']/$limit));
		$data['filename'] = '日志管理.'.config('my.dump_extension');
		$data['data'] = $res['data'];
		return json($data);
	}


}