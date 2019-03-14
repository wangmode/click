<?php
// +----------------------------------------------------------------------
// | 优站通 [ 一站到底，让排名不是梦 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.youzhantong.vip All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: WangMOde <wangmode@163.com>
// +----------------------------------------------------------------------
namespace app\agent\model;

use app\common\model\KeywordProductModel;
use app\common\model\OperationLogModel;
use think\Exception;
use think\Model;
use think\db;
class CustomerModel extends Model
{

    protected $connection = "db_daili";

    const STATUS_UNAUDITED = 0;     //未审核
    const STATUS_NORMAL = 1;        //正常
    const STATUS_PROHIBIT = 2;      //禁用

    const TYPE_YZT = 1;             //优站通（熊掌客）
    const TYPE_WTX = 2;             //网推侠
    const TYPE_ALL = 3;             //网推侠 + 优站通（熊掌客）
    const TYPE_KPB = 5;             //快排宝

    const TABLE = 'Customer';       //当前表名称


    /**
     * 新建快排宝客户数据
     * @param $data
     * @return int|string
     * @throws Exception
     */
    static public function addKPBCustomer($data)
    {
        $admin_id = AgentModel::getAdminIdById($data['agent_id']);
        $area = explode(',',$data['area']);
        unset($data['area']);
        $data['admin_id'] = $admin_id;
        $data['type'] = self::TYPE_KPB;
        $data['status'] = self::STATUS_NORMAL;
        $data['add_time']=time();
        $data['province']  =  $area[0];
        $data['city']  =  $area[1];
        $customer_id = self::insertKPBData($data);
        OperationLogModel::agentAddOperationLog(self::TABLE,OperationLogModel::ACTION_ADD,$customer_id,$data);
    }


    /**
     * 新增快排宝客户数据
     * @param $data
     * @return int|string
     */
    static public function insertKPBData($data)
    {
        return self::insertGetId([
            'account'       =>$data['account'],
            'password'      =>password_hash($data['password'],PASSWORD_DEFAULT),
            'company'       =>$data['company'],
            'agent_id'      =>$data['agent_id'],
            'class'         =>$data['class'],
            'linkman'       =>$data['linkman'],
            'linkphone'     =>$data['linkphone'],
            'qq'            =>$data['qq'],
            'wechat'        =>$data['wechat'],
            'detail'        =>$data['detail'],
            'admin_id'      =>$data['admin_id'],
            'type'          =>self::TYPE_KPB,
            'status'        =>self::STATUS_NORMAL,
            'add_time'      =>time(),
            'province'      =>$data['province'],
            'city'          =>$data['city']
        ]);
    }

    /**
     * 更新快排宝数据
     * @param $data
     */
    static public function updateKPBData($data)
    {
        $info = [
            'id'            =>$data['id'],
            'company'       =>$data['company'],
            'agent_id'      =>$data['agent_id'],
            'linkphone'     =>$data['linkphone'],
            'class'         =>$data['class'],
            'linkman'       =>$data['linkman'],
            'qq'            =>$data['qq'],
            'wechat'        =>$data['wechat'],
            'detail'        =>$data['detail'],
            'province'      =>$data['province'],
            'city'          =>$data['city']
        ];
        if(!empty($data['password'])){
            $info['password'] = $data['password'];
        }
        self::update($info);
    }


    /**
     *更新快排宝客户数据
     * @param $data
     */
    static public function updateKPBCustomer($data)
    {
        $area = explode(',',$data['area']);
        unset($data['area']);
        $data['province']  =  $area[0];
        $data['city']  =  $area[1];
        self::updateKPBData($data);
        OperationLogModel::agentAddOperationLog(self::TABLE,OperationLogModel::ACTION_UPDATE,$data['id'],$data);
    }

    /**
     * 通过ID获取客户状态
     * @param $id
     * @return mixed
     */
    static public function getCustomerStatusById($id)
    {
        return self::where('id',$id)->value('status');
    }

    /**
     * 变更客户状态
     * @param $id
     * @param $status
     */
    static public function updateCustomerStatusById($id,$status)
    {
        self::update(['id'=>$id,'status'=>$status]);
    }


    /**
     *  通过ID获取客户信息
     * @param $id           //客户ID
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\exception\DbException
     * @throws db\exception\DataNotFoundException
     * @throws db\exception\ModelNotFoundException
     */
    static public function getCustomerInfoById($id)
    {
        return self::field(['id','company','linkphone','linkman','account','money'])->find($id);
    }



    /**
     *更新客户状态
     * @param $id       //客户ID
     * @return int      //最新状态
     * @throws Exception
     */
    static public function editCustomerStatusById($id)
    {
        $customer_status = self::getCustomerStatusById($id);
        if(is_null($customer_status)){
            throw new Exception('查找不到当前用户！');
        }
        $status = $customer_status == 1 ? 2 : 1 ;
        self::updateCustomerStatusById($id,$status);
        OperationLogModel::agentAddOperationLog(self::TABLE,OperationLogModel::ACTION_UPDATE,$id,['status'=>$status]);
        return $status;
    }




    /**
     * 获取当前代理快排宝客户数据列表
     * @param $agent_id         //代理ID
     * @param $keyword          //搜索关键词
     * @param $page             // 分页
     * @param $limit            // 每页条数
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\exception\DbException
     * @throws db\exception\DataNotFoundException
     * @throws db\exception\ModelNotFoundException
     */
    static public function getKPBCustomerListByAgentId($agent_id,$keyword,$page,$limit)
    {
        $where = [];
        if(!empty($keyword)){
            $keywords = trim($keyword);
            $where['c.company|c.linkphone|c.linkman|c.url'] =['like',"%$keywords%"];
        }
        $start = ($page-1)*$limit;

        $customer_list = self::alias('c')
                                ->join('agent a','a.id = c.agent_id','left')
                                ->where($where)
                                ->where('c.type',self::TYPE_KPB)
                                ->where('c.agent_id = :agent_id or a.pid = :p_id ',['agent_id'=>$agent_id,'p_id'=>$agent_id])
                                ->field(['c.id','c.company','c.linkman','c.linkphone','c.money','a.company as agent_name','c.status'])
                                ->limit($start,$limit)
                                ->select();

        foreach ($customer_list as $key=>$value){
            $top = KeywordProductModel::getTopKeywordNumAndMoneyByCustomerId($value['id']);
            $customer_list[$key]['total'] = KeywordProductModel::getKeywordNumByCustomerId($value['id']);
            $customer_list[$key]['top'] = $top['top'];
            $customer_list[$key]['cost'] = $top['cost'];
        }
        return $customer_list;
    }


    /**
     * 获取条件查询总数
     * @param $agent_id     //代理ID
     * @param $keyword      //查询关键词
     * @return int|string
     */
    static public function getKPBCustomerCountByAgentId($agent_id,$keyword)
    {
        $where = [];
        if(!empty($keyword)){
            $where['c.company|c.linkphone|c.linkman|c.url'] =['like',"%$keyword%"];
        }
        return self::alias('c')
            ->join('agent a','a.id = c.agent_id','left')
            ->where($where)
            ->where('c.type',self::TYPE_KPB)
            ->where('c.agent_id = :agent_id or a.pid = :p_id ',['agent_id'=>$agent_id,'p_id'=>$agent_id])
            ->count();
    }







}