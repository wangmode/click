<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/7
 * Time: 16:24
 */

namespace app\common\model;


use think\Model;

class UserTokenModel extends Model
{
    protected $connection       = 'db_daili';

    const EXPIRATION_TIME       =   15552000; // 180天

    const DEVICE_TYPE_MOBILE    = 'mobile';
    const DEVICE_TYPE_ANDROID   = 'android';
    const DEVICE_TYPE_IPHONE    = 'iphone';
    const DEVICE_TYPE_IPAD      = 'ipad';
    const DEVICE_TYPE_WEB       = 'web';
    const DEVICE_TYPE_PC        = 'pc';
    const DEVICE_TYPE_MAC       = 'mac';
    const DEVICE_TYPE_WXAPP     = 'wxapp';


    /**
     * 通过user_id 获取token
     * @param $user_id          //用户USER_ID
     * @param $deviceType       //设备类别
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getUserTokenByUserId($user_id,$deviceType)
    {
        return self::where([
                        'user_id'=>$user_id,
                        'device_type'=>$deviceType
                    ])
                    ->field(['expire_time','token'])
                    ->find();
    }

    /**
     * 新增token
     * @param $userId           //用户ID
     * @param $deviceType       //设备类别
     * @param $token            //TOKEN
     */
    static public function addUserToken($userId,$deviceType,$token)
    {
        self::insert([
            'token'       => $token,
            'user_id'     => $userId,
            'expire_time' => time() + self::EXPIRATION_TIME,
            'create_time' => time(),
            'device_type' => $deviceType
        ]);
    }

    static public function updateUserToken($user_id,$deviceType)
    {
        self::where([
                'user_id'=>$user_id,
                'device_type'=>$deviceType
            ])
            ->update([
            'expire_time' => time() + self::EXPIRATION_TIME,
            'create_time' => time()
        ]);

    }


}