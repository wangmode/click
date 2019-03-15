<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/14
 * Time: 11:36
 */

namespace app\common\model;


use think\Model;

class CustomerAccountLogModel extends Model
{

    /**
     * 添加客户充值记录
     * @param $agent_id
     * @param $customer_id
     * @param $money
     */
    static public function addCustomerAccountLog($agent_id,$customer_id,$money)
    {
        self::insert([
            'money'=>$money,
            'agent_id'=> $agent_id,
            'customer_id'=>$customer_id,
            'time'=>date('Y-m-d H:i:s',time())
        ]);
    }






}