<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/16 0016
 * Time: 16:15
 */
namespace app\user\controller;

use cmf\controller\UserBaseController;

class SearchController extends UserBaseController
{
    public function index()
    {
        $product = ProductModel::getProductList();
        $this->assign('product',$product);
        return $this->fetch();
    }
}