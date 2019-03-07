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
namespace app\admin\model;

use think\Model;

class OuterModel extends Model
{
    protected $table = 'yzt_sptask';

    //添加任务
    public function add_task($info)
    {
        $time = time();
        $data['name'] = $info['name'];
        $url = $info['url'];
        $data['urls'] = str_replace("\n",',',$url);
        $data['num'] = substr_count($url,"\n")+1;
        $data['add_time'] = $time+3600*14*7;
        $data['expire_time'] = $time+3600*14*7;
        $re = OuterModel::save($data);
        if($re>0){
            return true;
        }
        return false;

    }
}