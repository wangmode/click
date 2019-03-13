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
    static private function addOperationLog($admin_id,$admin_type,$table,$action,$data_id,$data)
    {
        self::insert([
            'table'         =>$table,
            'action'        =>$action,
            'data_id'       =>$data_id,
            'admin_id'      =>$admin_id,
            'admin_type'    =>$admin_type,
            'data'          =>json_encode($data),
            'time'          =>date('Y-m-d H:i:s',time())
        ]);
    }

    /**
     * 添加管理员操作日志
     * @param $table
     * @param $action
     * @param $data_id
     */
    static public function adminAddOperationLog($table,$action,$data_id,$data)
    {
        $admin_id = cmf_get_current_admin_id();
        self::addOperationLog($admin_id,self::ADMIN_TYPE_ADMIN,$table,$action,$data_id,$data);
    }

    /**
     * 添加代理商操作日志
     * @param $table
     * @param $action
     * @param $data_id
     */
    static public function agentAddOperationLog($table,$action,$data_id,$data)
    {
        $admin_id = cmf_get_current_user_id();
        self::addOperationLog($admin_id,self::ADMIN_TYPE_AGENT,$table,$action,$data_id,$data);
    }

    /**
     * 添加客户操作日志
     * @param $table
     * @param $action
     * @param $data_id
     */
    static public function customerAddOperationLog($table,$action,$data_id,$data)
    {
        $admin_id = cmf_get_current_user_id();
        self::addOperationLog($admin_id,self::ADMIN_TYPE_CUSTOMER,$table,$action,$data_id,$data);
    }



}