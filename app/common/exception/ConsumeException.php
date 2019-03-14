<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/13
 * Time: 10:29
 */

use app\common\Model\ConsumeErrorLogModel;

class ConsumeException extends \think\Exception
{
    function __construct($message, $type ,$user_id)
    {
        $this->setData('Consume_error_log',[
            'type'      => $type,
            'user_id'   => $user_id,
        ]);
        parent::__construct($message);
    }

    function addConsumeErrorLog()
    {
        $data = $this->getData()['Consume_error_log'];
        ConsumeErrorLogModel::addConsumeErrorLog($data['type'],$data['user_id'],$this->getMessage());
    }
}