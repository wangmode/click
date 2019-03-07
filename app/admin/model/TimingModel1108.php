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
namespace app\admin\model;

use app\admin\model\InfoModel;
use app\admin\model\XzhModel;
use think\db;
use think\Model;

class TimingModel extends Model
{
    protected $table = 'yzt_info';
    // 设置当前模型的数据库连接
    protected $connection = [
        // 数据库类型
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => 'rm-j6c97b4qmlz5q2jgkfo.mysql.rds.aliyuncs.com',
        // 数据库名
        'database'    => 'yzt-news',
        // 数据库用户名
        'username'    => 'youzhantong',
        // 数据库密码
        'password'    => 'Yzt477515',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => 'yzt_',
        // 数据库调试模式
        'debug'       => false,
    ];

    //消息自动加入发布队列
    function news_auto_queue(){
        $two_db = Db::connect('mysql://youzhantong:Yzt477515@rm-j6c97b4qmlz5q2jgkfo.mysql.rds.aliyuncs.com:3306/china_machine36#utf8');
        //网站配置信息
        $site_info = cmf_get_option('site_info');
        $time = date('Y-m-d');
        $numbers = $site_info['fb_day_count'];
        $user_list =  $this->setTable('yzt_user')->where('status',1)->field('id,wtx_admin_id')->where('release_time','< time',$time)->limit($numbers)->select()->toArray();
        $info = [];
        $num = $site_info['fb_day_num'];
        foreach ($user_list as $k=>$v){
            $info = $this->setTable('yzt_info')->where('admin_id',$v['wtx_admin_id'])->limit($num)->select()->toArray();
            $info_array = [];
            foreach ($info as $kk=>$vv){
                $new_data['title'] = $vv['title'];
                $new_data['tid'] = 0;
                $new_data['admin_id'] = $vv['admin_id'];
                $new_data['cover'] = $vv['cover'];
                $new_data['task_id'] = 0;
                $new_data['class_id'] = $vv['class_id'];
                $new_data['content'] = $vv['content'];
                $new_data['item'] = $vv['item'];
                //发布id
                $info_array[] = $vv['id'];
                $news[] = $new_data;
            }

            //删除发布信息
            $d_result =  $this->setTable('yzt_info')->delete($info_array);

            if(isset($news)){
                shuffle($news);
                echo '数据处理';
                echo  date("H:i:s").'<br>';
                $result = $two_db->name('yzt_tem_news')->insertAll($news);
                echo '数据添加完成'.'<br>';
                if(($result>-1) && $d_result>0 ){
                    echo  '发布成功';
                }else{
                    echo '发布失败';
                }
            }else{
                echo '本次发布数量为0';
            }
            //更新时间
            $this->setTable('yzt_user')->where('id',$v['id'])->update(['release_time'=>time()]);
        }
    }

    //熊掌号审核结果查询
    function inquireReview(){
        $site_info = cmf_get_option('site_info');
        $user = Db::name('xzh_user')->where('status',1)->limit($site_info['cx_each_count_'])->column('id,name,third_id');
        if($user){
            //tp信息
            $tp_info = Db::name('xiongzhang')->where('clientId','Nk1mRQlCjFTDxnnYEWOcyIeieFtEe6ba')->find();
            $tp_verify_ticket = $tp_info['tp_verify_ticket'];
            $time = time();
            if($tp_info['expire_time']>$time){
                $tp_access_token = $tp_info['access_token'];
            }else{//access_token过期重新获取
                $result = httpRequest("https://openapi.baidu.com/oauth/2.0/token?grant_type=tp_credentials&tp_client_id=Nk1mRQlCjFTDxnnYEWOcyIeieFtEe6ba&tp_client_secret=HlMNC1gvv6HjZgws7Qav2kxvq54zy6LG&tp_verify_ticket=$tp_verify_ticket");
                $result = json_decode($result,true);
                $tp_access_token = $result['tp_access_token'];
                $info['access_token'] = $tp_access_token;
                //有效期2小时，防止误差，遂让其1小时50分过期
                $info['expire_time'] = $time+6600;
                Db::name('xiongzhang')->where('clientId',$tp_info['clientId'])->update($info);
            }
            foreach ($user as $k=>$v){
                $third_id = $v['third_id'];
                $info_re= httpRequest("https://msite.baidu.com/rest/2.0/cambrian/tp/get_child_xzh_info?tp_access_token=$tp_access_token&third_id=$third_id");
                $info_re = json_decode($info_re,true);
                $status = ['待审','审核拒绝','通过','封禁','已注销','已删除','禁止','预审失败','手机号待验证'];
                if(isset($info_re['error_code'])){
                    return array('status'=>0,'msg'=>'查询失败，错误代码'.$info_re['error_code'].'，'.$info_re['error_msg']);
                }else{
                    if(isset($info_re['office_info']['auth_status']) && $info_re['office_info']['auth_status']==2){
                        //审核通过，更新状态
                        Db::name('xzh_user')->where('id',$v['id'])->setField('status', 2);
                        return array('status'=>1,'msg'=>'审核通过！');
                    }else{
                        //审核失败
                        $reason='';
                        if(isset($info_re['office_info']['auth_msg']))$reason = '，原因：'.$info_re['office_info']['auth_msg'];
                        //更新状态
                        Db::name('xzh_user')->where('id',$v['id'])->update(['status' => 3,'reason'=>$reason]);
                        return json(array('status'=>0,'msg'=>'账号状态：'.$status[$info_re['office_info']['auth_status']].$reason));
                    }
                }
            }
        }else{
            echo '没有审核中的熊掌号！';exit;
        }
    }
}
