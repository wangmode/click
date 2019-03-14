<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/11 0011
 * Time: 16:39
 */
namespace app\agent\model;

use think\Db;
use think\Model;

class CustomerConsumeRecordModel extends Model
{
    /**
     * 查询客户总消费额
     * @param $customer_id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCustomerMoneyAll($customer_id)
    {
        $data = $this->where(['customer_id'=>$customer_id])
                    ->field('SUM(money) as money_all')
                    ->find();
        return $data;
    }
}