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

use cmf\controller\BaseController;
use think\Db;
//use app\admin\model\AdminMenuModel;

class ApiController extends BaseController
{
    public function checkVersion()
    {
        //当前版本
        $version = input('param.v');
        //最新版本信息
        $new_version_info = config('version_info');
        $new_version = $new_version_info['version'];
        $new_version_info['now_version'] = $version;
        if($new_version>$version){
            $data['status'] = 1;
            $data['sql_file'] = [];
            $data['version_info'] = $new_version_info;
            $data['msg'] = '有新版本更新！';
        }else{
            $data['status'] = 0;
            $data['sql_file'] = [];
            $data['version_info'] = $new_version_info;
            $data['msg'] = '没有发现新版本！';
        }

        return json($data);
    }

    function get_tem_list(){
        $class = $this->request->param('class')?:1;
        $temList = db('theme')->where('status',1)->where('class',$class)->select()->toArray();
        if(isset($temList)&& count($temList)>0){
            $data['status']=1;
            $data['class']=$class;
            $data['template'] = $temList;
            $data['msg'] = '模板信息！';
        }else{
            $data['status']=0;
            $data['class']=$class;
            $data['template'] = '';
            $data['msg'] = '模板信息拉取失败信息！';
        }
        return json($data);
    }

    function getCode(){
        $type = $this->request->param('type');
        $theme = $this->request->param('theme');
        $name = $this->request->param('name');
        $file = CMF_ROOT.'public/themes/'.$theme.'/'.$type.'/'.$name.'.html';
        $content = file_get_contents($file);
        if($content){
            return json(['status'=>1,'content'=>$content]);
        }else{
            return json(['status'=>0,'content'=>'']);
        }
    }

}
