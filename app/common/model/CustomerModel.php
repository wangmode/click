<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/13
 * Time: 16:23
 */

namespace app\common\Model;

use ConsumeException;
use think\Exception;
use think\Model;

class CustomerModel extends Model
{
    protected $connection = "db_daili";

    const STATUS_UNAUDITED = 0;     //未审核
    const STATUS_NORMAL = 1;        //正常
    const STATUS_PROHIBIT = 2;      //禁用

    const TYPE_YZT = 1;             //优站通（熊掌客）
    const TYPE_WTX = 2;             //网推侠
    const TYPE_ALL = 3;             //网推侠 + 优站通（熊掌客）
    const TYPE_KPB = 5;             //快排宝

    const TABLE = 'Customer';       //当前表名称



    /**
     * 客户扣费更新余额
     * @param $customer_id      //客户ID
     * @param $cost             //扣费
     * @return mixed
     * @throws ConsumeException
     * @throws \think\Exception
     */
    static public function customerPayment($customer_id,$cost)
    {
        $update_num = self::where('id',$customer_id)->setDec('money',$cost);
        if($update_num != 1){
            throw new ConsumeException('当前客户余额更新失败！',ConsumeErrorLogModel::TYPE_CUSTOMER,$customer_id);
        }
        return self::where('id',$customer_id)->value('money');
    }


    /**
     * 客户充值更新余额
     * @param $customer_id      //客户ID
     * @param $money            //金额
     * @return mixed
     * @throws \think\Exception
     */
    static public function customerRecharge($customer_id,$money)
    {
        $update_num = self::where('id',$customer_id)->setInc('money',$money);
        if($update_num != 1){
            throw new Exception('当前客户余额更新失败！');
        }
        return self::where('id',$customer_id)->value('money');
    }







}