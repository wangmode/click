<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/13
 * Time: 15:56
 */

namespace app\common\Model;

use app\agent\model\AgentModel;
use app\common\Model\AgentModel as CommonAgentModel;

use think\Exception;
use think\Model;
use ConsumeException;

class AgentConsumeRecordModel extends Model
{

    const IS_DEL_NO  = 0; //未删除
    const IS_DEL_YES = 1; //已删除


    static private $agent_cost = [
        AgentModel::LEVEL_ONE => 2.00,
        AgentModel::LEVEL_TWO => 5.00
    ];


    /**
     * 通过等级获取费用
     * @param $level
     * @return float|mixed
     */
    static public function getAgentCost($level)
    {
        $cost = is_null(self::$agent_cost[$level])?5.00:self::$agent_cost[$level];
        return $cost;
    }


    /**
     * 添加代理扣费记录
     * @param $agent_id         //代理ID
     * @param $money            //金额
     * @param $source_id        //来源产品ID
     * @param $balance          //当前余额
     * @return int|string
     */
    static public function addAgentConsumeRecord($agent_id,$money,$source_id,$balance)
    {
        return self::insertGetId([
            'agent_id'  =>$agent_id,
            'money'     =>$money,
            'source_id' =>$source_id,
            'balance'   =>$balance,
            'time'      =>date('Y-m-d H:i:s',time())
        ]);
    }


    /**
     * 代理商扣费并添加扣费记录
     * @param $agent_id             //代理ID
     * @param $keyword_product_id   //关键词产品套餐ID
     * @throws ConsumeException
     */
    static public function agentPaymentLog($agent_id,$keyword_product_id)
    {
        try{
            $agent = CommonAgentModel::getAgentMoneyById($agent_id);
            if(empty($agent)){
                throw new ConsumeException('获取不到当前代理信息！',ConsumeErrorLogModel::TYPE_AGENT,$agent_id);
            }
            $cost =  AgentConsumeRecordModel::getAgentCost($agent['level']);
            if(bccomp($cost,$agent['money'],2) == 1){
                KeywordProductModel::disableKeywordProductByAgentId($agent_id);
                throw new ConsumeException("您的余额不足，请充值！",ConsumeErrorLogModel::TYPE_AGENT,$agent_id);
            }
            $agent_balance = CommonAgentModel::agentPayment($agent_id,$cost);
            AgentConsumeRecordModel::addAgentConsumeRecord($agent_id,$cost,$keyword_product_id,$agent_balance);
        }catch (Exception $exception){
            throw new ConsumeException($exception->getMessage(),ConsumeErrorLogModel::TYPE_AGENT,$agent_id);
        }
    }


}