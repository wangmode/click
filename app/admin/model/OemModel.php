<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/20
 * Time: 9:49
 */

namespace app\admin\model;

use think\Exception;
use think\Model;

class OemModel extends Model
{

    const WTX_URL       = "oem1.yzt-tools.com";
    const DAILI_URL     = "daili.yzt-tools.com";

    const WTX_WEB_ID     = 51;
    const DAILI_WEB_ID   = 21;


    /**
     *更新oem域名
     * @param $Original     //原域名
     * @param $new          //新域名
     * @param $webname
     * @param $webid
     * @return bool
     * @throws Exception
     */
    static public function updateOemDomain($Original,$new,$webname,$webid)
    {
        if(strcmp($Original,$new) != 0 && !empty($new)){
            self::delDomain($Original,$webname,$webid);
            self::addDomain($new,$webname,$webid);
        }elseif(!empty($Original) && empty($new)){
            self::delDomain($Original,$webname,$webid);
        }
        return true;
    }

    /**
     *删除原域名
     * @param $Original     //原域名
     * @param $webname
     * @param $webid
     * @throws Exception
     */
    static public function delDomain($Original,$webname,$webid)
    {
        $bt = new BtApi();
        $Original_array = explode(',',$Original);
        foreach ($Original_array as $key=>$value){
            $response = $bt->DelDomain(preg_replace('/^(http:\/\/|https:\/\/)/','',$value),$webname,$webid);
            if($response['status'] === false){
                throw new Exception($response['msg']);
            }
        }
    }

    /**
     * 添加新域名
     * @param $new          //新域名
     * @param $webname
     * @param $webid
     * @throws Exception
     */
    static public function addDomain($new,$webname,$webid){
        $bt = new BtApi();
        $new_array = explode(',',$new);
        foreach ($new_array as $key=>$value){
            $response = $bt->AddDomain(preg_replace('/^(http:\/\/|https:\/\/)/','',$value),$webname,$webid);
            if($response['status'] === false){
                throw new Exception($response['msg']);
            }
        }
    }

}