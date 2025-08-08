<?php

namespace app\admin\controller;

class MyConfig extends Admin
{
    
    
    /**
     * @description 配置表单
     * @buildcode(true)
     */
    public function index()
    {
        if (!$this->request->isPost()) {
            return view('index');
        } else {
            $configData = $this->request->post();
            try {
                // 处理微信支付证书路径
                if (isset($configData['wechart_pay'])) {
                    $configData['wechart_pay'] = $this->handleWechatPayPaths($configData['wechart_pay']);
                }
                
                // 生成配置文件内容
                $content = $this->generateConfigContent($configData);
                
                // 配置文件路径 (默认放在config目录下)
                $configPath = app()->getConfigPath() . 'my.php';
                
                // 写入文件
                $result = file_put_contents($configPath, $content);
                
                if ($result === false) {
                    throw new \Exception('配置文件写入失败，请检查文件权限');
                }
                
                return json([
                    'status' => 200,
                    'msg' => '配置更新成功'
                ]);
            } catch (\Exception $e) {
                return json([
                    'status' => 500,
                    'msg' => $e->getMessage()
                ]);
            }
        }
    }
    
    
    /**
     * @description 修改信息之前查询信息的
     * @buildcode(true)
     */
    function getInfo()
    {
        $data['status'] = 200;
        $data['data'] = config('my');
        return json($data);
    }
    
    /**
     * 处理微信支付证书路径
     * 当路径前缀与项目根目录相同时，替换为动态路径
     * @param array $wechartPay 微信支付配置数组
     * @return array 处理后的配置数组
     */
    private function handleWechatPayPaths(array $wechartPay): array
    {
        $rootPath = app()->getRootPath();
        
        // 处理证书路径
        if (!empty($wechartPay['cert_path'])) {
            $wechartPay['cert_path'] = $this->replaceRootPath($wechartPay['cert_path'], $rootPath);
        }
        
        // 处理密钥路径
        if (!empty($wechartPay['key_path'])) {
            $wechartPay['key_path'] = $this->replaceRootPath($wechartPay['key_path'], $rootPath);
        }
        
        return $wechartPay;
    }
    
    /**
     * 替换路径中的根目录为动态获取方式
     * @param string $path 原始路径
     * @param string $rootPath 项目根目录
     * @return string 处理后的路径
     */
    private function replaceRootPath(string $path, string $rootPath): string
    {
        // 标准化路径分隔符，统一使用/
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedRoot = rtrim(str_replace('\\', '/', $rootPath), '/');
        
        // 检查路径是否以根目录开头
        if (strpos($normalizedPath, $normalizedRoot) === 0) {
            // 提取相对路径部分
            $relativePath = substr($normalizedPath, strlen($normalizedRoot));
            // 返回动态路径表达式标记（后续在生成配置时会特殊处理）
            return '__ROOT_PATH__' . $relativePath;
        }
        
        // 路径不匹配时返回原始路径
        return $path;
    }
    
    
    /**
     * 生成配置文件内容
     * @param array $data
     * @return string
     */
    private function generateConfigContent(array $data): string
    {
        // 处理数组类型的nocheck
        $nocheck = isset($data['nocheck']) ? $data['nocheck'] : [];
        if (is_string($nocheck)) {
            $nocheck = explode("\n", $nocheck);
            $nocheck = array_filter(array_map('trim', $nocheck));
        }
        
        // 处理嵌套数组
        $miniProgram = isset($data['mini_program']) ? $data['mini_program'] : [];
        $officialAccounts = isset($data['official_accounts']) ? $data['official_accounts'] : [];
        $wechartPay = isset($data['wechart_pay']) ? $data['wechart_pay'] : [];
        
        // 模板内容
        $template = <<<PHP
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 自定义配置
// +----------------------------------------------------------------------
return [
    'upload_subdir'         => '{$this->escapeValue($data['upload_subdir'] ?? 'Ym')}',                //文件上传二级目录 标准的日期格式
    'nocheck'               => {$this->formatArray($nocheck)},    //不需要验证权限的url
    'error_log_code'        => {$this->escapeValue($data['error_log_code'] ?? 500)},                 //写入日志的状态码
    'password_secrect'      => '{$this->escapeValue($data['password_secrect'] ?? 'tfadmin')}',             //密码加密秘钥
    'multiple_login'        => {$this->formatBoolean($data['multiple_login'] ?? true)},                 //后台单点登录 true 允许多个账户登录 false 只允许一个账户登录
    'dump_extension'        => '{$this->escapeValue($data['dump_extension'] ?? 'xlsx')}',                //默认导出格式
    'verify_status'         => {$this->formatBoolean($data['verify_status'] ?? false)},                  //后台登录验证码开关
    'water_img'             => '{$this->escapeValue($data['water_img'] ?? '')}',                    //水印图片路径
    'check_file_status'     => {$this->formatBoolean($data['check_file_status'] ?? true)},              //上传图片是否检测图片存在
    'show_home_chats'       => {$this->formatBoolean($data['show_home_chats'] ?? true)},                //是否显示首页图表
    'api_upload_auth'       => {$this->formatBoolean($data['api_upload_auth'] ?? true)},                //api应用上传是否验证token

    //腾讯云短信配置
    'tencent_sms_appid'     => '{$this->escapeValue($data['tencent_sms_appid'] ?? '')}',            //appiid
    'tencent_sms_appkey'    => '{$this->escapeValue($data['tencent_sms_appkey'] ?? '')}',           //appkey
    'tencent_sms_tempCode'  => '{$this->escapeValue($data['tencent_sms_tempCode'] ?? '')}',         //短信模板id
    'tencent_sms_signname'  => '{$this->escapeValue($data['tencent_sms_signname'] ?? 'tfadmin')}',         //短信签名
    
    //阿里云短信配置
    'ali_sms_accessKeyId'       => '{$this->escapeValue($data['ali_sms_accessKeyId'] ?? '')}',       //阿里云短信 keyId
    'ali_sms_accessKeySecret'   => '{$this->escapeValue($data['ali_sms_accessKeySecret'] ?? '')}',   //阿里云短信 keysecret
    'ali_sms_signname'          => '{$this->escapeValue($data['ali_sms_signname'] ?? 'tfadmin')}',          //签名
    'ali_sms_tempCode'          => '{$this->escapeValue($data['ali_sms_tempCode'] ?? '')}',                  //短信模板 Code
    
    //oss开启状态 以及配置指定oss
    'oss_status'            => {$this->formatBoolean($data['oss_status'] ?? false)},            //true启用  false 不启用
    'oss_upload_type'       => '{$this->escapeValue($data['oss_upload_type'] ?? 'server')}',    //client 客户端直传  server 服务端传
    'oss_default_type'      => '{$this->escapeValue($data['oss_default_type'] ?? 'ali')}',      //oss使用类别
    
    //阿里云oss配置
    'ali_oss_accessKeyId'       => '{$this->escapeValue($data['ali_oss_accessKeyId'] ?? '')}',       //阿里云 keyId
    'ali_oss_accessKeySecret'   => '{$this->escapeValue($data['ali_oss_accessKeySecret'] ?? '')}',   //阿里云 keysecret
    'ali_oss_endpoint'          => '{$this->escapeValue($data['ali_oss_endpoint'] ?? '')}',           //建议填写自己绑定的域名
    'ali_oss_bucket'            => '{$this->escapeValue($data['ali_oss_bucket'] ?? 'tfadmin')}',
    
    //七牛云oss配置
    'qny_oss_accessKey'         => '{$this->escapeValue($data['qny_oss_accessKey'] ?? '')}',  //access_key
    'qny_oss_secretKey'         => '{$this->escapeValue($data['qny_oss_secretKey'] ?? '')}',     //secret_key
    'qny_oss_bucket'            => '{$this->escapeValue($data['qny_oss_bucket'] ?? 'tfadmin')}',   //bucket
    'qny_oss_domain'            => '{$this->escapeValue($data['qny_oss_domain'] ?? '')}',    //七牛云访问的域名
    'qny_oss_client_uploadurl'  => '{$this->escapeValue($data['qny_oss_client_uploadurl'] ?? 'http://up-z0.qiniup.com')}',    //七牛云客户端直传地址
    
    //腾讯云cos配置
    'tencent_oss_secretId'      => '{$this->escapeValue($data['tencent_oss_secretId'] ?? '')}',      //腾讯云keyId
    'tencent_oss_secretKey'     => '{$this->escapeValue($data['tencent_oss_secretKey'] ?? '')}',     //腾讯云keysecret
    'tencent_oss_bucket'        => '{$this->escapeValue($data['tencent_oss_bucket'] ?? '')}',        //腾讯云bucket
    'tencent_oss_region'        => '{$this->escapeValue($data['tencent_oss_region'] ?? '')}',        //地区
    'tencent_oss_schema'        => '{$this->escapeValue($data['tencent_oss_schema'] ?? 'http')}',    //访问前缀
    
    //api tf鉴权配置
    'tf_expire_time'        => '{$this->escapeValue($data['tf_expire_time'] ?? '+7 day')}',         //过期时间
    'tf_secrect'            => '{$this->escapeValue($data['tf_secrect'] ?? 'KW11FbeWB3YKi0aGS0TxcHbCakmNeDnAj3DMrjxxnP5rdwxTxYb8irWZGZ5hYY7S')}',   //签名秘钥
    'tf_iss'                => '{$this->escapeValue($data['tf_iss'] ?? 'client.tfadmin')}',    //发送端
    'tf_aud'                => '{$this->escapeValue($data['tf_aud'] ?? 'server.tfadmin')}',    //接收端
    'tfExpireCode'          => {$this->escapeValue($data['tfExpireCode'] ?? 101)},              //tf过期代码
    'tfErrorCode'           => {$this->escapeValue($data['tfErrorCode'] ?? 102)},               //tf无效代码
    
    //小程序配置
    'mini_program'          => [
        'app_id' => '{$this->escapeValue($miniProgram['app_id'] ?? '')}',                 //小程序appid
        'secret' => '{$this->escapeValue($miniProgram['secret'] ?? '')}',                 //小程序secret
    ],
    
    //公众号配置
    'official_accounts'     => [
        'app_id'        => '{$this->escapeValue($officialAccounts['app_id'] ?? '')}',           //公众号appid
        'secret'        => '{$this->escapeValue($officialAccounts['secret'] ?? '')}',           //公众号secret
        'token'         => '{$this->escapeValue($officialAccounts['token'] ?? '')}',            //token
        'aes_key'       => '{$this->escapeValue($officialAccounts['aes_key'] ?? '')}',          //EncodingAESKey
    ],
    
    //微信支付配置
    'wechart_pay'           => [
        'mch_id'         => '{$this->escapeValue($wechartPay['mch_id'] ?? '')}',                //商户号
        'key'            => '{$this->escapeValue($wechartPay['key'] ?? '')}',                   //微信支付32位秘钥
        'cert_path'      => {$this->escapePath($wechartPay['cert_path'] ?? app()->getRootPath() . 'extend/utils/wechart/zcerts/apiclient_cert.pem')},  //证书路径
        'key_path'       => {$this->escapePath($wechartPay['key_path'] ?? app()->getRootPath() . 'extend/utils/wechart/zcerts/apiclient_key.pem')},    //证书路径
    ],
];
PHP;
        
        return $template;
    }
    
    /**
     * 转义字符串值，防止单引号冲突
     * @param mixed $value
     * @return string
     */
    private function escapeValue($value): string
    {
        if (is_string($value)) {
            return addslashes($value);
        }
        return (string)$value;
    }
    
    /**
     * 处理路径值，特别是包含动态根目录的路径
     * @param mixed $path
     * @return string
     */
    private function escapePath($path): string
    {
        // 处理标记为根目录路径的情况
        if (is_string($path) && strpos($path, '__ROOT_PATH__') === 0) {
            $relativePath = substr($path, strlen('__ROOT_PATH__'));
            return "app()->getRootPath() . '" . addslashes($relativePath) . "'";
        }
        
        // 处理默认路径（已经是动态路径的情况）
        if (is_string($path) && strpos($path, 'app()->getRootPath()') !== false) {
            return $path;
        }
        
        // 普通路径处理
        return "'" . addslashes($path) . "'";
    }
    
    /**
     * 格式化布尔值为PHP语法
     * @param mixed $value
     * @return string
     */
    private function formatBoolean($value): string
    {
        return $value ? 'true' : 'false';
    }
    
    /**
     * 格式化数组为PHP语法
     * @param array $array
     * @return string
     */
    private function formatArray(array $array): string
    {
        if (empty($array)) {
            return '[]';
        }
        
        $items = [];
        foreach ($array as $item) {
            $items[] = "'" . addslashes($item) . "'";
        }
        
        return "[\n        " . implode(",\n        ", $items) . "\n    ]";
    }
}
