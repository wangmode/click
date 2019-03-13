<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/9 0009
 * Time: 17:40
 */
namespace app\agent\model;

use think\Db;
use think\Model;

class AgentConsumeRecordModel extends Model
{
    const IS_DEL_NO  = 0; //未删除
    const IS_DEL_YES = 1; //已删除


    /**
     * 获取代理人消费信息
     * @param $agentId
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function getAgentData($agentId)
    {
        $data = $this->alias('re')
                ->join('keyword_product ke','re.source_id = ke.id')
                ->where(['ke.agent_id'=>$agentId,'ke.is_del'=>self::IS_DEL_NO])
                ->field([
                    'to_days(re.time) as days',"date_format(re.time,'%Y-%m-%d') as time",
                    "count(ke.id) as num","SUM(re.moeny) as c_moeny","re.balance"
                ])
                ->group("days")
                ->order('re.time','desc')
                ->select();
        return $data;
    }
}