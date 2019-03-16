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
use app\common\Model\CustomerModel as CommonCustomerModel;

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
    public function getCustomer($agentId,$time,$page,$limit)
    {
        $start = ($page-1)*$limit;
        $data = self::alias('kp')
            ->join('keyword k','kp.keyword_id = k.id','left')
            ->join('customer_consume_record ccr','kp.id = ccr.source_id','left')
            ->where('kp.agent_id',$agentId)
            ->where('ccr.time','between',[$time,"$time 23:59:59"])
            ->limit($start,$limit)
            ->field(['kp.id','k.keyword',"date_format(ccr.time,'%Y-%m-%d') as days",'kp.customer_id','SUM(ccr.money) as total_money'])
            ->group('kp.customer_id')
            ->select();
        foreach ($data as $key=>$val){
            $data[$key]['company'] = CommonCustomerModel::getCustomerCompanyById($val['customer_id']);
        }
        return $data;
    }

    /**
     * 获取客户消费记录条数
     * @param $agentId
     * @param $time
     * @return int|string
     * @throws \think\Exception
     */
    public function getCustomerCount($agentId,$time)
    {
        $count = self::alias('kp')
            ->join('keyword k','kp.keyword_id = k.id','left')
            ->join('customer_consume_record ccr','kp.id = ccr.source_id','left')
            ->where('kp.agent_id',$agentId)
            ->where('ccr.time','between',[$time,"$time 23:59:59"])
            ->field("date_format(ccr.time,'%Y-%m-%d') as days")
            ->group('kp.customer_id')
            ->count();
        return $count;
    }

    public function getCustomerDataHandle($data)
    {
        $CustomerModel = new CustomerConsumeRecordModel();
        foreach ($data as $key => $val){
            $data[$key]['money_all'] = $CustomerModel->getCustomerMoneyAll($val['customer_id'])['money_all'];
            $data[$key]['total_money'] = $data[$key]['total_money']."/天";
        }
        return $data;
    }


}