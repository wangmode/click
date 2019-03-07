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
use \think\Validate;
use \think\Db;

class CaseController extends UserBaseController
{
    /**
     *案例管理
     */
    public function index()
    {
        $class_info = cmf_get_option('class_info');
        $this->assign('class_info',$class_info);
        return $this->fetch();
    }

    //获取案例
    public function data()
    {
        $info = $this->request->param();
        $start = ($info['page']-1)*$info['limit'];
        $where = [];
        $where['is_show'] = 1;
        $where['sum'] = ['gt',3000];
        $class_id = $this->request->param('class_id');
        if($class_id){
            $where['class_id'] = $this->request->param('class_id');
        }
        $keyword = $this->request->param('keyword');
        if($keyword){
            $where['company|keyword'] = ['like','%'.$keyword .'%'];
        }
        $count = Db::name('case')->where($where)->count();
        $case_list = Db::name('case')->limit($start,$info['limit'])->where($where)->order('id', 'desc')->select();
        $class_info = cmf_get_option('class_info');
        $case_data = [];
        foreach ($case_list as $k=>$v){
            $class = $class_info[$v['class_id']];
            $case_data[$k] = $v;
            $case_data[$k]['class'] = $class;
        }
        $data['code'] = 200;
        $data['count'] = $count;
        $data['message'] = '';
        $data['data'] = $case_data;
        return json($data);
    }

    //客户信息
    public function customer_data()
    {
        $info = $this->request->param();
        $start = ($info['page']-1)*$info['limit'];
        //关键词查询
        $where['status'] = 1;
        if(isset($info['company']))$where['company'] = ['like', "%{$info['company']}%"];
        $count = Db::name('customer')->where($where)->count();
        $customer_list = Db::name('customer')->where($where)->limit($start,$info['limit'])->select();
        $data['code'] = 0;
        $data['count'] = $count;
        $data['message'] = '';
        $data['data'] = $customer_list;
        return json($data);
    }

    /**
     * 添加新案例
     */
    public function add()
    {
        $class_info = cmf_get_option('class_info');
        //业务列表
        $admin_list = Db::name('user')->where('user_status',1)->field('id,user_login')->select();
        $this->assign('admin_list',$admin_list);
        $this->assign('class_info',$class_info);
        return $this->fetch();
    }

    /**
     * 添加案例提交保存
     */
    public function add_Post()
    {
        if($this->request->isPost()){
            $info = $this->request->post();
            $case_info['company'] = $info['company'];
            $case_info['class_id'] = $info['class'];
            $case_info['type'] = $info['type'];
            $case_info['keyword'] = $info['keyword'];
            $case_info['customer_id'] = $info['customer_id'];
            $case_info['url'] = $info['url'];
            $re = Db::name('case')->insert($case_info);
            if($re){
                return json(['status'=>1,'msg'=>'案例添加成功！']);
            }else{
                return json(['status'=>0,'msg'=>'添加失败，请稍后再试！']);
            }
        }else{
            return json(['status'=>0,'msg'=>'没有收到数据！']);
        }
    }

    public function to_disable(){
        $info = $this->request->param();
        $now_status = Db::name('case')->where('id',$info['id'])->value('status');
        $now_status==1 ? $status = 0 : $status = 1;
        $re = Db::name('case')->where('id',$info['id'])->setField('status', $status);
        if($re){
            return json(['status'=>1,'customer_status'=>$status,'msg'=>'操作成功！']);
        }else{
            return json(['status'=>0,'customer_status'=>$status,'msg'=>'操作失败！']);
        }
    }
}