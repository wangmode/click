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
use app\agent\model\AgentModel;
use think\Exception;

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
     *  获取代理商消费记录
     * @return \think\response\Json
     */
    public function getAgentData()
    {
        $limit      = $this->request->param('limit',10,'intval');
        $page       = $this->request->param('page',1,'intval');
        $agentId    = cmf_get_current_user_id();
        $model      = new AgentConsumeRecordModel();
        try {
            if(empty($agentId)){
                throw new Exception("非法访问！");
            }
            $count         =$model->getAgentCount($agentId);
            $agentData     = $model->getAgentData($agentId,$page,$limit);
            $agentDataList = $model->getAgentDataHandle($agentData,$agentId);
            return $this->returnListJson(self::CODE_OK,$count,$agentDataList,'获取客户信息成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,0,null,$exception->getMessage());
        }
    }

    /**
     * 查询消费记录客户详细信息
     * @return \think\response\Json
     */
    public function getCustomerData()
    {
        $limit        = $this->request->param('limit',10,'intval');
        $page         = $this->request->param('page',1,'intval');
        $time         = $this->request->param('time');
        $agentId      = cmf_get_current_user_id();
        $keywordModel = new KeywordProductModel();
        try{
            if(empty($agentId)){
                throw new Exception("非法访问！");
            }
            $count            = $keywordModel->getCustomerCount($agentId,$time);
            $customerData     = $keywordModel->getCustomer($agentId,$time,$page,$limit);
            $customerDataList = $keywordModel->getCustomerDataHandle($customerData);
            return $this->returnListJson(self::CODE_OK,$count,$customerDataList,'获取客户信息成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,0,null,$exception->getMessage());
        }
    }


}