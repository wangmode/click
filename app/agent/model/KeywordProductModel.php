<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/9 0009
 * Time: 14:56
 */
namespace app\agent\model;

use think\Db;
use think\Model;

class KeywordProductModel extends Model
{
    /**
     * 查询代理商下所有的关键词数量
     * @param $agentId
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTotal($agentId)
    {
        $data = $this->where(['agent_id'=>$agentId,'is_del'=>'0'])->select();
        $total = count($data);
        return $total;
    }

    //查询代理商下推送首页的关键词数量
    public function getOnline($agentId)
    {
        $this->where(['agent_id'=>$agentId,'is_top'=>'1'])->select();
    }


}