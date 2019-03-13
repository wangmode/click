<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8 0008
 * Time: 11:36
 */
namespace app\agent\controller;
use cmf\controller\UserBaseController;
use app\agent\model\KeywordProductModel;
use app\agent\model\AgentConsumeRecordModel;
use app\agent\model\CustomerConsumeRecordModel;
use app\agent\model\AgentModel;

class ConsumptionController extends UserBaseController
{
    /**
     * 消费记录列表
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 客户详情列表
     */
    public function edit()
    {
        $time = $this->request->param('time');
        $this->assign('time',$time);
        return $this->fetch();
    }
    /**
     * 获取代理商消费记录
     * @return \think\response\Json
     * @throws \think\db\exception\BindParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function getAgentData()
    {
        $agentId    = cmf_get_current_user_id();
        $model      = new AgentConsumeRecordModel();
        $keywordModel = new KeywordProductModel();
        $agentData  = $model->getAgentData($agentId);
        foreach ($agentData as $key=>$val){
            $agentData[$key]['total']  = $keywordModel->getTotal($agentId,$val['time'])['total'];
            $agentData[$key]['number'] = $agentData[$key]['num'].'/'.$agentData[$key]['total'];
        }
        $data['code'] = 200;
        $data['count'] = count($agentData);
        $data['message'] = '';
        $data['data'] = $agentData;
        return json($data);
    }

    /**
     * 查询消费记录客户详细信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCustomerData()
    {
        $time = $this->request->param('time');
        $agentId    = cmf_get_current_user_id();
        $keywordModel = new KeywordProductModel();
        $CustomerModel = new CustomerConsumeRecordModel();
        $customerData = $keywordModel->getCustomer($agentId,$time);
        foreach ($customerData as $key => $val){
            $customerData[$key]['money_all'] = $CustomerModel->getCustomerMoneyAll($val['customer_id'])['money_all'];
            $customerData[$key]['total_money'] = $customerData[$key]['total_money']."/天";
        }

        $data['code'] = 200;
        $data['count'] = count($customerData);
        $data['message'] = '';
        $data['data'] = $customerData;
        return json($data);
    }


}