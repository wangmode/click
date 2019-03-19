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
namespace app\user\controller;

use app\user\model\AgentModel;
use cmf\controller\CustomerBaseController;
use cmf\controller\HomeBaseController;

class IndexController extends CustomerBaseController
{

    /**
     * 前台用户首页(公开)
     */
    public function index()
    {
        $user = cmf_get_current_customer();
        if (empty($user)) {
            $this->redirect('login/index');
        }
        $this->assign('user',$user);
        return $this->fetch(":index");
    }

    /**
     * 前台ajax 判断用户登录状态接口
     */
    function isLogin()
    {
        if (cmf_is_users_login()) {
            $this->success("用户已登录",null,['user'=>cmf_get_current_user()]);
        } else {
            $this->error("此用户未登录!");
        }
    }

    /**
     * 退出登录
    */
    public function logout()
    {
        session("customer", null);//只有前台用户退出
        return redirect($this->request->root() . "login/index");
    }

}
