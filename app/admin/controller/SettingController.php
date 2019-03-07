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
namespace app\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;
use cmf\lib\Upload;
use think\Db;
use \think\Validate;
use app\admin\model\XzhModel;
use app\admin\model\Update;
use app\admin\model\InfoModel;
class SettingController extends AdminBaseController
{

    /**
     * 网站信息
     */
    public function site()
    {
        $this->assign('site_info', cmf_get_option('site_info'));
        return $this->fetch();
    }

    function xzh_cx(){
        $xzh = new XzhModel();
        $re = $xzh->inquireReview('Nfgad0LYyUxMeqQNi0');
        print_r($re);exit;
    }



    /**
     * 网站信息设置提交
     */
    public function sitePost()
    {
        if ($this->request->isPost()) {
//            $result = $this->validate($this->request->param(), 'SettingSite');
//            if ($result !== true) {
//                return json(['status'=>0,'msg'=>$result]);
//            }
            $options = $this->request->param('site_info/a');
            cmf_set_option('site_info', $options);
            return json(['status'=>1,'msg'=>'保存成功']);
        }else{
            return json(['status'=>0,'msg'=>'没有数据提交']);
        }
    }

    /**
     * 密码修改
     */
    public function password()
    {
        return $this->fetch();
    }

    /**
     * 密码修改提交
     */
    public function passwordPost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            if (empty($data['old_password'])) {
                return json(['status'=>0,'msg'=>'原始密码不能为空']);
            }
            if (empty($data['password'])) {
                return json(['status'=>0,'msg'=>'新密码不能为空']);
            }

            $userId = cmf_get_current_admin_id();

            $admin = Db::name('user')->where(["id" => $userId])->find();

            $oldPassword = $data['old_password'];
            $password    = $data['password'];
            $rePassword  = $data['re_password'];

            if (cmf_compare_password($oldPassword, $admin['user_pass'])) {
                if ($password == $rePassword) {
                    if (cmf_compare_password($password, $admin['user_pass'])) {
                        return json(['status'=>0,'msg'=>'新密码不能和原始密码相同']);
                    } else {
                        db('user')->where('id', $userId)->update(['user_pass' => cmf_password($password)]);
                        return json(['status'=>1,'msg'=>'密码修改成功']);
                    }
                } else {
                    return json(['status'=>0,'msg'=>'密码输入不一致']);
                }

            } else {
                return json(['status'=>0,'msg'=>'原始密码不正确']);
            }
        }
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        cmf_clear_cache();
        return $this->fetch();
    }


}