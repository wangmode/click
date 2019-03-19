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
namespace app\user\model;

use think\Db;
use think\Model;

class CustomerModel extends Model
{
    const EXPIRATION_TIME       =   15552000; // 180天

    protected $connection= 'db_daili';

    protected $type = [
        'more' => 'array',
    ];

    public function doName($user)
    {
        $result = $this->where('account', $user['account'])->find();
        if (!empty($result)) {
            $comparePasswordResult = password_verify($user['password'], $result['password']);
            $hookParam             = [
                'user'                    => $user,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {
                //禁用判断。
                if ($result['status'] == 0) {
                    return ['status'=>3,'massage'=>'账号被禁止访问系统'];
                }
                session('customer', $result->toArray());
//                $data = [
//                    'last_login_time' => time(),
//                    'last_login_ip'   => get_client_ip(0, true),
//                ];
//                $result->where('id', $result["id"])->update($data);
//                $token = cmf_generate_user_token($result["id"], 'web');
                $token = $result['access_token'];
                if (empty($token)) {
                    $this->addToken($result['id']);
                    session('customer_token', $result['access_token']);
                }else{
                    if($result['token_expire_time'] > time() && !empty($token)){
                        session('token', $token);
                    }else{
                        $this->updateExpireTime($result['id']);
                    }
                }
                return ['status'=>1,'massage'=>lang('LOGIN_SUCCESS')];
            }
            return ['status'=>0,'massage'=>lang('PASSWORD_NOT_RIGHT')];
        }
        $hookParam = [
            'user'                    => $user,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return ['status'=>2,'massage'=>'账户不存在'];
    }


    /**
     * 更新token过期时间
     * @param $user_id
     * @param $deviceType
     */
    public function updateExpireTime($user_id)
    {
        self::where([
            'id'=>$user_id,
        ])
            ->update([
                'token_expire_time' => time() + self::EXPIRATION_TIME,
            ]);
    }

    /**
     * 添加客户token
     * @param $userId
     */
    public function addToken($userId)
    {
        $token = md5(uniqid()) . md5(uniqid());
        self::where(['id'=>$userId])->update([
            'access_token' => $token,
            'token_expire_time'  => time() + self::EXPIRATION_TIME,
        ]);
    }

    /**
     * 获取用户密码
     * @param $id
     * @return mixed
     */
    public function getPassword($id)
    {
        $data = self::where(['id'=>$id])->find();
        return $data['password'];
    }

    /**
     * 修改用户密码
     * @param $id
     * @param $pwd
     */
    public function updatePwd($id,$pwd)
    {
        self::where(['id'=>$id])->update(['password'=>password_hash($pwd,PASSWORD_DEFAULT)]);
    }
}
