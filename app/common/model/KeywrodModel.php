<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/12
 * Time: 10:48
 */

namespace app\common\Model;


use think\Model;

class KeywrodModel extends Model
{
    const STATUS_PAUSE      = 0 ; // 暂停
    const STATUS_NORMAL     = 1 ; // 正常
    const STATUS_DISABLE    = 2 ; // 禁用

    /**
     * 新增关键词，返回自增ID
     * @param $keywrod
     * @param $customer_id
     * @param $url
     * @return int|string
     */
    static public function addKeyword($keywrod,$customer_id,$url)
    {
        return self::insertGetId([
                    'keyword'       =>$keywrod,
                    'customer_id'   =>$customer_id,
                    'url'           =>$url,
                    'status'        =>self::STATUS_NORMAL
                ]);
    }

}