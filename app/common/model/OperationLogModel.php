<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8
 * Time: 10:21
 */

namespace app\common\model;


use think\Model;

class OperationLogModel extends Model
{
    const ADMIN_TYPE_ADMIN      = 1;        //管理员
    const ADMIN_TYPE_AGENT      = 2;        //代理商
    const ADMIN_TYPE_CUSTOMER   = 3;        //客户

    const ACTION_ADD        = 'add';        //新增数据
    const ACTION_DEL        = 'del';        //删除数据
    const ACTION_UPDATE     = 'update';     //修改数据
    const ACTION_SELECT     = 'select';     //查询数据


    /**
     * 添加操作日志
     * @param $admin_id         //操作人ID
     * @param $admin_type       //操作人类别
     * @param $table            // 操作表
     * @param $action           // 动作
     * @param $data_id          // 数据ID
     */
    static public function addOperationLog($admin_id,$admin_type,$table,$action,$data_id)
    {
        self::insert([
            'admin_id'=>$admin_id,
            'admin_type'=>$admin_type,
            'table'=>$table,
            'action'=>$action,
            'data_id'=>$data_id,
            'time'=>date('Y-m-d H:i:s',time())
        ]);
    }

}