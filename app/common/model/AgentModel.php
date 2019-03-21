<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/13
 * Time: 16:02
 */

namespace app\common\model;

use app\common\Exception\ConsumeException;
use think\Model;

class AgentModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];

    const LEVEL_ONE = 1;
    const LEVEL_TWO = 2;

    protected $connection= 'db_daili';


    /**
     * 获取代理等级与余额
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getAgentMoneyById($id)
    {
        return self::where('id',$id)->field(['level','money'])->find();
    }


    /**
     * 代理扣费更新余额
     * @param $agent_id     //代理ID
     * @param $cost         //扣费
     * @return mixed
     * @throws ConsumeException
     * @throws \think\Exception
     */
    static public function agentPayment($agent_id,$cost)
    {
        $update_num = self::where('id',$agent_id)->where('money','>=',$cost)->setDec('money',$cost);
        if($update_num != 1){
            throw new ConsumeException('当前代理余额更新失败！',ConsumeErrorLogModel::TYPE_AGENT,$agent_id);
        }
        return self::where('id',$agent_id)->value('money');
    }

}