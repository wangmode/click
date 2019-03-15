<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/14
 * Time: 11:13
 */

namespace app\agent\validate;


use think\Validate;

class RenewValidate extends Validate
{

    protected $rule = [
        'id'            => 'require|number',
        'money'         => 'require|float'
    ];

    protected $message = [
        'id.require'            => '非法访问',
        'id.number'             => '非法访问',
        'money.require'         => '充值金额不能为空',
        'money.float'           => '充值金额不合法'
    ];
}