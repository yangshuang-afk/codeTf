<?php

namespace app\admin\controller;
use think\exception\FuncNotFoundException;
use think\exception\ValidateException;
use app\BaseController;
use think\facade\Db;


class Admin extends BaseController
{


	protected function initialize(){
		$controller = $this->request->controller();
		$action = $this->request->action();
		$app = app('http')->getName();

		$admin = session('admin');
        $userid = session('admin_sign') == data_auth_sign($admin) ? $admin['user_id'] : 0;
        
        if(!$userid && ($app <> 'admin' || $controller <> 'Login')){
			echo '<script type="text/javascript">top.parent.frames.location.href="'.url('admin/Login/index').'";</script>';exit();
        }
        
        $url =  "/{$app}/{$controller}/{$action}.html";
        if(session('admin.role_id') <> 1
            && !in_array($url,config('my.nocheck'))
            && !in_array($action,[
                'getExtends',
                'getInfo',
                'getFieldList',
                'addSchedule',
                'deleteSchedule',
                'getSchedules',
                'getInitData'
                ,'setAdminIndexStatus',
                'watermark',
                'loginEX',
                'preview',
            ])){
            if(!in_array($url,session('admin.access'))){
                throw new ValidateException ('你没操作权限');
            }
        }

		event('DoLog',session('admin.username'));	//写入操作日志

		$list = Db::name('base_config')->cache(true,60)->select()->column('data','name');
		config($list,'base_config');
		//验证唯一登录
		$this->checkSingleSession();
	}


	//返回当前应用的菜单列表
	protected function getBaseMenus(){
		$appname = app('http')->getName();
		$field = 'menu_id,pid,title,controller_name,status,icon,sortid,url';
		$list = db("menu")->field($field)->where(['status'=>1,'app_id'=>1])->order('sortid asc')->select()->toArray();
		if($list){
			foreach($list as $key=>$val){
				$menus[$key]['pid'] = $val['pid'];
				$menus[$key]['menu_id'] = $val['menu_id'];
				$menus[$key]['title'] = $val['title'];
				$menus[$key]['sortid'] = $val['sortid'];
				$menus[$key]['icon'] = $val['icon'] ? $val['icon'] : 'el-icon-menu';
				$menus[$key]['url'] = $this->getUrl($val,$appname);
				$menus[$key]['access'] = $val['url'] ? $val['url'] : $appname.'/'.$val['controller_name'];
			}
			return _generateListTree($menus,0,['menu_id','pid']);
		}
	}

	//获取url
	private function getUrl($val,$appname){
		if($val['url']){
			if(strpos($val['url'], '://')){
				$url = $val['url'];
			}else{
				$url = (string)url(ltrim(str_replace('.html','',$val['url']),'/'));
			}
			$appname = app('http')->getName();
			$mapping = '';
			foreach(config('app.app_map') as $k=>$v){
				if($v == $appname){
					$mapping = $k;
				}
			}
			if(!empty($mapping)){
				$url = str_replace($appname,$mapping,$url);
			}
		}else{
			$url = (string)url(getBaseUrl().'/'.str_replace('/','.',$val['controller_name']).'/index');
		}
		return $url;
	}


	//验证器 并且抛出异常
	protected function validate($data,$validate){
		try{
			validate($validate)->scene($this->request->action())->check($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}
		return true;
	}

	//格式化sql字段查询 转化为 key=>val 结构
	protected function query($sql,$connect='mysql'){
		preg_match_all('/select(.*)from/iUs',$sql,$all);
		if(!empty($all[1][0])){
			$sqlvalue = explode(',',trim($all[1][0]));
		}
		if(strpos($sql,'tkey') !== false){
			$sqlvalue[1] = 'tkey';
		}

		if(strpos($sql,'tval') !== false){
			$sqlvalue[0] = 'tval';
		}
		$sql = str_replace('pre_',config('database.connections.'.$connect.'.prefix'),$sql);
		$list = Db::connect($connect)->query($sql);
		$array = [];
		foreach($list as $k=>$v){
			$array[$k]['key'] = $v[trim($sqlvalue[1])];
			$array[$k]['val'] = $v[$sqlvalue[0]];
			if($sqlvalue[2]){
				$array[$k]['pid'] = $v[trim($sqlvalue[2])];
			}
		}
		return $array;
	}



	public function __call($method, $args){
        throw new FuncNotFoundException('方法不存在',$method);
    }

    function getRealClientIP() {
        $ip = '';

        // 检查是否使用了代理
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // 可能是多个IP地址列表，取第一个
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        // 验证IP地址格式
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
    	// 唯一登录检查每次
    public function checkSingleSession() {
        //if (!isset($_SESSION['admin.user_id'])) return;
        $dlbh = Db::name('base_config')->column('data','name');
        
        // dump($dlbh);
        // exit;
        
        
        if($dlbh['denglubaohu'] == 1) {
            
            $sessionuser_id = session('admin.user_id');
    
            if (!empty($sessionuser_id)){
                $storedToken = Db::name('admin_user')
                ->field('session_token')
                ->where('user_id', $sessionuser_id)
                ->value('session_token');
            
                if (session('user_token') !== $storedToken) {
                    // token不匹配，说明其他地方登录了
                    session('admin', null);
                    session('user_token', null);
            		session('admin_sign', null);
                    return json(['status' => 200]);
                }
                
            }else{
                    session('admin', null);
                    session('user_token', null);
            		session('admin_sign', null);
                    return json(['status' => 200]);
                
            }
            
        }
        

        

    }


}
