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
    const TYPE_AGENT = 1;
    const TYPE_ADMIN = 2;

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
            'admin_id'=> $agent_id,
            'customer_id'=>$customer_id,
            'type'=>self::TYPE_AGENT,
            'time'=>date('Y-m-d H:i:s',time())
        ]);
    }


    /**
     * 获取客户充值记录
     * @param $customer_id
     * @param $page
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getCustomerAccountList($customer_id,$page,$limit)
    {
        $start = ($page-1)*$limit;
        return self::where('customer_id',$customer_id)
            ->field(['time','money'])
            ->limit($start,$limit)
            ->order('time','desc')
            ->select();
    }

    /**
     * 客户充值记录条数
     * @param $customer_id
     * @return int|string
     */
    static public function getCustomerAccountCount($customer_id)
    {
        return self::where('customer_id',$customer_id)->count();
    }






}