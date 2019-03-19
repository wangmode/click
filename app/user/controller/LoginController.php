<?php
// +----------------------------------------------------------------------
// | 优站通 [ 一站到底，让排名不是梦 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.youzhantong.vip All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: WangMode <wangmode@163.com>

namespace app\user\controller;

use think\Validate;
use cmf\controller\HomeBaseController;
use app\user\model\CustomerModel;

class LoginController extends HomeBaseController
{

    /**
     * 登录
     */
    public function index()
    {
        $redirect = $this->request->post("redirect");
        if (empty($redirect)) {
            $redirect = $this->request->server('HTTP_REFERER');
        } else {
            $redirect = base64_decode($redirect);
        }
        session('login_http_referer', $redirect);
        if (cmf_is_users_login()) { //已经登录时直接跳到首页
            return redirect($this->request->root() . 'index/index');
        } else {
            return $this->fetch(':login');
        }
    }

    /**
     * 登录验证提交
     */
    public function doLogin()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'captcha'  => 'require',
                'username' => 'require',
                'password' => 'require|min:6|max:32',
            ]);
            $validate->message([
                'username.require' => '用户名不能为空',
                'password.require' => '密码不能为空',
                'password.max'     => '密码不能超过32个字符',
                'password.min'     => '密码不能小于6个字符',
                'captcha.require'  => '验证码不能为空',
            ]);

            $data = $this->request->post();

            if (!$validate->check($data)) {
                return json(['status'=>0,'msg'=>$validate->getError()]);
            }

            if (!cmf_captcha_check($data['captcha'])) {
                return json(['status'=>0,'msg'=>lang('CAPTCHA_NOT_RIGHT')]);
            }
            $userModel         = new CustomerModel();
            $user['password'] = $data['password'];
            $user['account'] = $data['username'];

            $log   = $userModel->doName($user);
            $session_login_http_referer = session('login_http_referer');
            $redirect = empty($session_login_http_referer) ? $this->request->root() : $session_login_http_referer;

            if($log['status']==1){
                return json(['status'=>1,'msg'=>$log['massage'],'url'=>$redirect]);
            }else{
                return json(['status'=>0,'msg'=>$log['massage']]);
            }
        } else {
            return json(['status'=>0,'msg'=>'请求错误!']);
        }
    }
}