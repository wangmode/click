<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8
 * Time: 11:28
 */

namespace app\common\model;


use think\Exception;
use think\Model;

class KeywordProductModel extends Model
{
    const IS_DEL_NO         = 0 ; // 未删除
    const IS_DEL_YES        = 1 ; // 已删除

    const IS_TOP_NO         = 0 ; // 未达标
    const IS_TOP_YES        = 1 ; // 已达标

    const STATUS_PAUSE      = 0 ; // 暂停
    const STATUS_NORMAL     = 1 ; // 正常
    const STATUS_DISABLE    = 2 ; // 禁用

    static private $billing_days = [1=>90,2=>180,3=>360];


    /**
     * 获取客户关键词总数
     * @param $customer_id      //客户ID
     * @return int|string
     */
    static public function getKeywordNumByCustomerId($customer_id)
    {
        return self::where('customer_id',$customer_id)
                    ->where('is_del',self::IS_DEL_NO)
                    ->count();
    }

    /**
     * 获取客户关键词生效数量与金额
     * @param $customer_id      //金额
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTopKeywordNumAndMoneyByCustomerId($customer_id)
    {
        return self::where('customer_id',$customer_id)
                        ->where('is_del',self::IS_DEL_NO)
                        ->where('is_top',self::IS_TOP_YES)
                        ->where('status',self::STATUS_NORMAL)
                        ->field(['count(*) as top','ifnull(sum(money),0.00) as cost'])
                        ->find();
    }


    /**
     * 获取客户关键词列表
     * @param $customer_id      //客户ID
     * @param $keyword          //搜索关键词
     * @param $is_top           //是否达标
     * @param $status           //关键词产品状态
     * @param $page             //页码
     * @param $limit            //每页条数
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getCustomerKeywordListData($customer_id,$keyword,$is_top,$status,$page,$limit)
    {
        $where = [];
        if(!empty($keyword)){
            $keywords = trim($keyword);
            $where['k.keyword'] =['like',"%$keywords%"];
        }
        if(is_null($is_top) === false){
            $where['kp.is_top'] =$is_top;
        }
        if(is_null($status) === false){
            $where['kp.status'] =$status;
        }
        $start = ($page-1)*$limit;
        $subsql  = CustomerConsumeRecordModel::getSubsql();
        return self::alias('kp')
                ->join('keyword k','kp.keyword_id = k.id')
                ->join('product p','kp.product_id = p.id')
                ->join([$subsql=>'c'],'kp.id = c.source_id','left')
                ->field(['k.keyword','p.name as product_name','kp.billing_time','kp.money as cost','kp.status','kp.is_top','count(c.days) as day_num','kp.ranking','kp.id'])
                ->where($where)
                ->where('kp.customer_id',$customer_id)
                ->where('kp.is_del',self::IS_DEL_NO)
                ->limit($start,$limit)
                ->order('kp.id desc')
                ->group('c.source_id')
                ->select();

    }


    /**
     * 获取客户关键词总条数
     * @param $customer_id      //客户ID
     * @param $keyword          //搜索关键词
     * @param $is_top           //是否达标
     * @param $status           //关键词产品状态
     * @return int|string
     */
    static public function getCustomerKeywordCount($customer_id,$keyword,$is_top,$status)
    {
        $where = [];
        if(!empty($keyword)){
            $keywords = trim($keyword);
            $where['k.keyword'] =['like',"%$keywords%"];
        }
        if(is_null($is_top) === false){
            $where['kp.is_top'] =$is_top;
        }
        if(is_null($status) === false){
            $where['kp.status'] =$status;
        }

        return self::alias('kp')
            ->join('keyword k','kp.keyword_id = k.id')
            ->where($where)
            ->where('kp.customer_id',$customer_id)
            ->where('kp.is_del',self::IS_DEL_NO)
            ->count();
    }

    /**
     * 修改客户关键词状态
     * @param $id
     * @return int
     * @throws Exception
     */
    static public function updateKeywordStatus($id)
    {
        $keyword_info = self::checkKeywordCostDays($id);
        if(empty($keyword_info)){
            throw new Exception("套餐尚未到期！$id");
        }
        if($keyword_info['status'] == self::STATUS_NORMAL){
            $status = self::STATUS_PAUSE;
        }else if($keyword_info['status'] == self::STATUS_PAUSE){
            $status = self::STATUS_NORMAL;
        }else{
            throw new Exception("套餐已被禁用！");
        }
        return self::update([
                        'id'        =>$id,
                        'status'    =>$status
                    ])->value('status');
    }

    /**
     * 检查关键词产品状态
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function checkKeywordCostDays($id)
    {
        $subsql  = CustomerConsumeRecordModel::getSubsql($id);
        return self::alias('kp')
                ->join([$subsql=>'c'],'kp.id = c.source_id','left')
                ->where('kp.id',$id)
                ->where('kp.is_del',self::IS_DEL_NO)
                ->field(['count(*) as days','kp.billing_time','kp.status'])
                ->having("days >= kp.billing_time ")
                ->find();
    }

    /**
     * 删除关键词产品
     * @param $id
     * @return mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function delKeywordProduct($id)
    {
        $keyword_info = self::checkKeywordCostDays($id);
        if(empty($keyword_info)){
            throw new Exception("套餐尚未到期！");
        }
        if($keyword_info['status'] == self::STATUS_NORMAL){
            throw new Exception('请先暂停关键词！');
        }
        return self::update([
            'id'        =>$id,
            'is_del'    =>self::IS_DEL_YES
        ]);
    }

    /**
     * 通过ID 获取关键词状态
     * @param $id
     * @return mixed
     */
    static public function getKeywordStatus($id)
    {
        return self::where('id',$id)->value('status');
    }

    /**
     * 获取套餐天数
     * @param null $key
     * @return array|mixed
     */
    static public function getBillingDays($key=null)
    {
        $days = is_null($key)?self::$billing_days:self::$billing_days[$key];
        return $days;
    }


    /**
     *
     * @param $admin_id
     * @param $keyword
     * @param $data
     * @param $page
     * @param $limit
     * @throws Exception
     */
    static public function newKeywordProduct($admin_id,$keyword,$data,$page,$limit)
    {
        $gettingKeywrod = new GettingKeywordModel();
        $list = $gettingKeywrod->getKeywordListData($admin_id,$keyword,$page,$limit);
        foreach ($data['keyword_list'] as $key=>$val){
            $info = [];
            if(!isset($list[$val['keywrod_key']]['keywrod']) || empty($list[$val['keywrod_key']]['keywrod'])){
                throw new Exception('数据错误，请重新挖掘关键词！');
            }
            $info['agent_id']       = $admin_id;
            $info['customer_id']    = $data['customer_id'];
            $info['billing_time']   = self::getBillingDays($val['billing_time']);
            $info['keyword_id']     = KeywrodModel::addKeyword( $list[$val['keywrod_key']]['keywrod'],$data['customer_id'],$data['url']);
            if(empty($info['keyword_id'])){
                throw new Exception('关键词添加失败，请重试！');
            }
            $basics_price = KeywordPriceModel::getBasicsPrice($list[$val['keywrod_key']]['baidu_index'],$list[$val['keywrod_key']]['bidword_kwc'],$list[$val['keywrod_key']]['bidword_pcpv']);
            foreach ($val['product'] as $k=>$v){
                $info['product_id'] =  $v;
                $coefficient = ProductModel::getProductCoefficientById($v);
                $info['money'] =  bcmul($coefficient,$basics_price,2);
                self::addKeywordProduct($info);
            }
        }
    }




    /**
     * 新增关键词产品数据
     * @param $data
     * @return int|string
     */
    static public function addKeywordProduct($data)
    {
        return self::insert([
                'money'         =>$data['money']
                ,'agent_id'     =>$data['agent_id']
                ,'product_id'   =>$data['product_id']
                ,'keyword_id'   =>$data['keyword_id']
                ,'customer_id'  =>$data['customer_id']
                ,'billing_time' =>$data['billing_time']
                ,'is_del'       =>self::IS_DEL_NO
                ,'is_top'       =>self::IS_TOP_NO
                ,'status'       =>self::STATUS_NORMAL
                ,'creation_at'  =>date('Y-m-d H:i:s',time())
            ]);
    }





}