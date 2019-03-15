<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8
 * Time: 15:50
 */

namespace app\common\model;


use think\Model;

class CityModel extends Model
{
    protected $connection= 'db_daili';

    /**
     * 获取省级行政区列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getProvinceList()
    {
        return self::where('level',1)->field('id,parent_id,level,name')->select();
    }


    /**
     * 通过省级地区代码获取市级地区列表
     * @param $province_id      //省级地区ID
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getCityList($province_id)
    {
        return self::where('parent_id',$province_id)->select();
    }



}