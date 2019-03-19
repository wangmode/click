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
namespace app\user\controller;

use cmf\controller\CustomerBaseController;
use \think\Db;
use think\Exception;
use \think\Validate;
use app\user\model\CustomerModel;
class SettingController extends CustomerBaseController
{
    protected $connection= 'db_daili';

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

            try{
                $userId = cmf_get_current_customer_id();
                $model = new CustomerModel();
                $pwd = $model->getPassword($userId);

                $oldPassword = $data['old_password'];
                $password    = $data['password'];
                $rePassword  = $data['re_password'];

                if (password_verify($oldPassword,$pwd)) {
                    if ($password == $rePassword) {
                        if (password_verify($password,$pwd)) {
                            return json(['status'=>0,'msg'=>'新密码不能和原始密码相同']);
                        } else {
                            $model->updatePwd($userId,$password);
                            return json(['status'=>1,'msg'=>'密码修改成功']);
                        }
                    } else {
                        return json(['status'=>0,'msg'=>'密码输入不一致']);
                    }
                } else {
                    return json(['status'=>0,'msg'=>'原始密码不正确']);
                }
            }catch (Exception $exception){
                return json(['status'=>0,'msg'=>$exception->getMessage()]);
            }

        }
    }


    /**
     * 清除缓存
     */
    public function clearcache()
    {
        cmf_clear_cache();
        return $this->fetch();
    }


}