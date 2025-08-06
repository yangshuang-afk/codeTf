<?php
namespace app\admin\controller;
use think\exception\ValidateException;
use app\admin\model\Baseconfig as BaseconfigModel;
use think\facade\Db;

class Baseconfig extends Admin{



	/**
	 *@description 配置表单
	 *@buildcode(true)
	*/
	public function index(){
		if (!$this->request->isPost()){
			return view('index');
		}else{
			$data = $this->request->post();
			$this->validate($data,\app\admin\validate\Baseconfig::class);

			$data['keyword'] = is_array($data['keyword']) ? implode(',',$data['keyword']) : '';
			$info = BaseconfigModel::column('data','name');
			foreach ($data as $key => $value) {
				if(array_key_exists($key,$info)){
					BaseconfigModel::field('data')->where(['name'=>$key])->update(['data'=>$value]);
				}else{
					BaseconfigModel::create(['name'=>$key,'data'=>$value]);
				}
			}
			return json(['status'=>200,'msg'=>'操作成功']);
		}
	}


	/**
	 *@description 修改信息之前查询信息的
	 *@buildcode(true)
	 */
	function getInfo(){
		$res = BaseconfigModel::column('data','name');
		$res['keyword'] = isset($res['keyword']) ? explode(',',$res['keyword']) : [];
		$res['water_alpha'] = (int)$res['water_alpha'];

		$data['status'] = 200;
		$data['data'] = $res;
		return json($data);
	}


}