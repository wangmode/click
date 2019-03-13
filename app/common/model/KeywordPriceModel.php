<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/11
 * Time: 15:38
 */

namespace app\common\model;


use think\Model;

class KeywordPriceModel extends Model
{
    const COEFFICIENT_BAIDU_INDEX   = 0.03;     //百度指数 系数
    const COEFFICIENT_BIDWORD_KWC   = 0.3;      //竞价竞争激烈程度 系数
    const COEFFICIENT_BIDWORD_PCPV  = 0.07;     //百度PC检索量 系数


    /**
     * 获取基础价格
     * @param $baidu_index //百度指数
     * @param $bidword_kwc //竞价竞争激烈程度
     * @param $bidword_pcpv //百度PC检索量
     * @return float|string
     */
    static public function getBasicsPrice($baidu_index, $bidword_kwc, $bidword_pcpv)
    {
        if (empty($baidu_index) && empty($bidword_kwc) && empty($bidword_pcpv)) {
            return 4.5;
        }
        $price = bcadd(bcadd(self::getProduct(self::COEFFICIENT_BAIDU_INDEX, $baidu_index), self::getProduct(self::COEFFICIENT_BIDWORD_KWC, $bidword_kwc), 2), self::getProduct(self::COEFFICIENT_BIDWORD_PCPV, $bidword_pcpv), 2);
        if(bccomp($price,4.5) === -1){
            return 4.5;
        }
        return $price;
    }


    /**
     * 获取乘积
     * @param $coefficient //系数
     * @param $param //参数
     * @return string
     */
    static public function getProduct($coefficient, $param)
    {
        if (empty($param)) {
            return 1;
        }
        $product = bcmul($coefficient, $param, 2);
        return $product;
    }

}