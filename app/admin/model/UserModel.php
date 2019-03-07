<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class UserModel extends Model
{

    protected $type = [
        'more' => 'array',
    ];

    /**
     * 获取所有业务员列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSalesmanList()
    {
        $admin_list = self::alias('u')
            ->join('role_user r','u.id = r.user_id')
            ->field(['u.id','u.user_login'])
            ->where('r.role_id',3)
            ->select();
        return $admin_list;
    }


}