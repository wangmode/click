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
use app\common\model\ProductModel;

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
        $keywords = new Keywords();

        if (!empty($info['keywords'])){
             $datas = $keywords->getKeywordList('1', $info['keywords'], "1", '10');

            if (!empty($datas['list'])) {
                $data = $keywords->getKeyword($datas , $info['keywords']);
            }else {
                $data = [];
            }
             return $this->returnListJson(self::CODE_OK, "1", $data, '返回搜索数据');
        }else {
            $data = [];
            return $this->returnListJson(self::CODE_OK, "1", $data, '返回搜索数据');
        }

    }

    public function datas()
    {
        $info = $this->request->param();
        $keywords = new Keywords();
        if (!empty($info['keywords'])) {
            $page = $info['page'];
            $limit = $info['limit'];
            $result = $keywords->getKeywordList('1', $info['keywords'], "$page", "$limit");
            $count = array_sum($result);
            return $this->returnListJson(self::CODE_OK, "$count", $result['list'], '返回搜索数据');
        }else {
            $data = [];
            return $this->returnListJson(self::CODE_OK, "1", $data, '返回搜索数据');
        }
    }
}