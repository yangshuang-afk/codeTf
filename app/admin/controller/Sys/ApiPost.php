<?php 
namespace app\admin\controller\Sys;
use app\admin\controller\Admin;
use think\facade\Db;
use think\exception\ValidateException;


class ApiPost extends Admin
{
	
	//接口请求域名，填写自己的
	private $url = 'http://tpnew.me'; 
	
	//apipost平台的api-token参数,apipost客户端左侧栏->项目设置->openApi  点开即可查看,复制即可  (注意下载经典版，不要下载协作版不然使用不了)
	private $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiVUdJT0Q1NTA4N0Q5IiwiaWFIjoxNzE1NDc3Mzc1fQ.NWsyahfi70Nz0blepwtrgwTAo4UBsxbe0YVvjkErwJU';
	
	private $application;
	
	private $fields;
	
	private $actions;
	
	private $menu;
	
	private $firstInfo;
	
	private $token_fields = [];
	
	
	//生成接口文档项目
	public function createDocProject(){
		$param = $this->request->post();
		if(empty($param['app_id'])){
			throw new ValidateException('app_id不能为空');
		}
		$info = Db::name('application')->where('app_id',$param['app_id'])->find();
		if($info['app_type'] <>2){
			throw new ValidateException('仅支持api应用');
		}
		if(!empty($info['project_id'])){
			throw new ValidateException('接口项目已经创建');
		}
		
		$teamInfo = $this->go_curl('https://openapi.apipost.cn/open/team/list?is_readonly=1','GET');
		if($teamInfo['code'] !== 10000){
			throw new ValidateException($teamInfo['msg']);
		}
				
		$data['name'] = $info['application_name'];
		$data['intro'] = $info['application_name'];
		$data['team_id'] = $teamInfo['data'][0]['team_id'];
		
		$res = $this->go_curl('https://openapi.apipost.cn/open/project/create','POST',$data);
		if($res['code'] !== 10000){
			throw new ValidateException($res['msg']);
		}
		try{
			Db::name('application')->where('app_id',$param['app_id'])->update(['project_id'=>$res['data']['project_id']]);
		}catch(\Exception $e){
			throw new ValidateException($e->getMessage());
		}
		
		return json(['status'=>200,'msg'=>'创建成功']);
	}
	
	//生成api
	public function createApi(){
		$param = $this->request->post();
		if(empty($param['menu_id'])){
			throw new ValidateException('menu_id不能为空');
		}
		
		$this->menu = Db::name("menu")->where('menu_id',$param['menu_id'])->find();
		$this->application = Db::name('application')->where('app_id',$this->menu['app_id'])->find();
		
		if(empty($this->application['project_id'])){
			throw new ValidateException('项目id不能为空');
		}
		
		$action = Db::name('action')->where('menu_id',$param['menu_id']);
		if($param['id']){
			$action = $action->where('id','in',$param['id']);
		}
		$this->actions = $action->order('sortid asc')->select()->toArray();
		
		$this->fields = Db::name('field')->where('menu_id',$param['menu_id'])->order('sortid asc')->select()->toArray();
		$this->firstInfo = Db::connect($this->menu['connect'])->name($this->menu['table_name'])->order($this->menu['pk'].' asc')->find();
		
		foreach($this->fields as $v){
			if($v['type'] == 30){
				array_push($this->token_fields,$v['field']);
			}
		}
		
		foreach($this->actions as $k=>$v){
			$res = $this->doApi($v,$k);
			if($res['code'] == 12000){
				throw new ValidateException($res['msg']);
			}
			if($res['code'] == 11095){
				$version = Db::name('action')->where('id',$v['id'])->value('version');
				Db::name('action')->where('id',$v['id'])->update(['version'=>$version+1]);
				$v['version'] = $v['version']+1;
				$ret = $this->doApi($v,$k);
			}
		}
		
		return json(['status'=>200,'msg'=>'生成成功']);
	}
	
	//开始生成
	private function doApi($val,$k){
		$data['target_id'] = md5($val['id'].'_'.$this->application['project_id'].'_'.$this->menu['menu_id'].'_'.$val['version']);
		$data['project_id'] = $this->application['project_id'];
		$data['parent_id']= '0';
		$data['mark'] = 'complated';
		$data['target_type'] = 'api';
		$data['name'] = $this->menu['title'].'-'.$val['name'];
		$data['method'] = 'POST';
		$data['sort'] = $val['menu_id']*100000+$val['id']*100+$k;
		$data['request']['url'] = $this->getUrl().'/'.str_replace('/','.',$this->menu['controller_name']).'/'.$val['action_name'];
		if($val['type'] == 50){
			$data['request']['description'] = '说明:token有效期'.config('my.tf_expire_time');
		}
		$data['request']['event'] = [
			'pre_script'=>'',
			'test'=>'',
		];
		$data['request']['auth']['type'] = 'noauth';
		$data['request']['pre_tasks'] = [];
		$data['request']['post_tasks'] = [];
		if($val['api_auth']){
			$data['request']['header'] = [
				'parameter'=>[
					[
						'description'=>'token值',
						'is_checked'=>1,
						'key'=>'Authorization',
						'type'=>'text',
						'not_null'=>1,
						'field_type'=>'String',
						'value'=>'1613887bd772e2b67c57bea93d622080923f3a51',
					],
				],
			];
		}
		$data['request']['body']['mode'] = 'urlencoded';
		$data['request']['body']['parameter'] = [];
		
		if($val['sms_auth']){
			array_push($data['request']['body']['parameter'],[
				'description'=>'短信验证码',
				'is_checked'=>1,
				'key'=>'verify',
				'type'=>'text',
				'not_null'=>1,
				'field_type'=>'string',
				'value'=>'123456',
			],[
				'description'=>'验证id,发送短信时返回参数',
				'is_checked'=>1,
				'key'=>'verify_id',
				'type'=>'text',
				'not_null'=>1,
				'field_type'=>'string',
				'value'=>'57bea93d622080923f3a51',
			],[
				'description'=>'手机号,必须同接收短信手机号一致',
				'is_checked'=>1,
				'key'=>'mobile',
				'type'=>'text',
				'not_null'=>1,
				'field_type'=>'string',
				'value'=>'135450280000',
			]);
		}
		
		if($val['img_auth']){
			array_push($data['request']['body']['parameter'],[
				'description'=>'图片验证码',
				'is_checked'=>1,
				'key'=>'captcha',
				'type'=>'text',
				'not_null'=>1,
				'field_type'=>'string',
				'value'=>'0f5q',
			],[
				'description'=>'验证码id',
				'is_checked'=>1,
				'key'=>'key_id',
				'type'=>'text',
				'not_null'=>1,
				'field_type'=>'string',
				'value'=>'57bea93d622080923f3a51',
			]);
		}

		switch($val['type']){
			case 1:
				array_push($data['request']['body']['parameter'],
					[
						'description'=>'每页显示条数，默认为1',
						'is_checked'=>0,
						'key'=>'limit',
						'type'=>'text',
						'not_null'=>0,
						'field_type'=>'Number',
						'value'=>'1',
					],
					[
						'description'=>'页码，默认为20',
						'is_checked'=>0,
						'key'=>'page',
						'type'=>'text',
						'not_null'=>0,
						'field_type'=>'Number',
						'value'=>(string)$val['pagesize'],
					],
				);
				foreach($this->fields as $v){
					if(in_array($v['search_type'],[1,2])){
						array_push($data['request']['body']['parameter'],[
							'description'=>$this->getDescription($v),
							'is_checked'=>0,
							'key'=>$v['field'],
							'type'=>'text',
							'not_null'=>0,
							'field_type'=>$this->getSearchDataType($v),
							'value'=>$this->getSearchDefaultValue($v),
						]);
					}
				}
			break;
			
			case 2:
				if($val['fields']){
					foreach(explode(',',$val['fields']) as $vv){
						foreach($this->fields as $v){
							if($v['field'] == $vv && $v['post_status'] && !in_array($v['type'],[11,12,30])){
								array_push($data['request']['body']['parameter'],[
									'description'=>$this->getDescription($v),
									'is_checked'=>1,
									'key'=>$v['field'],
									'type'=>'text',
									'not_null'=>$this->getRequired($v),
									'field_type'=>$this->getDataType($v),
									'value'=>$this->getPostDefaultValue($v),
								]);
							}
						}
					}
				}else{
					foreach($this->fields as $v){
						if($v['post_status'] && !in_array($v['type'],[11,12,30])){
							array_push($data['request']['body']['parameter'],[
								'description'=>$this->getDescription($v),
								'is_checked'=>1,
								'key'=>$v['field'],
								'type'=>'text',
								'not_null'=>$this->getRequired($v),
								'field_type'=>$this->getDataType($v),
								'value'=>$this->getPostDefaultValue($v),
							]);
						}
					}
				}
			break;
			
			case 3:
				if(!$val['api_auth'] || !in_array($this->menu['pk'],$this->token_fields)){
					array_push($data['request']['body']['parameter'],[
						'description'=>'编号',
						'is_checked'=>1,
						'key'=>$this->menu['pk'],
						'type'=>'text',
						'not_null'=>1,
						'field_type'=>'Number',
						'value'=>'1',
					]);
				}
				if($val['fields']){
					foreach(explode(',',$val['fields']) as $vv){
						foreach($this->fields as $v){
							if($v['field'] == $vv && $v['post_status'] && !in_array($v['type'],[7,11,12,30])){
								array_push($data['request']['body']['parameter'],[
									'description'=>$this->getDescription($v),
									'is_checked'=>1,
									'key'=>$v['field'],
									'type'=>'text',
									'not_null'=>$this->getRequired($v),
									'field_type'=>$this->getDataType($v),
									'value'=>$this->getPostDefaultValue($v),
								]);
							}
						}
					}
				}else{
					foreach($this->fields as $v){
						if($v['post_status'] && !in_array($v['type'],[7,11,12,30])){
							array_push($data['request']['body']['parameter'],[
								'description'=>$this->getDescription($v),
								'is_checked'=>1,
								'key'=>$v['field'],
								'type'=>'text',
								'not_null'=>$this->getRequired($v),
								'field_type'=>$this->getDataType($v),
								'value'=>$this->getPostDefaultValue($v),
							]);
						}
					}
				}
			break;
			
			case 4:
				if(!$val['api_auth'] || !in_array($this->menu['pk'],$this->token_fields)){
					array_push($data['request']['body']['parameter'],[
						'description'=>'编号',
						'is_checked'=>1,
						'key'=>$this->menu['pk'],
						'type'=>'text',
						'not_null'=>1,
						'field_type'=>'Number',
						'value'=>'1',
					]);
				}
			break;
			
			case 5:
				$other_config = json_decode($val['other_config'],true);
				if(isset($other_config['detail_search_field']) && count($other_config['detail_search_field']) > 0){
					foreach($other_config['detail_search_field'] as $vv){
						foreach($this->fields as $v){
							if($v['field'] == $vv){
								array_push($data['request']['body']['parameter'],[
									'description'=>$this->getDescription($v),
									'is_checked'=>1,
									'key'=>$v['field'],
									'type'=>'text',
									'not_null'=>$this->getRequired($v),
									'field_type'=>$this->getDataType($v),
									'value'=>$this->getPostDefaultValue($v),
								]);
							}
						}
					}
				}else{
					if(!$val['api_auth'] || !in_array($this->menu['pk'],$this->token_fields)){
						array_push($data['request']['body']['parameter'],[
							'description'=>'编号',
							'is_checked'=>1,
							'key'=>$this->menu['pk'],
							'type'=>'text',
							'not_null'=>1,
							'field_type'=>'Number',
							'value'=>'1',
						]);
					}
				}
			break;
			
			case 6:
				if(!$val['api_auth'] || !in_array($this->menu['pk'],$this->token_fields)){
					array_push($data['request']['body']['parameter'],[
						'description'=>'编号',
						'is_checked'=>1,
						'key'=>$this->menu['pk'],
						'type'=>'text',
						'not_null'=>1,
						'field_type'=>'Number',
						'value'=>'1',
					]);
				}
				array_push($data['request']['body']['parameter'],[
					'description'=>'密码',
					'is_checked'=>1,
					'key'=>'password',
					'type'=>'text',
					'not_null'=>1,
					'field_type'=>'String',
					'value'=>'123456',
				]);
			break;
			
			case 7:
				if(!$val['api_auth'] || !in_array($this->menu['pk'],$this->token_fields)){
					array_push($data['request']['body']['parameter'],[
						'description'=>'编号',
						'is_checked'=>1,
						'key'=>$this->menu['pk'],
						'type'=>'text',
						'not_null'=>1,
						'field_type'=>'Number',
						'value'=>'1',
					]);
				}
			break;
			
			case 8:
				if(!$val['api_auth'] || !in_array($this->menu['pk'],$this->token_fields)){
					array_push($data['request']['body']['parameter'],[
						'description'=>'编号',
						'is_checked'=>1,
						'key'=>$this->menu['pk'],
						'type'=>'text',
						'not_null'=>1,
						'field_type'=>'Number',
						'value'=>'1',
					]);
				}
				foreach($this->fields as $v){
					if($v['field'] == $val['fields']){
						array_push($data['request']['body']['parameter'],[
							'description'=>$v['title'],
							'is_checked'=>1,
							'key'=>$v['field'],
							'type'=>'text',
							'not_null'=>1,
							'field_type'=>$this->getDataType($v),
							'value'=>'5',
						]);
					}
				}
			break;
			
			case 9:
				if(!$val['api_auth'] || !in_array($this->menu['pk'],$this->token_fields)){
					array_push($data['request']['body']['parameter'],[
						'description'=>'编号',
						'is_checked'=>1,
						'key'=>$this->menu['pk'],
						'type'=>'text',
						'not_null'=>1,
						'field_type'=>'Number',
						'value'=>'1',
					]);
				}
				foreach($this->fields as $v){
					if($v['field'] == $val['fields']){
						array_push($data['request']['body']['parameter'],[
							'description'=>$v['title'],
							'is_checked'=>1,
							'key'=>$v['field'],
							'type'=>'text',
							'not_null'=>1,
							'field_type'=>$this->getDataType($v),
							'value'=>'5',
						]);
					}
				}
			break;
			
			case 50:
				$other_config = json_decode($val['other_config'],true);
				if($other_config['login_type'] == 1){
					array_push($data['request']['body']['parameter'],[
						'description'=>'登录账号',
						'is_checked'=>1,
						'key'=>$other_config['userField'],
						'type'=>'text',
						'not_null'=>1,
						'field_type'=>'String',
						'value'=>'zhansgan',
					],[
						'description'=>'登录密码',
						'is_checked'=>1,
						'key'=>$other_config['pwdField'],
						'type'=>'text',
						'not_null'=>1,
						'field_type'=>'String',
						'value'=>'123456',
					]);
				}else{
					if(!$val['sms_auth']){
						array_push($data['request']['body']['parameter'],[
							'description'=>'登录手机号',
							'is_checked'=>1,
							'key'=>$other_config['smsField'],
							'type'=>'text',
							'not_null'=>1,
							'field_type'=>'String',
							'value'=>'13545028000',
						]);
					}
				}
			break;
			
			case 51:
				array_push($data['request']['body']['parameter'],[
					'description'=>'手机号',
					'is_checked'=>1,
					'key'=>'mobile',
					'type'=>'text',
					'not_null'=>1,
					'field_type'=>'String',
					'value'=>'13545028000',
				]);
			break;
		}
		$data['request']['body']['raw'] = '';
		$data['request']['body']['raw_para'] = [];
		$data['request']['body']['raw_schema']['type'] = 'object';
		switch($val['type']){
			case 1:
				$raw = '{"status":200,"data":{"total":10,"per_page":20,"current_page":1,"last_page":1,"data":[]}}';
			break;
			
			case 2:
				$raw = '{"status":200,"data":1,"msg":"添加成功"}';
			break;
			
			case 3:
				$raw = '{"status":200,"msg":"修改成功"}';
			break;
			
			case 4:
				$raw = '{"status":200,"msg":"操作成功"}';
			break;
			
			case 5:
				$raw = '{"status":200,"data":{}}';
			break;
			
			case 6:
				$raw = '{"status":200,"msg":"操作成功"}';
			break;
			
			case 7:
				$raw = '{"status":200,"msg":"操作成功"}';
			break;
			
			case 8:
				$raw = '{"status":200}';
			break;
			
			case 9:
				$raw = '{"status":200}';
			break;
			
			case 50:
				$raw  = '{"status":200,"data":{},"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjbGllbnQudnVlYWRtaW4iLCJhdWQiOiJzZXJ2ZXIudnVlYWRtaW4iLCJpYXQiOjE2OTg2NDQ1NjQuOTcyMjI3LCJuYmYiOjE2OTg2NDQ1NjQuOTcyMjI3LCJleHAiOjE2OTkwNzY1NjQuOTcyMjI3LCJ1aWQiOiJ7XCJtZW1iZV9pZFwiOjI3LFwidXNlcm5hbWVcIjpcIlxcdTczOGJcXHU0ZTk0XCIsXCJzZXhcIjoyLFwibW9iaWxlXCI6XCIxMzU0NTA5ODc2MVwiLFwicGljXCI6XCJodHRwOlxcL1xcL3Z1ZS53aHBqLnZpcFxcL3VwbG9hZHNcXC9hZG1pblxcLzIwMjEwNVxcLzYwYWY4ZDdkNDdhTAuanBnXCIsXCJlbWFpbFwiOlwiMzY3MzI3NjdAcXEuY29tXCJ9In0.lBbgBmOstAAZQX9kN_BvYUHYp3Ff-mibzmmuDVaUENc"}';
			break;
			
			case 51:
				$raw = '{"status":200,"msg":"发送成功",key:"57bea93d622080923f3a51"}';
			break;
		}
		
		$data['response']['success']['raw'] = $raw;
		$data['response']['success']['parameter'] = [];
		
		switch($val['type']){
			case 1:
				foreach($this->fields as $v){
					if($v['list_show'] == 1){
						array_push($data['response']['success']['parameter'],[
							'description'=>$this->getDescription($v),
							'is_checked'=>1,
							'key'=>$v['field'],
							'type'=>'text',
							'not_null'=>1,
							'field_type'=>$this->getDataType($v),
							'value'=>'',
						]);
					}
				}
			break;
			
			case 5:
				if($val['fields']){
					foreach(explode(',',$val['fields']) as $vv){
						foreach($this->fields as $v){
							if($v['field'] == $vv){
								array_push($data['response']['success']['parameter'],[
									'description'=>$this->getDescription($v),
									'is_checked'=>1,
									'key'=>$v['field'],
									'type'=>'text',
									'not_null'=>1,
									'field_type'=>$this->getDataType($v),
									'value'=>'',
								]);
							}
						}
					}
				}else{
					foreach($this->fields as $v){
						if($v['list_show'] == 1){
							array_push($data['response']['success']['parameter'],[
								'description'=>$this->getDescription($v),
								'is_checked'=>1,
								'key'=>$v['field'],
								'type'=>'text',
								'not_null'=>1,
								'field_type'=>$this->getDataType($v),
								'value'=>'',
							]);
						}
					}
				}
			break;
			
			case 14:
				foreach($this->fields as $v){
					if($v['list_show'] == 1){
						array_push($data['response']['success']['parameter'],[
							'description'=>$this->getDescription($v),
							'is_checked'=>1,
							'key'=>$v['field'],
							'type'=>'text',
							'not_null'=>1,
							'field_type'=>$this->getDataType($v),
							'value'=>'',
						]);
					}
				}
			break;
		}
		
		$data['response']['success']['expect'] = [
			'name'=>'成功',
			'isDefault'=>1,
			'code'=>200,
			'contentType'=>'json',
			'verifyType'=>'schema',
			'mock'=>'',
			'schema'=>[
				'type'=>'object',
			],
		];
		$data['response']['error'] = [
			'raw'=>'',
			'parameter'=>[],
			'expect'=>[
				'name'=>'失败',
				'isDefault'=>-1,
				'code'=>404,
				'contentType'=>'json',
				'verifyType'=>'schema',
				'mock'=>'',
				'schema'=>[
					'type'=>'object',
				],
			],
		];
		$res = $this->go_curl('https://openapi.apipost.cn/open/apis/save_target','POST',$data);
		return $res;
	}
	
	//获取字段描述
	function getDescription($val){
		$description = '';
		if(in_array($val['type'],[2,3,4,5,6]) && !empty($val['item_config'])){
			$data = json_decode($val['item_config'],true);
			if(is_array($data)){
				foreach($data as $v){
					$description .= $v['key'].'-'.$v['val'].' ; ';
				}
			}
		}	
		return rtrim($val['title'].' , '.$description,' , ');
	}
	
	
	//获取搜索默认参数值
	function getSearchDefaultValue($val){
		$data = '';
		if(in_array($val['type'],[2,3,4,5,6])){
			if(!empty($val['item_config'])){
				$data = json_decode($val['item_config'],true)[0]['val'];
			}
		}else if($val['type'] == 7){
			$data = '123456';
		}else if(in_array($val['type'],[11,12])){
			$data = '[2023-01-01 12:00:00,2023-01-05 12:00:00]';
		}else{
			$data = $this->firstInfo[$val['field']];
		}
		return $data;
	}
	
	//获取提交默认参数值
	function getPostDefaultValue($val){
		$data = '';
		if(in_array($val['type'],[2,3,4,5,6])){
			if(!empty($val['item_config'])){
				$data = json_decode($val['item_config'],true)[0]['val'];
			}
		}else if($val['type'] == 7){
			$data = '123456';
		}else if($val['type'] == 9){
			$data = '2023-01-01 12:00:00';
		}else if(in_array($val['type'],[14,16])){
			$data = '[{"url":"a.jpg","name":"文件名称"},{"url":"b.jpg","name":"文件名称2.jpg"}]';
		}else if(in_array($val['type'],[25,26,27])){
			$data = '测试编辑器内容';
		}else{
			$data = $this->firstInfo[$val['field']];
		}
		return $data;
	}
	
	//判断是否必填字段
	function getRequired($val){
		$status = 0;
		if(!empty($val['validate'])){
			$data = explode(',',$val['validate']);
			if(in_array('notempty',$data)){
				$status = 1;
			}
		}
		return $status;
	}
	
	//获取文档数据类型
	function getSearchDataType($val){
		if(in_array($val['type'],[11,12])){
			$data = 'Array';
		}else{
			if(in_array($val["datatype"],["varchar","char","text","tinytext","smalltext","longtext"])){
				$data = "String";
			}elseif(in_array($val["datatype"],["tinyint","int","smallint","longint"])){
				$data = "Number";
			}elseif(in_array($val["datatype"],["decimal"])){
				$data = "Float";
			}		
		}
		return $data;
	}
	
	//获取文档数据类型
	function getDataType($val){
		if(in_array($val["datatype"],["varchar","char","text","tinytext","smalltext","longtext"])){
			$data = "String";
		}elseif(in_array($val["datatype"],["tinyint","int","smallint","longint"])){
			$data = "Number";
		}elseif(in_array($val["datatype"],["decimal"])){
			$data = "Float";
		}		
		return $data;
	}
	
	//获取访问地址
	function getUrl(){
		$url = $this->url.'/'.$this->application['app_dir'];
		foreach(config('app.domain_bind') as $v){
			if($this->application['app_dir'] == $v){
				$url = $this->url;
			}
		}
		return $url;
	}
	
	//curl请求方法
	private function go_curl($url, $type, $data = false, &$err_msg = null, $timeout = 20, $cert_info = array()){
		$type = strtoupper($type);
		if ($type == 'GET' && is_array($data)) {
			$data = http_build_query($data);
		}
		$option = array();
		if ( $type == 'POST' ) {
			$option[CURLOPT_POST] = 1;
		}
		if ($data) {
			if ($type == 'POST') {
				$option[CURLOPT_POSTFIELDS] = json_encode($data);
			} elseif ($type == 'GET') {
				$url = strpos($url, '?') !== false ? $url.'&'.$data :  $url.'?'.$data;
			}
		}
		$option[CURLOPT_URL]            = $url;
		$option[CURLOPT_HTTPHEADER]     = array(
			'Content-Type: application/json',"api-token:".$this->token,
		);
		$option[CURLOPT_FOLLOWLOCATION] = TRUE;
		$option[CURLOPT_MAXREDIRS]      = 4;
		$option[CURLOPT_RETURNTRANSFER] = TRUE;
		$option[CURLOPT_TIMEOUT]        = $timeout;
		//设置证书信息
		if(!empty($cert_info) && !empty($cert_info['cert_file'])) {
			$option[CURLOPT_SSLCERT]       = $cert_info['cert_file'];
			$option[CURLOPT_SSLCERTPASSWD] = $cert_info['cert_pass'];
			$option[CURLOPT_SSLCERTTYPE]   = $cert_info['cert_type'];
		}
		//设置CA
		if(!empty($cert_info['ca_file'])) {
			// 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
			$option[CURLOPT_SSL_VERIFYPEER] = 1;
			$option[CURLOPT_CAINFO] = $cert_info['ca_file'];
		} else {
			// 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
			$option[CURLOPT_SSL_VERIFYPEER] = 0;
		}
		$ch = curl_init();
		curl_setopt_array($ch, $option);
		$response = curl_exec($ch);
		$curl_no  = curl_errno($ch);
		$curl_err = curl_error($ch);
		curl_close($ch);
		// error_log
		if($curl_no > 0) {
			if($err_msg !== null) {
				$err_msg = '('.$curl_no.')'.$curl_err;
			}
		}
		return json_decode($response,true);
	}
	
	
}

