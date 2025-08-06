<?php 

namespace app\admin\controller\Sys\validate;
use think\validate;

class Field extends validate {


	protected $rule = [
		'title'=>['require'],
		'field'=>['require'],
		'type'=>'checkType',
	];
	

	protected $message = [
		'title.require'=>'字段中文名不能为空',
		'field.require'=>'字段英文名不能为空',
		'type.require'=>'字段类型不能为空',
	];

	
	//检测字段类型
    protected function checkType($value, $rule, $data=[])
    {
		if(in_array($data['type'],[2,3,4,5,6])){
			if(empty($data['item_config']) && empty($data['sql'])){
				$msg = '请配置选项';
			}else{
				foreach($data['item_config'] as $v){
					if(empty($v['val']) && $v['val'] <> '0'){
						$msg = '选项值有空值';
					}
				}
			}
			return $msg ? $msg : true;
		}else if($data['type'] == 31){
			if(empty($data['other_config']['rand_length'])){
				$msg = '请配置随机数长度';
			}
			if($data['validate']){
				if(in_array('notempty',$data['validate'])){
					$msg = '该选项后端生成数据，不支持设置数据不为空';
				}
			}
			return $msg ? $msg : true;
		}else if(in_array($data['type'],[11,12,30,32,34,36])){
			if($data['validate']){
				if(in_array('notempty',$data['validate'])){
					$msg = '该选项后端生成数据，不支持设置数据不为空';
				}
			}
			return $msg ? $msg : true;
		}elseif($data["type"] == 38){
			if(empty($data['other_config']['application_id'])){
				$msg = '请选择应用';
			}
			return $msg ? $msg : true;
		}elseif($data["type"] == 39){
			$guige = $data['other_config']['guige'];
			if(count($guige) ==0){
				$msg = '请配置选项';
			}
			foreach($guige as $k=>$v){
				if(empty($v['title']) || empty($v['field']) || empty($v['type'])){
					$msg = '选项内容不能为空';
				}
				if($v['type'] == 2 && empty($v['item'])){
					$msg = '下拉选项内容不能为空';
				}
			}
			return $msg ? $msg : true;
		}elseif($data["type"] == 40){
			if(empty($data['other_config']['jisuan'])){
				$msg = '请设置计算公式';
			}
			return $msg ? $msg : true;
		}else{
			return true;
		}
    }
	
	protected $scene  = [
		'createField'=>['title','field','type'],
		'updateField'=>['name','field','type'],
	];
	
	
	

}

