<?php

namespace app\admin\controller\Sys;

use think\exception\ValidateException;
use app\admin\controller\Sys\Build;
use app\admin\controller\Sys\model\Application;
use app\admin\controller\Sys\model\Field;
use app\admin\controller\Sys\model\Menu;
use app\admin\controller\Sys\model\Action;
use app\admin\controller\Admin;
use think\facade\Db;

class CreateMysql extends Admin
{
    //生成
    public function create()
    {
        $menu_id = $this->request->post('menu_id');
        $menuInfo = Menu::find($menu_id);
        $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
        $prefix = config('database.connections.'.$connect.'.prefix');

        if(config('database.connections.'.$connect.'.type') <> 'mysql'){
            throw new ValidateException('ai创建字段方法暂适用于mysql');
        }

        $fields = Db::name('field')->where('menu_id',$menu_id)->select()->toArray();

        foreach ($fields as &$field) {
            if (!in_array($field['datatype'],['varchar','int','smallint','text','decimal','tinyint','datetime','longtext'])) {
                $field['datatype'] = ['varchar','int','smallint','text','decimal','tinyint','datetime','longtext'][$field['datatype']-1];
            }

            if (!in_array($field['datatype'],['varchar','int','smallint','text','longtext','decimal','tinyint','datetime'])) {
                throw new ValidateException('ai创建字段类型有误：'.$field['datatype']);
            }
        }

        foreach ($fields as $data) {
            if ($menuInfo['pk'] == $data['field']) {
                continue;
            }
            if($data['create_table_field']){
                if($menuInfo['page_type'] == 1){
                    if((!empty($data['default_value']))){
                        if($data['type'] == 13){
                            $data['default_value'] = '0';
                        }
                        $default = "DEFAULT '".$data['default_value']."'";
                    }else{
                        $default = 'DEFAULT NULL';
                    }

                    if(in_array($data['datatype'],['datetime','longtext'])){
                        $data['length'] = ' null';
                    }else{
                        $data['length'] = "({$data['length']})";
                    }

                    $comment = self::getDescription($data);

                    // 检查字段是否存在
                    $checkField = Db::connect($connect)
                        ->query("SHOW COLUMNS FROM ".$prefix."{$menuInfo['table_name']} LIKE '{$data['field']}'");

                    if(empty($checkField)){
                        // 字段不存在，执行ADD操作
                        $sql = "ALTER TABLE ".$prefix."{$menuInfo['table_name']} ADD {$data['field']} {$data['datatype']}{$data['length']} COMMENT '{$comment}' {$default}";
                    } else {
                        // 字段存在，执行MODIFY操作
                        $sql = "ALTER TABLE ".$prefix."{$menuInfo['table_name']} MODIFY COLUMN {$data['field']} {$data['datatype']}{$data['length']} COMMENT '{$comment}' {$default}";
                    }

                    Db::connect($connect)->execute($sql);

                    if(!empty($data['indexdata'])){
                        Db::connect($connect)->execute("ALTER TABLE ".$prefix."{$menuInfo['table_name']} ADD ".$data['indexdata']." (  `".$data['field']."` )");
                    }
                }
                if($menuInfo['page_type'] == 2 && $data['type'] == 30){
                    // 检查字段是否存在
                    $checkField = Db::connect($connect)
                        ->query("SHOW COLUMNS FROM ".$prefix."{$menuInfo['table_name']} LIKE '{$data['field']}'");

                    if(empty($checkField)){
                        Db::connect($connect)->execute("ALTER TABLE `".$prefix.$menuInfo['table_name']."` ADD {$data['field']} VARCHAR(50)");
                    } else {
                        Db::connect($connect)->execute("ALTER TABLE `".$prefix.$menuInfo['table_name']."` MODIFY COLUMN {$data['field']} VARCHAR(50)");
                    }
                }
            }
        }

        return json(['status' => 200]);
    }


    private function getDescription($val){
        $description = '';
        if(in_array($val['type'],[2,3,4,5,6]) && !empty($val['item_config'])){
            if(is_array($val['item_config'])){
                foreach($val['item_config'] as $v){
                    $description .= $v['key'].'-'.$v['val'].' ; ';
                }
            }
        }
        return rtrim($val['title'].' , '.$description,' , ');
    }

}
