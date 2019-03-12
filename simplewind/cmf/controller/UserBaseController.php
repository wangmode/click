<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

class UserBaseController extends HomeBaseController
{
    const CODE_OK   = 200;
    const CODE_FAIL = 0;

    const STATUS_OK     = 1;
    const STATUS_FAIL   = 0;


    public function _initialize()
    {
        parent::_initialize();
        $this->checkUserLogin();
    }

    /**
     * @param int $code
     * @param int $count
     * @param null $data
     * @param string $message
     * @return \think\response\Json
     */
    protected function returnListJson($code = self::CODE_FAIL,$count = 0,$data= null, $message = '')
    {
        return json(['code'=>$code,'count'=>$count,'data'=>$data,'message'=>$message]);
    }


    /**
     * @param int $status
     * @param null $data
     * @param string $message
     * @return \think\response\Json
     */
    protected function returnJson($status = self::STATUS_FAIL,$data= null, $message = '')
    {
        return json(['status'=>$status,'data'=>$data,'message'=>$message]);
    }


    /**
     * @param int $status
     * @param null $customer_status
     * @param string $message
     * @return \think\response\Json
     */
    protected function returnStatusJson($status = self::STATUS_FAIL,$customer_status= null, $message = '')
    {
        return json(['status'=>$status,'customer_status'=>$customer_status,'msg'=>$message]);
    }


}