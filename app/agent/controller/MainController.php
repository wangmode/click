<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\agent\controller;

use cmf\controller\UserBaseController;
use think\Db;
class MainController extends UserBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     *  后台欢迎页
     */
    public function index()
    {
        return $this->fetch();
    }
    public function tongji(){
        $agent_id = cmf_get_current_user_id();
        $yzt= Db::query("select left(FROM_UNIXTIME(add_time),7) as month, count(id) as count
from yzt_customer where type=1 and agent_id=".$agent_id."
group by left(FROM_UNIXTIME(add_time),7)");
        $wtx= Db::query("select left(FROM_UNIXTIME(add_time),7) as month, count(id) as count
from yzt_customer where type=2 and agent_id=".$agent_id."
group by left(FROM_UNIXTIME(add_time),7)");

        $consume = Db::query("select left(FROM_UNIXTIME(time),7) as month, sum(if(money is null,0,money)) as sum
from yzt_consume where type=1 and agent_id=".$agent_id."
group by left(FROM_UNIXTIME(time),7)");

        $count['yzt'] = count($yzt);
        $count['wtx'] = count($wtx);
        $count['consume'] = count($consume);
        $max = max($count);
        $flip_count = array_flip($count);
        $key = $flip_count[$max];
        $month = [];
        $data_yzt = [];
        $data_wtx = [];
        $data_consume = [];
        foreach ($yzt as $ky=>$vy){
            $data_yzt[$vy['month']] = $vy['count'];
        }

        foreach ($wtx as $kw=>$vw){
            $data_wtx[$vw['month']] = $vw['count'];
        }
        foreach ($consume as $kc=>$vc){
            $data_consume[$vc['month']] = $vc['sum'];
        }

        foreach ($$key as $k=>$v){
            $month[] = $v['month'];
            if(!array_key_exists($v['month'],$data_yzt)){
                $data_yzt[$v['month']] = 0;
            }
            if(!array_key_exists($v['month'],$data_wtx)){
                $data_wtx[$v['month']] = 0;
            }
            if(!array_key_exists($v['month'],$data_consume)){
                $data_consume[$v['month']] = 0;
            }
        }

        ksort($data_yzt);
        ksort($data_wtx);
        ksort($data_consume);
        $data_info['label'] = $month;
        $data_info['yzt'] = array_values($data_yzt);
        $data_info['wtx'] = array_values($data_wtx);
        $data_info['sum'] = array_values($data_consume);
        return json(['code'=>0,'data'=>$data_info]);
    }

    public function dashboardWidget()
    {
        $dashboardWidgets = [];
        $widgets          = $this->request->param('widgets/a');
        if (!empty($widgets)) {
            foreach ($widgets as $widget) {
                if ($widget['is_system']) {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 1]);
                } else {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 0]);
                }
            }
        }

        cmf_set_option('admin_dashboard_widgets', $dashboardWidgets, true);

        $this->success('更新成功!');

    }


}
