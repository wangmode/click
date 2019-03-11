<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/28
 * Time: 8:59
 */

namespace app\agent\validate;


use think\Validate;

class CustomerKPBValidate extends Validate
{
    protected $rule = [
        'id'            => 'require',
        'account'       => 'require|unique:customer',
        'password'      => 'require|confirm|min:6|max:32',
        'company'       => 'require',
        'agent_id'      => 'require',
        'area'          => 'require',
        'class'         => 'require',
        'linkman'       => 'require',
        'linkphone'     => 'require',
        'qq'            => 'require',
        'wechat'        => 'require',
        'detail'        => 'require',
    ];

    protected $message = [
        'id.require'            => '非法访问',
        'account.require'       => '账号名称不能为空',
        'account.unique'        => '账号名称已存在',
        'password.require'      => '密码不能为空',
        'password.confirm'      => '两次密码输入不一致',
        'password.max'          => '密码不能超过32个字符',
        'password.min'          => '密码不能小于6个字符',
        'company.require'       => '企业名称不能为空',
        'agent_id.require'      => '代理商不能为空',
        'area.require'          => '地区选择不能为空',
        'class.require'         => '行业选择不能为空',
        'linkman.require'       => '联系人名称不能为空',
        'linkphone.require'     => '联系方式不能为空',
        'qq.require'            => 'QQ号码不能为空',
        'wechat.require'        => '微信号码不能为空',
        'detail.require'        => '详细资料不能为空',
    ];


    protected $scene = [
        'add'  => ['account','password', 'agent_id', 'area', 'class', 'linkman', 'linkphone', 'qq', 'wechat', 'detail'],
        'edit' => ['password'=>'confirm','company', 'area', 'id','class', 'linkman', 'linkphone', 'qq', 'wechat', 'detail'],
    ];


}