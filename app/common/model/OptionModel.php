<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8
 * Time: 16:19
 */

namespace app\common\model;


use think\Model;

class OptionModel extends Model
{
    protected $connection= 'db_daili';

    /**
     * 获取配置内容
     * @param $name
     * @return mixed
     */
    static public function getOptionValueByName($name)
    {
        return self::where('option_name', $name)->value('option_value');
    }

}