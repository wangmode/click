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
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\Menu;
use app\admin\model\DeployModel;
class MainController extends AdminBaseController
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

    //业务出单统计
    function business_data(){
        $info = $this->request->param();
        $where['c.type'] = 1;
        $start_time = 0;
        $end_time = time();
        if(isset($info['time'])){
            $time_array = explode(' - ',$info['time']);
            $start_time = strtotime(trim($time_array[0]));
            $end_time = strtotime(trim($time_array[1]));
        }
        $start = ($info['page']-1)*$info['limit'];
        $data = Db::name('consume')
            ->group('a.admin_id')
            ->alias('c')
            ->join('yzt_agent a','c.agent_id = a.id')
            ->limit($start,$info['limit'])
            ->field('count(c.id) as value,sum(c.money) as money,a.admin_id')
            ->where('c.type',1)
            ->where('c.time','>',$start_time)
            ->where('c.time','<',$end_time)
            ->select();
        $count =Db::name('consume')
            ->group('a.admin_id')
            ->alias('c')
            ->join('yzt_agent a','c.agent_id = a.id')
            ->field('count(c.id) as value,sum(c.money) as money,a.admin_id')
            ->where('c.type',1)
            ->where('c.time','>',$start_time)
            ->where('c.time','<',$end_time)
            ->count();
        $data_list = [];
        foreach ($data as $k=>$v){
            if($v['admin_id']){
                $name = Db::name('user')->where('id',$v['admin_id'])->value('user_login');
                $v['name'] = $name;
            }else{
                $v['name'] = '无业游民';
            }
            $data_list[] = $v;
        }
        $data['code'] = 200;
        $data['count'] = $count;
        $data['message'] = '';
        $data['data'] = $data_list;
        return json($data);

    }



    public function tongji(){
        $yzt= Db::query("select left(FROM_UNIXTIME(add_time),7) as month, count(id) as count
from yzt_customer where type=1
group by left(FROM_UNIXTIME(add_time),7)");
        $wtx= Db::query("select left(FROM_UNIXTIME(add_time),7) as month, count(id) as count
from yzt_customer where type=2
group by left(FROM_UNIXTIME(add_time),7)");

        $consume = Db::query("select left(FROM_UNIXTIME(time),7) as month, sum(if(money is null,0,money)) as sum
from yzt_consume where type=1
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





    //地区统计
    public function area_tongji(){
//        $province = Db::name('agent')->field('id,province')->select();
//        foreach ($province as $v){
//            $name = Db::name('area')->where('id',$v['province'])->value('name');
//            $id = Db::name('city')->where('name',$name)->value('id');
//            echo $name.'<br>';
//            echo $id.'\n<br>';
//            Db::name('agent')->where('id',$v['id'])->update(['province'=>$id]);
//
//        }




        $data = Db::name('customer')->group('province')->alias('c')->join('yzt_city a','c.province = a.id')->field('count(c.id) as value,a.name')->select()->toArray();

        //$data = Db::name('agent')->group('province')->alias('c')->join('yzt_city a','c.province = a.id')->field('count(c.id) as value,a.name')->select()->toArray();
        $max = 200;
        foreach ($data as $v){
            if($v['value']>$max){
                $max = $v['value'];
            }
        }
        return json(['code'=>0,'max'=>$max,'data'=>$data]);
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
