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

use cmf\controller\UserBaseController;
use \think\Db;
use app\common\model\ConfigModel as CommonConfigModel;
use app\common\model\ProductModel as CommonProductModel;
use app\common\model\CustomerModel as CommonCustomerModel;
use app\agent\model\CustomerModel;
use app\admin\model\DeployModel;
use think\Exception;

class CustomerController extends UserBaseController
{

    /**
     * 客户列表
     */
    public function index()
    {
        $type = $this->request->param('type')?:2;
        $this->assign('type',$type);
        return $this->fetch();
    }

    //获取客户信息
    public function data()
    {
        $page = $this->request->param('page',1,'number');
        $limit= $this->request->param('limit',10,'number');
        $keywords = $this->request->param('keywords',1,'string');
        $agent_id = cmf_get_current_user()['id'];
        try{
            if(empty($agent_id)){
                throw new Exception("非法访问!");
            }
            $customer_list = CustomerModel::getKPBCustomerListByAgentId($agent_id,$keywords,$page,$limit);
            $count = CustomerModel::getKPBCustomerCountByAgentId($agent_id,$keywords);
            return json(['code'=>200,'count'=>$count,'data'=>$customer_list,'message'=>"获取客户信息成功！"]);
        }catch (Exception $exception){
            return json(['code'=>0,'count'=>0,'data'=>null,'message'=>$exception->getMessage()]);
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
        $data['agent_id'] = cmf_get_current_user()['id'];
        $validate = $this->validate($data,'CustomerKPB.add');
        try{
            if($validate !== true){
                throw new Exception($validate);
            }
            CustomerModel::addKPBCustomer($data);
            return json(['status'=>1,'data'=>null,'message'=>"新建客户成功！"]);
        }catch (Exception $exception){
            return json(['status'=>0,'data'=>null,'message'=>$exception->getMessage()]);
        }
    }

    /**
     * 编辑客户提交保存
     */
    public function editPost()
    {
        $data      = $this->request->param();
        $data['agent_id'] = cmf_get_current_user()['id'];
        $validate = $this->validate($data,'CustomerKPB.edit');
        try{
            if($validate !== true){
                throw new Exception($validate);
            }
            CustomerModel::updateKPBCustomer($data);
            return json(['status'=>1,'data'=>null,'message'=>"更新客户成功！"]);
        }catch (Exception $exception){
            return json(['status'=>0,'data'=>null,'message'=>$exception->getMessage()]);
        }
    }

    public function to_disable(){
        $customer_id = $this->request->param('id');

        $customer_info = Db::name('customer')->where('id',$customer_id)->find();

        $deploy= new DeployModel();

        $customer_info['status']==1 ? $status = 2 : $status = 1;

        //修改apache配置
        $config = $deploy->modify_config($customer_info['format_url'],$customer_info['name'],$customer_info['url_str'],$status);
        if(!$config){
            return json(['status'=>0,'msg'=>'操作失败！']);
        }
        $re = Db::name('customer')->where('id',$customer_id)->setField('status', $status);
        if($re){
            return json(['status'=>1,'customer_status'=>$status,'msg'=>'操作成功！']);
        }else{
            return json(['status'=>0,'customer_status'=>$status,'msg'=>'操作失败！']);
        }
    }

    function to_deploy(){
        $customer_id = $this->request->param('customer_id');
        $deploy= new DeployModel();
        $customer_info = Db::name('customer')->where('id',$customer_id)->find();
        $url = $customer_info['url'];

        if($customer_info['table_password']){
            $url_data['table_password'] = $customer_info['table_password'];
            $url_data['name'] = $customer_info['name'];
            $url_data['url_str'] = $customer_info['url_str'];
            $url_data['url'] = $customer_info['format_url'];
        }else{
            $url_data = $deploy->url_format($url,$customer_id,$customer_info['class']);
        }

        //开始部署
        $re = $deploy->to_deploy($url_data,$customer_id,1);
        if($re['status']){
            //初始化优站通信息
            $deploy->config_initialize($customer_info,$url_data['name'],$url_data['table_password']);
        }
        return $re;
    }
}
