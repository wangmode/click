<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/9 0009
 * Time: 14:56
 */
namespace app\agent\model;

use think\Db;
use think\Model;

class KeywordProductModel extends Model
{
    const IS_DEL_NO  = 0; //未删除
    const IS_DEL_YES = 1; //已删除

    const IS_TOP_NO  = 0; //未达标
    const IS_TOP_YES = 1; //已达标

    /**
     * 查询代理商下所有的关键词数量
     * @param $agentId
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTotal($agentId,$time)
    {
        $data = $this->where(['agent_id'=>$agentId,'is_del'=>self::IS_DEL_NO])
                    ->field(['to_days(creation_at) as days',"date_format(creation_at,'%Y-%m-%d') as time",'count(*) as total'])
                    ->group('days')
                    ->having("time > $time")
                    ->find();

        return $data;
    }

    /**
     * 查询消费记录客户详情
     * @param $agentId
     * @param $time
     * @return mixed
     */
    public function getCustomer($agentId,$time)
    {
        $sql = <<<sql
        select cu.company,kw.keyword,date_format(ccr.time,'%Y-%m-%d') as days,ccr.customer_id,SUM(ccr.money) as total_money
from yzt_keyword_product as ke 
LEFT JOIN daili_cmf.yzt_customer as cu ON ke.customer_id = cu.id
LEFT JOIN yzt_keyword as kw ON ke.keyword_id = kw.id
LEFT JOIN yzt_customer_consume_record as ccr ON ke.id = ccr.source_id
where ke.agent_id = $agentId AND ccr.time between "$time" and "$time 23:59:59"
GROUP BY ke.customer_id
sql;
//        return $sql;
        $data = db::query($sql);
        return $data;
    }




}