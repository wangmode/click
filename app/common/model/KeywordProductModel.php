<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8
 * Time: 11:28
 */

namespace app\common\model;


use think\Model;

class KeywordProductModel extends Model
{
    const IS_DEL_NO         = 0 ; // 未删除
    const IS_DEL_YES        = 1 ; // 已删除

    const IS_TOP_NO         = 0 ; // 未达标
    const IS_TOP_YES        = 1 ; // 已达标

    const STATUS_PAUSE      = 0 ; // 暂停
    const STATUS_NORMAL     = 1 ; // 正常
    const STATUS_DISABLE    = 2 ; // 禁用


    /**
     * 获取客户关键词总数
     * @param $customer_id      //客户ID
     * @return int|string
     */
    static public function getKeywordNumByCustomerId($customer_id)
    {
        return self::where('customer_id',$customer_id)
                    ->where('is_del',self::IS_DEL_NO)
                    ->count();
    }

    /**
     * 获取客户关键词生效数量与金额
     * @param $customer_id      //金额
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTopKeywordNumAndMoneyByCustomerId($customer_id)
    {
        return self::where('customer_id',$customer_id)
                        ->where('is_del',self::IS_DEL_NO)
                        ->where('is_top',self::IS_TOP_YES)
                        ->where('status',self::STATUS_NORMAL)
                        ->field(['count(*) as top','ifnull(sum(money),0.00) as cost'])
                        ->find();
    }



}