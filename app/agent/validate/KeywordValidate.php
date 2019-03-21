<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21 0021
 * Time: 11:44
 */
namespace app\agent\validate;

use think\Validate;

class KeywordValidate extends Validate
{
    protected $rule = [
        'customer_id' => 'require',
        'price'       => 'require',
        'setmeal'     => 'require',
        'url'         => 'require'
    ];

    protected $message = [
        'customer_id.require' => '非法访问',
        'price.require'       => '搜索引擎不能为空',
        'setmeal.require'     => '计费时间不能为空',
        'url.require'         => '网址不能为空'
    ];
}