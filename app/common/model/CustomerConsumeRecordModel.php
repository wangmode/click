<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/11
 * Time: 10:34
 */

namespace app\common\model;


use think\Model;

class CustomerConsumeRecordModel extends Model
{

    /**
     * 创建获取天数子查询
     * @param $source_id
     * @return string
     * @throws \think\exception\DbException
     */
    static public function getSubsql($source_id=null){
        $where = [];
        if(!empty($source_id)){
            $where['source_id'] = $source_id;
        }
        return self::where($where)
                ->field(['count(*) as days','source_id'])
                ->group('to_days(time)')
                ->buildSql();
    }

}