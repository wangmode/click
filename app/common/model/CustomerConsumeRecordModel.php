<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/11
 * Time: 10:34
 */

namespace app\common\model;

use ConsumeException;
use think\Exception;
use think\Model;

class CustomerConsumeRecordModel extends Model
{

    /**
     * 创建获取天数子查询
     * @param $source_id
     * @return string
     * @throws \think\exception\DbException
     */
    static public function getSubsql($source_id){
        return self::where('source_id',$source_id)
                ->field(['count(*) as days','source_id'])
                ->group('to_days(time)')
                ->buildSql();
    }

    static public function getCustomerNum($source_id)
    {
        return self::where('source_id',$source_id)
                    ->field(['count(*) as days','source_id'])
                    ->group('to_days(time)')
                    ->count();
    }


    /**
     * 添加客户扣费记录
     * @param $customer_id      //客户ID
     * @param $money            // 扣费金额
     * @param $source_id        //  扣费产品套餐ID
     * @param $balance          // 客户当前余额
     * @return int|string
     */
    static public function addCustomerConsumeRecord($customer_id,$money,$source_id,$balance)
    {
        return self::insertGetId([
            'customer_id'   =>$customer_id,
            'money'         =>$money,
            'source_id'     =>$source_id,
            'balance'       =>$balance,
            'time'          =>date('Y-m-d H:i:s',time())
        ]);
    }


    /**
     * 客户扣费更新余额
     * @param $customer_id          //客户ID
     * @param $cost                 //费用
     * @param $keyword_product_id   //关键词产品套餐ID
     * @throws ConsumeException
     */
    static public function customerPaymentLog($customer_id,$cost,$keyword_product_id)
    {
        try{
            $customer_balance = CustomerModel::customerPayment($customer_id,$cost);
            CustomerConsumeRecordModel::addCustomerConsumeRecord($customer_id,$cost,$keyword_product_id,$customer_balance);
        }catch (Exception $exception){
            throw new ConsumeException($exception->getMessage(),ConsumeErrorLogModel::TYPE_CUSTOMER,$customer_id);
        }
    }



}