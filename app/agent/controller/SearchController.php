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
use app\agent\model\SearchModel;
use app\common\model\Keywords;
use app\common\model\GettingKeywordModel;
use app\common\model\ProductModel;
use think\Exception;

/**
 * Class SearchController
 * $X = baidu_index //百度指数
 * $Y = bidword_kwc //竞争激烈度
 * $Z = bidword_pcpv //百度PC检索量
 */
class SearchController extends UserBaseController {
    public function index()
    {
        $product = ProductModel::getProductList();
        $this->assign('product',$product);
        return $this->fetch();
    }

    public function data(){
        $info = $this->request->param();
        $keywords = new GettingKeywordModel();
        if (!empty($info['keywords'])){
            try {
                $datas = $keywords->getKeywordLists('1', $info['keywords'], "1", '10');

                $data = $keywords->getKeyword($datas, $info['keywords']);
            }catch (Exception $a){
                    $data = [
                        'list' =>[
                            'keyword' => $info['keywords'],
                            'price' => [
                                0 => [
                                    'id'=> 1,
                                    'name'=>'百度',
                                    'price'=>4.5
                                ],
                                1 => [
                                    'id'=> 2,
                                    'name'=>'360',
                                    'price'=>3.6
                                ],
                                2 => [
                                    'id'=> 3,
                                    'name'=>'搜狗',
                                    'price'=>2.70
                                ],
                                3 => [
                                    'id'=> 4,
                                    'name'=>'神马',
                                    'price'=>2.25
                                ],
                                4 => [
                                    'id'=> 5,
                                    'name'=>'神马',
                                    'price'=>5.40
                                ]
                            ]
                        ]
                    ];
                }
                 return $this->returnListJson(self::CODE_OK, "1", $data, '返回搜索数据');
        }
    }

    public function datas()
    {
        $info = $this->request->param();
        $keywords = new GettingKeywordModel();
        try{
            $page = $info['page'];
            $limit = $info['limit'];
            $result = $keywords->getKeywordLists('1', $info['keywords'], "$page", "$limit");
            $count = array_sum($result);
            return $this->returnListJson(self::CODE_OK, "$count", $result['list'], '返回搜索数据');
        }catch (Exception $error) {
            $data = [];
            return $this->returnListJson(self::CODE_OK, "1", $data, '返回搜索数据');
        }
    }
}