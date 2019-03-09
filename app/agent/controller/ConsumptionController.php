<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8 0008
 * Time: 11:36
 */
namespace app\agent\controller;
use cmf\controller\UserBaseController;
use app\agent\model\KeywordProductModel;

class ConsumptionController extends UserBaseController
{
    /**
     * 消费记录列表
     */
    public function index()
    {
        $type = $this->request->param('type')?:2;
        $this->assign('type',$type);

        return $this->fetch();
    }

    //获取客户信息
    public function data()
    {
        $agentId  = cmf_get_current_user_id();
        $model = new KeywordProductModel();
        $total = $model->getTotal($agentId);


        $data['code'] = 200;
        $data['count'] = 25;
        $data['message'] = '';
        $data['total'] = $total;
        return json($data);
//        $info = $this->request->param();
//        print_r($info);die;
    }
}