<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/9 0009
 * Time: 17:40
 */
namespace app\agent\model;

use app\common\model\ConsumeErrorLogModel;
use app\agent\model\KeywordProductModel;
use think\Db;
use think\Exception;
use app\common\Exception\ConsumeException;
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
    public function getAgentData($agentId,$page,$limit)
    {
        $start = ($page-1)*$limit;
        $data = $this->alias('re')
                ->join('keyword_product ke','re.source_id = ke.id')
                ->where(['ke.agent_id'=>$agentId,'ke.is_del'=>self::IS_DEL_NO])
                ->field([
                    'to_days(re.time) as days',"date_format(re.time,'%Y-%m-%d') as time",
                    "count(ke.id) as num","SUM(re.money) as c_money","re.balance"
                ])
                ->group("days")
                ->limit($start,$limit)
                ->order('re.time','desc')
                ->select();
        return $data;
    }

    /**
     * 获取代理人消费信息总数
     * @param $agentId
     * @return int|string
     * @throws Exception
     */
    public function getAgentCount($agentId)
    {
        $count = $this->alias('re')
            ->join('keyword_product ke','re.source_id = ke.id')
            ->where(['ke.agent_id'=>$agentId,'ke.is_del'=>self::IS_DEL_NO])
            ->field('to_days(re.time) as days')
            ->group("days")
            ->count();
        return $count;
    }

    /**
     * 代理人数据处理
     * @param $agentData
     * @param $agentId
     */
    public function getAgentDataHandle($data,$agentId){
        $keywordModel = new KeywordProductModel();
        foreach ($data as $key=>$val){
            $data[$key]['total']  = $keywordModel->getTotal($agentId,$val['time'])['total'];
            $data[$key]['number'] = $data[$key]['num'].'/'.$data[$key]['total'];
        }
        return $data;
    }
}