<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/9 0009
 * Time: 17:40
 */
namespace app\agent\model;

use app\common\model\ConsumeErrorLogModel;
use app\common\model\KeywordProductModel;
use think\Db;
use think\Exception;
use ConsumeException;
use think\Model;

class AgentConsumeRecordModel extends Model
{
    const IS_DEL_NO  = 0; //未删除
    const IS_DEL_YES = 1; //已删除


    static private $agent_cost = [
        AgentModel::LEVEL_ONE => 2.00,
        AgentModel::LEVEL_TWO => 5.00
    ];



    /**
     * 获取代理人消费信息
     * @param $agentId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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