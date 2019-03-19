<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19 0019
 * Time: 8:47
 */

namespace cmf\controller;


class CustomerBaseController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
        $this->checkCustomerLogin();
    }

}