<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8
 * Time: 11:28
 */

namespace app\common\model;


use ConsumeException;
use think\Exception;
use think\exception\PDOException;
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

    const TABLE             = 'keyword_product';

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

        $keyword_list =  self::alias('kp')
                ->join('keyword k','kp.keyword_id = k.id')
                ->join('product p','kp.product_id = p.id')
                ->field(['k.keyword','p.name as product_name','kp.billing_time','kp.money as cost','kp.status','kp.is_top','kp.ranking','kp.id'])
                ->where($where)
                ->where('kp.customer_id',$customer_id)
                ->where('kp.is_del',self::IS_DEL_NO)
                ->limit($start,$limit)
                ->order('kp.id desc')
                ->select();

        foreach ($keyword_list as $key=>$value){
            $keyword_list[$key]['days']= CustomerConsumeRecordModel::getCustomerNum($value['id']);
        }
        return $keyword_list;
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
            ->join('product p','kp.product_id = p.id')
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
        $keyword_info= self::getKeywordInfo($id);
        if($keyword_info['status'] == self::STATUS_NORMAL){
            $status = self::STATUS_PAUSE;
            $keyword_bool = self::checkKeywordCostDays($id);
            if(empty($keyword_bool)){
                throw new Exception("套餐尚未到期！");
            }
        }else if($keyword_info['status'] == self::STATUS_PAUSE){
            $agent = AgentModel::getAgentMoneyById($keyword_info['agent_id']);
            $cost =  AgentConsumeRecordModel::getAgentCost($agent['level']);
            if(bccomp($cost,$agent['money'],2) == 1){
                throw new Exception("您的余额不足，请充值！");
            }
            $status = self::STATUS_NORMAL;
        }else{
            throw new Exception("套餐已被禁用！");
        }
        $status = self::update([
                        'id'        =>$id,
                        'status'    =>$status
                    ])->value('status');
        OperationLogModel::agentAddOperationLog(self::TABLE,OperationLogModel::ACTION_UPDATE,$id,['status'=>$status]);
        return $status;
    }


    /**
     * 变更关键词首页状态
     * @param $id
     * @return KeywordProductModel
     */
    public function updateKeywordProductIsTop($id,$ranking)
    {
        return self::where('id',$id)->where('status',self::STATUS_NORMAL)->update(['is_top'=>self::IS_TOP_YES,'ranking'=>$ranking]);
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
        $del = self::update([
                'id'        =>$id,
                'is_del'    =>self::IS_DEL_YES
            ]);
        OperationLogModel::agentAddOperationLog(self::TABLE,OperationLogModel::ACTION_DEL,$id,['is_del'=>self::IS_DEL_YES]);
        return $del;
    }

    static public function disableKeywordProductByAgentId($agent_id)
    {
        return self::where('agent_id',$agent_id)->update(['status'=>self::STATUS_DISABLE]);
    }

    /**
     * 通过ID 获取关键词状态
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getKeywordInfo($id)
    {
        return self::where('id',$id)->where('is_del',self::IS_DEL_NO)->where('is_top',self::IS_TOP_YES)->field(['status','money','agent_id','customer_id'])->find();
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
    static public function newKeywordProduct($admin_id,$keyword,$data)
    {
        $gettingKeywrod = new GettingKeywordModel();
        $list = $gettingKeywrod->getKeywordInfo($admin_id,$keyword);
        $info = [];
        if(!isset($list['key_id']) || empty($list['key_id'])){
            throw new Exception('数据错误，请重新挖掘关键词！');
        }
        $info['agent_id']       = $admin_id;
        $info['customer_id']    = $data['customer_id'];
        $info['billing_time']   = self::getBillingDays($data['setmeal']);
        $info['keyword_id']     = KeywordModel::addKeyword( $list['keyword'],$data['customer_id'],$data['url']);
        if(empty($info['keyword_id'])){
            throw new Exception('关键词添加失败，请重试！');
        }
        $basics_price = KeywordPriceModel::getBasicsPrice($list['baidu_index'],$list['bidword_kwc'],$list['bidword_pcpv']);

        foreach ($data['price'] as $k=>$v){
            $info['product_id'] =  $v;
            $coefficient = ProductModel::getProductCoefficientById($v);
            $info['money'] =  bcmul($coefficient,$basics_price,2);
            $keywrodProduct_id = self::addKeywordProduct($info);
            OperationLogModel::agentAddOperationLog(self::TABLE,OperationLogModel::ACTION_ADD,$keywrodProduct_id ,$info);
        }
    }


    /**
     * 获取当前产品套餐信息并变更首页状态
     * @param $id
     * @param $ranking
     * @return array|false|\PDOStatement|string|Model
     * @throws ConsumeException
     */
    static public function changeKeywordProductIsTop($id,$ranking)
    {
        try{
            $keywrod_info = self::getKeywordInfo($id);
            self::updateKeywordProductIsTop($id,$ranking);
            if(empty($keywrod_info)){
                throw new Exception('获取不到当前产品信息！');
            }
            return $keywrod_info;
        }catch (Exception $exception){
            throw new ConsumeException($exception->getMessage(),ConsumeErrorLogModel::TYPE_PRODUCT,$id);
        }
    }


    /**
     * 添加关键词套餐扣费记录
     * @param $id           //关键词产品套餐ID
     * @param $ranking      //关键词产品套餐当前名次
     * @throws PDOException
     */
    static public function keywordProductPayment($id,$ranking)
    {
        try{
            self::startTrans();
            $keywrod_info = self::changeKeywordProductIsTop($id,$ranking);
            AgentConsumeRecordModel::agentPaymentLog($keywrod_info['agent_id'],$id);
            CustomerConsumeRecordModel::customerPaymentLog($keywrod_info['customer_id'],$keywrod_info['money'],$id);
            self::commit();
        }catch (ConsumeException $exception){
            self::rollback();
            $exception->addConsumeErrorLog();
        }
    }




    /**
     * 新增关键词产品数据
     * @param $data
     * @return int|string
     */
    static public function addKeywordProduct($data)
    {
        return self::insertGetId([
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