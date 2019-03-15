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
namespace app\agent\controller;

use app\common\model\CustomerAccountLogModel;
use app\common\model\CustomerConsumeRecordModel;
use app\common\model\KeywordProductModel;
use app\common\model\GettingKeywordModel;
use app\common\model\ProductModel;
use cmf\controller\UserBaseController;
use \think\Db;
use app\common\model\ConfigModel as CommonConfigModel;
use app\common\model\ProductModel as CommonProductModel;
use app\common\model\CustomerModel as CommonCustomerModel;
use app\agent\model\CustomerModel;
use think\Exception;

class CustomerController extends UserBaseController
{

    const KEY_PREFIX = "keyword_result_";
    /**
     * 客户列表
     */
    public function index()
    {
        $type = $this->request->param('type')?:2;
        $this->assign('type',$type);
        return $this->fetch();
    }

    /**
     * 添加关键词列表
     */
    public function keyword_add(){
        $customer_id = $this->request->param('customer_id');
        $product_list = ProductModel::getProductList();
        $this->assign('product',$product_list);
        $this->assign('customer_id',$customer_id);
        return $this->fetch();
    }

    //获取客户信息
    public function data()
    {
        $page = $this->request->param('page',1,'number');
        $limit= $this->request->param('limit',10,'number');
        $keywords = $this->request->param('keywords',1,'string');
        $agent_id = cmf_get_current_user_id();
        try{
            if(empty($agent_id)){
                throw new Exception("非法访问!");
            }
            $customer_list = CustomerModel::getKPBCustomerListByAgentId($agent_id,$keywords,$page,$limit);
            $count = CustomerModel::getKPBCustomerCountByAgentId($agent_id,$keywords);
            return $this->returnListJson(self::CODE_OK,$count,$customer_list,"获取客户信息成功！");
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,0,null,$exception->getMessage());
        }
    }




    /**
     * 添加新客户
     */
    public function add()
    {
        $area_info = yzt_get_city_info();
        //分类信息
        $class_info = cmf_get_option('class_info');
        $this->assign('province',json_encode($area_info));
        $this->assign('class_info',$class_info);
        return $this->fetch();
    }


    /**
     * 编辑
     */
    public function edit()
    {
        $id        = $this->request->param('customer_id', 0, 'intval');
        $area_info = yzt_get_city_info();
        $this->assign('province',json_encode($area_info));
        $CustomerModel = CustomerModel::get($id);
        $class_info = cmf_get_option('class_info');
        $this->assign('class_info',$class_info);
        //地区
        $area_check = $CustomerModel['province'].','.$CustomerModel['city'];
        $CustomerModel['area_check'] = $area_check;
        $this->assign('info', $CustomerModel);
        return $this->fetch();
    }

    /**
     * 添加客户提交保存
     */
    public function addPost()
    {
        $data      = $this->request->param();
        $data['agent_id'] = cmf_get_current_user_id();
        $validate = $this->validate($data,'CustomerKPB.add');
        try{
            if($validate !== true){
                throw new Exception($validate);
            }
            CustomerModel::addKPBCustomer($data);
            return $this->returnJson(self::STATUS_OK,null,"新建客户成功！");
        }catch (Exception $exception){
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    /**
     * 编辑客户提交保存
     */
    public function editPost()
    {
        $data      = $this->request->param();
        $data['agent_id'] = cmf_get_current_user_id();
        $validate = $this->validate($data,'CustomerKPB.edit');
        try{
            if($validate !== true){
                throw new Exception($validate);
            }
            CustomerModel::updateKPBCustomer($data);
            return $this->returnJson(self::STATUS_OK,null,"更新客户成功！");
        }catch (Exception $exception){
            return json(['status'=>0,'data'=>null,'message'=>$exception->getMessage()]);
        }
    }

    /**
     * 变更客户状态
     * @return \think\response\Json
     */
    public function to_disable(){
        $customer_id = $this->request->param('id');
        try{
            $status = CustomerModel::editCustomerStatusById($customer_id);
            return $this->returnStatusJson(self::STATUS_OK,$status,'操作成功！');
        }catch (Exception $exception){
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    /**
     * 客户关键词列表
     */
    public function keywordList()
    {
        $customer_id = $this->request->param('id');
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
        $customer_id    = $this->request->param('id',0,'intval');
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
            var_dump($keyword_list);die;
            return $this->returnListJson(self::CODE_OK,$count,$keyword_list,"获取关键词信息成功！");
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,0,null,$exception->getMessage());
        }
    }


    /**
     * 客户充值
     * @return \think\response\Json
     */
    public function customerRenew()
    {
        $data = $this->request->param();
        $validate = $this->validate($data,'Renew');

        $agent_id = cmf_get_current_user_id();
        if($validate !== true){
            return $this->returnJson(self::STATUS_FAIL,null,$validate);
        }
        try{
            Db::startTrans();
            CommonCustomerModel::customerRecharge($data['id'],$data['money']);
            CustomerAccountLogModel::addCustomerAccountLog($agent_id,$data['id'],$data['money']);
            Db::commit();
            return $this->returnJson(self::STATUS_OK,null,'充值成功！');
        }catch (Exception $exception){
            Db::rollback();
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
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
     * 挖掘关键词
     * @return \think\response\Json
     */
    public function excavateKeywords()
    {
        $str        = $this->request->param('keyword','','string');
        $str_EN     = preg_replace("/(，)/" ,',' ,$str);
        $keywords   = preg_replace('/ /', '', $str_EN);
        $keyword    = explode(",",$keywords);
        $limit      = $this->request->param('limit',10,'intval');
        $page       = $this->request->param('page',1,'intval');
        $agent_id   = cmf_get_current_user_id();
        try{
            if (!empty($keyword)) {
                $keyword_list = (new GettingKeywordModel())->getKeywordList($agent_id, $keyword, $page, $limit);
                return $this->returnListJson(self::CODE_OK, $keyword_list['count'], $keyword_list['list'], "获取关键词信息成功！");
            }
            return $this->returnListJson(self::CODE_OK, 0, [], "获取关键词信息成功！");
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,0,null,$exception->getMessage());
        }
    }


    /**
     * 添加新的关键词
     * @return \think\response\Json
     */
    public function addKeywords()
    {
        $data        = $this->request->param();
        $agent_id    = cmf_get_current_user_id();
        $url = $data['url'];
        $customer_id = $data['customer_id'];
        unset($data['url']);
        unset($data['customer_id']);
        if(array_key_exists('layTableCheckbox',$data)){
            unset($data['layTableCheckbox']);
        }
        $res = [];
        foreach ($data as $key=>$value){
            $keys = substr($key,15);
            $keys_arr = explode("-",$keys);
            if(strcmp($keys_arr[1],'setmeal') !== 0){
                $res[$keys_arr[0]]['price'][] = $keys_arr[1];
                $res[$keys_arr[0]]['setmeal'] = $data['keyword_result_'.$keys_arr[0].'-setmeal'];
                $res[$keys_arr[0]]['url'] = $url;
                $res[$keys_arr[0]]['customer_id'] = $customer_id;
            }
        }
        try{
            Db::startTrans();
            foreach ($res as $key => $val){
                KeywordProductModel::newKeywordProduct($agent_id,self::KEY_PREFIX.$key,$val);
            }
            Db::commit();
            return $this->returnJson(self::STATUS_OK,null,'添加关键词成功！');
        }catch (Exception $exception){
            Db::rollback();
            return $this->returnJson(self::STATUS_FAIL,null,'添加关键词失败！');
        }
    }

    /**
     * 客户充值记录列表
     * @return mixed
     */
    public function cusetomerCustomerRenewList()
    {
        $cusetomer_id = $this->request->param('id');
        try{
            if(empty($cusetomer_id)){
                throw new Exception('非法访问');
            }
            $this->assign('id',$cusetomer_id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 获取客户充值信息
     * @return \think\response\Json
     */
    public function cusetomerCustomerRenewData()
    {
        $cusetomer_id = $this->request->param('id');
        $limit      = $this->request->param('limit',10,'intval');
        $page       = $this->request->param('page',1,'intval');
        try{
            if(empty($cusetomer_id)){
                throw new Exception("非法访问！");
            }
            $count = CustomerAccountLogModel::getCustomerAccountCount($cusetomer_id);
            $list = CustomerAccountLogModel::getCustomerAccountList($cusetomer_id,$page,$limit);
            return $this->returnListJson(self::CODE_OK,$count,$list,"获取充值信息成功！");
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,0,null,$exception->getMessage());
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
