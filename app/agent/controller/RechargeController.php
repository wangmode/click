<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\agent\controller;

use cmf\controller\UserBaseController;
use think\db;
class RechargeController extends UserBaseController
{
    public $payment; //  具体的支付类
    private $money_list = array('0.01','5000','10000','20000','50000','100000');
    public function  _initialize() {
        parent::_initialize();
        vendor('alipay.alipay');
        $this->payment = new \alipay();
    }
    /**
     * 在线充值首页
     */
    public function index()
    {
        $this->assign('user_info',cmf_get_current_user());
        return $this->fetch();
    }


    //充值记录、消费记录
    public function record()
    {
        $type = $this->request->param('type')?:'recharge';
        $this->assign('type',$type);
        if($type=='recharge'){
            return $this->fetch('recharge');
        }else{
            return $this->fetch('consume');
        }
    }

    //获取记录信息
    public function data()
    {
        $info = $this->request->param();
        $start = ($info['page']-1)*$info['limit'];
        $user = cmf_get_current_user();
        $where['agent_id'] = $user['id'];
        $type = $this->request->param('type')?:'recharge';
        //充值记录
        if($type=='recharge'){
            $count = db('recharge')->where("agent_id",$user['id'])->count();
            $data_list = db('recharge')->where("agent_id",$user['id'])->where('pay_status',1)->limit($start,$info['limit'])->order("id","desc")->select();
        }else{//消费记录
            $count = db('consume')->where("agent_id",$user['id'])->count();
            $data_list = db('consume')->where("agent_id",$user['id'])->limit($start,$info['limit'])->order("id","desc")->select();
        }
        $data['code'] = 200;
        $data['count'] = $count;
        $data['message'] = '';
        $data['data'] = $data_list;
        return json($data);
    }

    function to_recharge(){
        $data['agent_id'] = cmf_get_current_user_id();
        //充值金额
        $amount = $this->request->param('amount');
        if(!in_array($amount,$this->money_list)){
            $this->error('金额非法！');
            //return json(['status'=>0,'msg'=>'金额非法']);
        }
        $data['amount'] = $amount;

        //支付方式
        $data['pay_code'] = 'alipay';
        $data['create_time'] = time();
        $data['order_sn'] = 'recharge'.str_rand(10);
        $order_id = Db('recharge')->insert($data);

        $data['subject'] = '代理商充值';
        $data['body'] = '代理商充值';

        if($order_id){
            $re= $this->payment->get_code($data);
            var_dump($re);exit;
            //return json(['status'=>1,'msg'=>'支付跳转','data'=>$re]);
        }else{
            $this->error('参数错误！');
            //return json(['status'=>0,'msg'=>'参数错误']);
        }
    }

    public function notifyUrl(){
        $this->payment->response();
        exit();
    }

    public function returnUrl(){
        $result = $this->payment->page_response();
        if($result['status']==1){
            $this->redirect(url('index/index'));
        }else{
            $this->redirect(url('index/index'));
        }
    }
}
