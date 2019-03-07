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
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use cmf\lib\Upload;
class CommonController extends AdminBaseController
{
    /*
     * 公共文件上传
     * */
    public function upload(){
        $type = $this->request->param('type');

        $uploader = new Upload();

        $result = $uploader->upload();
        if ($result === false) {
            return json(['status'=>0,'msg'=>$uploader->getError()]);
        } else {
            $url = get_img_url($result['filepath'],3);
            return json(['status'=>1,'msg'=>'上传成功','type'=>$type,'data'=>['site_url'=>$result['filepath'],'oss_url'=>$url]]);
        }
    }


    /*
     * 编辑器图片上传
     * */
    public function editor_upload(){
        $type = $this->request->param('type');
        $uploader = new Upload();
        $result = $uploader->upload();
        if ($result === false) {
            return json(['code'=>1,'msg'=>$uploader->getError()]);
        } else {
            $url = get_img_url($result['filepath'],3);
            return json(['code'=>0,'msg'=>'上传成功','type'=>$type,'data'=>['title'=>'产品图片','src'=>$url]]);
        }
    }
}