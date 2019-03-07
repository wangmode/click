<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/25
 * Time: 9:02
 */

namespace app\common\model;


use think\Exception;
use think\Model;

class ConfigModel extends Model
{

    protected $connection = 'db_machine';


    /**
     * @param $admin_id
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getConfigInfoByAdminId($admin_id){
        $info = self::where('admin_id',$admin_id)->field('amount,remaining')->find()->toArray();
        return $info;
    }


    /**
     * 续费更新
     * @param $admin_id
     * @param $num              //续费信息条数
     * @param $expire_time       //续费更新时间
     * @throws Exception
     */
     static public function cstomerRenew($admin_id,$num,$expire_time)
     {
         $num = self::where('admin_id',$admin_id)
             ->inc('amount',$num)
             ->inc('remaining',$num)
             ->update(['expire_time'=>$expire_time]);
         if($num != 1){
            throw new Exception('续费失败，请稍后再试！');
         }
     }


}