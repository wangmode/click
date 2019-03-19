<?php
// +----------------------------------------------------------------------
// | 优站通 [ 一站到底，让排名不是梦 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.youzhantong.vip All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: WangMode <wangmode@163.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use app\common\model\CustomerAccountLogModel;
use app\common\model\CustomerConsumeRecordModel;
use app\common\model\KeywordProductModel;
use cmf\controller\CustomerBaseController;
use app\agent\model\CustomerModel;
use think\Exception;

class CustomerController extends CustomerBaseController
{

    /**
     * 客户关键词列表
     */
    public function keywordList()
    {
        $customer_id = cmf_get_current_customer_id();
        try{
            if(empty($customer_id)){
                throw new Exception("非法访问！");
            }
            $info = CustomerModel::getCustomerInfoById($customer_id);
            $this->assign('id',$customer_id);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 客户关键词数据
     */
    public function keywordData()
    {
        $customer_id    = cmf_get_current_customer_id();
        $is_top         = $this->request->param('is_top',null,'intval');
        $keyword        = $this->request->param('keyword',null,'string');
        $status         = $this->request->param('status',null,'intval');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        try{
            if(empty($customer_id)){
                throw new Exception("非法访问！");
            }
            $count = KeywordProductModel::getCustomerKeywordCount($customer_id,$keyword,$is_top,$status);
            $keyword_list = KeywordProductModel::getCustomerKeywordListData($customer_id,$keyword,$is_top,$status,$page,$limit);
            return $this->returnListJson(self::CODE_OK,$count,$keyword_list,"获取关键词信息成功！");
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,0,null,$exception->getMessage());
        }
    }


    /**
     * 修改关键词状态
     * @return \think\response\Json
     */
    public function keywordToDisable()
    {
        $id    = $this->request->param('id',0,'intval');

        try{
            $status = KeywordProductModel::updateKeywordStatus($id);
            return $this->returnStatusJson(self::STATUS_OK,$status,"修改关键词状态成功！");
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }


    /**
     * 删除关键词
     * @return \think\response\Json
     */
    public function deleteKeyword()
    {
        $id    = $this->request->param('id',0,'intval');
        try{
            $status = KeywordProductModel::delKeywordProduct($id);
            return $this->returnStatusJson(self::STATUS_OK,$status,"删除关键词成功！");
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }

    }

    /**
     * 关键词产品套餐扣费记录列表
     * @return mixed
     */
    public function consumeRecordList()
    {
        $keywordProduct_id = $this->request->param('id');

        try{
            if(empty($keywordProduct_id)){
                throw new Exception("非法访问！");
            }
            $money = CustomerConsumeRecordModel::getTotalSumBySourceId($keywordProduct_id);
            $this->assign('id',$keywordProduct_id);
            $this->assign('money',$money);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 获取关键词扣费信息列表数据
     * @return \think\response\Json
     */
    public function keywordProductConsumeRecordData()
    {
        $keywordProduct_id = $this->request->param('id');
        $limit      = $this->request->param('limit',10,'intval');
        $page       = $this->request->param('page',1,'intval');
        try{
            if(empty($keywordProduct_id)){
                throw new Exception("非法访问！");
            }
            $count = CustomerConsumeRecordModel::getRecordListCountBySourceId($keywordProduct_id);
            $list = CustomerConsumeRecordModel::getRecordListBySourceId($keywordProduct_id,$page,$limit);
            return $this->returnListJson(self::CODE_OK,$count,$list,"获取扣费信息成功！");
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,0,null,$exception->getMessage());
        }

    }


}
