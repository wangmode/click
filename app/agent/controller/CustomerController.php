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
        $info = $this->request->param();
        $start = ($info['page']-1)*$info['limit'];
        $type = $info['type']?:2;
        $where['type'] = $type;
        $user = cmf_get_current_user();
        $where['agent_id'] = $user['id'];

        if(isset($info['keywords']) && $info['keywords']){
            $keywords = trim($info['keywords']);
            $where['company'] = ['like','%'.$keywords.'%'];
        }

        if(isset($info['url']) && $info['url']){
            $url = trim($info['url']);
            $where['url'] = ['like','%'.$url.'%'];
        }

        $count = db('customer')->where($where)->count();
        $customer_list = Db::name('customer')
            ->where($where)
            ->limit($start,$info['limit'])
            ->order('id', 'desc')
            ->select()->toArray();

            foreach ($customer_list as $k=>$v){
                $amount = 0;
                $remaining = 0;
                $baobiao_url = '';
                if($v['wtx_id']){
                    $baobiao_url = 'http://sh.yzt-tools.com/admin/baobiaoone/index/sousuo/baidu/adminid/'.$v['wtx_id'].'.html';
                    $info = CommonConfigModel::getConfigInfoByAdminId($v['wtx_id']);
                    $amount = $info['amount'];
                    $remaining = $info['remaining'];
                }
                $customer_list[$k]['wtx_id_64'] = base64_encode($v['wtx_id']);
                $customer_list[$k]['wtx_amount'] = $amount;
                $customer_list[$k]['wtx_remaining'] = $remaining;
                $customer_list[$k]['baobiao_url'] = $baobiao_url;
            }


        $data['code'] = 200;
        $data['count'] = $count;
        $data['message'] = '';
        $data['data'] = $customer_list;
        return json($data);
    }

    /**
     * 网推侠客户续费
     * @return \think\response\Json
     */
    public function customerRenew(){
        $id = $this->request->param('id');
        $product_id = $this->request->param('product_id');
        $user = cmf_get_current_user();
        Db::startTrans();
        try {
            if(empty($id) || empty($product_id) || $user === false){
                throw new Exception("非法访问！");
            }
            CommonCustomerModel::checkCustomerByAgentId($id,$user['id']);
            CommonCustomerModel::cstomerWTXRenew($product_id, $id);
            Db::commit();
            return json(['status'=>1,'data'=>null,'message'=>"续费成功！"]);
        } catch (Exception $e) {
            Db::rollback();
            return json(['status'=>0,'data'=>null,'message'=>$e->getMessage()]);
        }
    }

    /**
     * 通过客户等级获取产品信息
     * @return \think\response\Json
     */
    public function getProductList(){
        $id = $this->request->param('id');
        try{
            if(empty($id)){
                throw new Exception("非法访问！");
            }
            $level =CommonCustomerModel::getAgentLevelById($id);
            $product_list = CommonProductModel::getProductByLevel(CommonProductModel::TYPE_WTX,$level);
            if(empty($product_list)){
                throw new Exception("找不到续费产品信息！");
            }
            return json(['status'=>1,'data'=>$product_list,'message'=>"获取产品信息成功！"]);
        }catch (Exception $exception){
            return json(['status'=>0,'data'=>null,'message'=>$exception->getMessage()]);
        }
    }

    /**
     * 添加新客户
     */
    public function add()
    {
        $type= $this->request->param('type')?:1;
        $area_info = yzt_get_city_info();
        $agent_info = cmf_get_current_user();
        //分类信息
        $class_info = cmf_get_option('class_info');
        $this->assign('province',json_encode($area_info));
        $this->assign('class_info',$class_info);
        if($type==2){
            $product_list = get_product_list($agent_info['level'],'wtx');
            //$product_list = Db::name('product')->where('type','wtx')->select();
            $this->assign('product_list',$product_list);
            //托管
            $product_trust = Db::name('product')->where('type','trust')->select();
            $this->assign('product_trust',$product_trust);
            return $this->fetch('wtx');
        }else{
            $product_list = get_product_list($agent_info['level'],'yzt');
            $this->assign('product_list',$product_list);
            return $this->fetch('yzt');
        }
    }

    /**
     * 新客户保存
     */
    public function customer_add_post(){
        if($this->request->isPost()){
            $customer_info = $this->request->post();
            if(isset($customer_info['url'])){
                if(url_is_exists($customer_info['url'])){
                    return json(['status'=>0,'msg'=>'该域名已存在！']);
                }
            }
            $agent_id = cmf_get_current_user_id();
            $customer = new CustomerModel();
            $result = $customer->add_customer($customer_info,$agent_id);
            return json($result);
        }else{
            return json(['status'=>0,'msg'=>'没有收到数据！']);
        }
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
        //代理商
        $agent = Db::name('agent')->where('id',$CustomerModel['agent_id'])->value('account');
        //地区
        $area_check = $CustomerModel['province'].','.$CustomerModel['city'];
        $CustomerModel['agent'] = $agent;
        $CustomerModel['area_check'] = $area_check;
        $this->assign('info', $CustomerModel);
        return $this->fetch();
    }

    /**
     * 编辑客户提交保存
     */
    public function editPost()
    {
        $data      = $this->request->param();
        $CustomerModel = new CustomerModel();
        $result    = $CustomerModel->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            return json(['status'=>0,'msg'=>$CustomerModel->getError()]);
        }
        return json(['status'=>1,'msg'=>'保存成功']);
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
