<?php
// +----------------------------------------------------------------------
// | 优站通 [ 一站到底，让排名不是梦 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.youzhantong.vip All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: WangMOde <wangmode@163.com>
// +----------------------------------------------------------------------
namespace app\agent\model;

use think\Model;
use think\db;
class CustomerModel extends Model
{
    //添加客户
    function add_customer($customer_info,$agent_id){
        $product = CustomerModel::name('product')->where('id',$customer_info['product_id'])->find()->toArray();
        if(!$product){
            $re['status'] = 0;
            $re['msg'] = '产品不存在，请重新选择！';
            return $re;
        }
        //余额检测
        $price = $product['price'];
        $agent_info = CustomerModel::name("agent")->where("id",$agent_id)->find();
        if($price>$agent_info['money']){
            $re['status'] = 0;
            $re['msg'] = '用户余额不足，请先充值！';
            return $re;
        }

        $wtx_data['username'] = $customer_info['account'];
        $wtx_data['item'] = $customer_info['item'];
        $wtx_data['amount'] = $product['num'];
        $wtx_data['remaining'] = $product['num'];
        $wtx_data['password'] = md5($customer_info['password']);
        $wtx_data['class'] = $customer_info['class'];
        //网推侠添加
        $result = $this->add_wtx($wtx_data);

        if(!$result['status']){//添加失败
            return $result;
        }
        $wtx_id = $result['wtx_id'];

        $type = $product['type'];
        $year = $product['year'];
        $area = explode(',',$customer_info['area']);

        //过期时间
        $insert_data['add_time']  =  time();
        $insert_data['expire_time']  =  time()+31536000*$year;
        $insert_data['company']  =  trim($customer_info['company']);
        $insert_data['province']  =  $area[0];
        $insert_data['city']  =  $area[1];
        $insert_data['linkman']  =  trim($customer_info['linkman']);
        $insert_data['linkphone']  =  trim($customer_info['linkphone']);
        $insert_data['qq']  =  trim($customer_info['qq']);
        $insert_data['wechat']  =  trim($customer_info['wechat']);
        $insert_data['manager']  =  trim($customer_info['manager']);
        $insert_data['detail']  =  trim($customer_info['detail']);
        $insert_data['agent_id']  =  $agent_id;

        $insert_data['class'] = trim($customer_info['class']);
        $insert_data['item'] = trim($customer_info['item']);
        //账号名
        $insert_data['account'] = trim($customer_info['account']);

        //随机密码
        //$insert_data['password'] = str_rand(8);

        $insert_data['wtx_id'] = $wtx_id;


        if($type=='wtx'){//网推侠
            $insert_data['type']  =  2;
        }else{//优站通
            $insert_data['url']  =  trim($customer_info['url']);
            $insert_data['type']  =  1;
        }
        $insert_result = CustomerModel::name('customer')->insert($insert_data);
        if($insert_result){
            $consume_data = array(
                'money' => $price,
                'time' => time(),
                'agent_id' => $agent_id,
                'remark' => '为用户'.$customer_info['company'].'提单！'
            );
            //更新账户余额
            accountLog($agent_id, -$price, $desc = '为用户'.$customer_info['company'].'提单！');
            //插入消费信息
            CustomerModel::name('consume')->insert($consume_data);
            $re['status'] = 1;
            $re['msg'] = '添加成功！';
            return $re;
        }else{
            $re['status'] = 0;
            $re['msg'] = '客户添加失败！';
            return $re;
        }
    }



    //添加网推侠
    function add_wtx($wtx_data){
        $db = Db::connect('mysql://youzhantong:Yzt477515@rm-j6c97b4qmlz5q2jgkfo.mysql.rds.aliyuncs.com:3306/china_machine36#utf8');
        $item_count = $db->name('yzt_config')->where('item',$wtx_data['item'])->count();
        $username_count = $db->name('yzt_admin')->where('username',$wtx_data['username'])->count();
        if($item_count){
            $re['status'] = 0;
            $re['msg'] = '个性域名已存在！';
            return $re;
        }
        if($username_count){
            $re['status'] = 0;
            $re['msg'] = '账户名已存在！';
            return $re;
        }
        $db->startTrans();
            try{
                //添加网推侠账户
                $admin_data['username'] = $wtx_data['username'];
                $admin_data['password'] = $wtx_data['password'];
                $admin_data['add_time'] = time();
                $admin_data['status'] = 1;
                $admin_data['role_id'] = 14;
                $admin_id = $db->name("yzt_admin")->insertGetId($admin_data);
                if (!$admin_id) {
                    throw new \Exception("网推侠账户添加失败！");
                }
                //为用户添加配置信息
                $config_data['class'] =$wtx_data['class'];
                $config_data['admin_id'] = $admin_id;
                $config_data['amount'] = $wtx_data['amount'];
                $config_data['remaining'] = $wtx_data['remaining'];
                $config_data['item'] = $wtx_data['item'];
                $wtx_result = $db->name("yzt_config")->insert($config_data);
                $db->commit();
            }catch(\Exception $e){
                $db->rollback();
                return array('status'=>0,'msg'=>$e);
            }

        if(isset($wtx_result) && $wtx_result){

            return array('status'=>1,'msg'=>'网推侠添加成功','wtx_id'=>$admin_id);
        }else{
            return array('status'=>0,'msg'=>'网推侠添加失败');
        }
    }


    /*
     * 定时信息发布(临时)
     * */
    function to_tem_news(){


    }






}