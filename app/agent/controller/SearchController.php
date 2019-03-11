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
use \think\Db;

class SearchController extends UserBaseController {
    public function index(){

        return $this->fetch();
    }

    public function data(){
        $info = $this->request->param();

        $search = new SearchModel();
        $datas = $search->Search('keyword' , $info['keywords']);


        $data['code'] = 200;
        $data['message'] = '';
        $data['data'] = $datas;
        return json($data);
    }
}