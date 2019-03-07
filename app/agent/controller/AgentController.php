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
class AgentController extends UserBaseController
{

    /**
     * 客户列表
     */
    public function index()
    {

        $user = cmf_get_current_user();



        return $this->fetch();
    }
}
