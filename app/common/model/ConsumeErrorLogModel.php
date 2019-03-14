<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/13
 * Time: 10:34
 */

namespace app\common\model;

use think\Model;

class ConsumeErrorLogModel extends Model
{
    const TYPE_AGENT    = 1;        //代理商
    const TYPE_CUSTOMER = 2;        //客户
    const TYPE_PRODUCT  = 3;        //产品

    /**
     * 添加扣费错误日志
     * @param $type
     * @param $user_id
     * @param $remarks
     */
    static public function addConsumeErrorLog($type,$user_id,$remarks)
    {
        self::insert([
            'type'=>$type,
            'user_id'=>$user_id,
            'remarks'=>$remarks,
            'time'=>date("Y-m-d H:i:s",time())
        ]);
    }

}