<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/11
 * Time: 16:12
 */

namespace app\common\model;


use think\Model;

class ProductModel extends Model
{

    const STATUS_PROHIBIT   = 0 ;   //禁用
    const STATUS_NORMAL     = 1 ;   //正常


    /**
     * 获取正常产品列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getProductList()
    {
        return self::where('status',self::STATUS_NORMAL)->select()->toArray();
    }

    /**
     * 通过ID 获取产品
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getProductById($id)
    {
        return self::where('status',self::STATUS_NORMAL)->where('id',$id)->find();
    }

    /**
     * 获取指定产品系数
     * @param $id
     * @return mixed
     */
    static public function getProductCoefficientById($id)
    {
        return self::where('status',self::STATUS_NORMAL)->where('id',$id)->value('coefficient');
    }


    /**
     * //指定关键词获取产品价格列表
     * @param $baidu_index //百度指数
     * @param $bidword_kwc //竞价竞争激烈程度
     * @param $bidword_pcpv //百度PC检索量
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getProductPriceList($baidu_index, $bidword_kwc, $bidword_pcpv)
    {
        $product_list = self::getProductList();
        $basics_price = KeywordPriceModel::getBasicsPrice($baidu_index, $bidword_kwc, $bidword_pcpv);
        foreach ($product_list as $key=>$value){
            $product_list[$key]['price'] = bcmul($value['coefficient'],$basics_price,2);
        }
        return $product_list;
    }

}