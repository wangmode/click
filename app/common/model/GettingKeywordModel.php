<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/2
 * Time: 9:07
 */

namespace app\common\model;


use think\cache\driver\Redis;
use think\Exception;
use think\Model;

class GettingKeywordModel extends Model
{


    const APIKEY = "6BF2794D60C840E8930FD4C3CDFEF325";

    const KEYWORD_TIME = 18000;

    private $redis ;

    public function __construct()
    {
        $this->redis = new Redis();
    }

    /**
     * @param $keyword
     * @return string
     */
    private function getResultKey($keyword)
    {
        return 'keyword_result_'.md5($keyword);
    }

    /**
     * @param $admin_id
     * @return string
     */
    private function getKeywordKey($admin_id)
    {
        return 'keyword_key_'.md5($admin_id);
    }


    /*参数1:请求的URL;参数2:以CURL方式设置http的请求头;参数3:要提交的数据包*/
    private function doCurlPostRequest($url,$header,$data)
    {

        $ch = curl_init();

        /*请求地址*/

        curl_setopt($ch, CURLOPT_URL, $url);

        /*以CURL方式设置http的请求头*/

        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);

        /*文件流形式*/

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        /*发送一个常规的Post请求*/

        curl_setopt($ch, CURLOPT_POST, 1);

        /*Post提交的数据包*/

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        return curl_exec($ch);

    }


    /**
     * 5188关键词挖掘接口
     * @param $keyword          //关键词
     * @param int $page         //分页页码
     * @return string
     */
    private function getKeywordWord($keyword,$page=1)
    {
        /*请求的URL*/
        $url = "http://apis.5118.com/keyword/word";
        /*要提交的数据包*/

        $data = "keyword=$keyword&page_index=$page";


        /*以CURL方式设置http的请求头*/
        $header[] = "Content-type:application/x-www-form-urlencoded";

        /*输入你要调用API的APIKEY*/

        $header[] = "Authorization:".self::APIKEY;

        /*调用CURL POST函数*/

        $result=$this->doCurlPostRequest($url,$header,$data);

        return $result;
    }


    /**
     * 结果处理
     * @param $keyword  //关键词
     * @param $page     //页码
     * @return array
     */
    private function processResult($keyword,$page)
    {
        $result = json_decode($this->getKeywordWord($keyword,$page),true);
        if($result['errcode'] != 0){
            return [];
        }
        $data = $result['data']['word'];
        return $data;
    }


    /**
     * 保存获取结果
     * @param $keyword     //关键词
     */
    private function setKeywordResult($keyword)
    {
        $data = $this->getKeywordResultData($keyword);
        $this->redis->set($this->getResultKey($keyword),$data,self::KEYWORD_TIME);
    }

    /**
     * 保存查询关键词
     * @param $admin_id
     * @param $keyword
     */
    private function setKeywordKeyByAdminId($admin_id,$keyword)
    {
        $this->redis->set($this->getKeywordKey($admin_id),$keyword,self::KEYWORD_TIME);
    }

    /**
     * 获取查询关键词
     * @param $admin_id
     * @return mixed
     */
    public function getKeywordKeyByAdminId($admin_id)
    {
        return $this->redis->get($this->getKeywordKey($admin_id));
    }


    /**
     * 获取关键词挖掘结果
     * @param $keyword      //关键词
     * @return array
     */
    private function getKeywordResultData($keyword)
    {
        $data = [];
        $page = config('keyword_num')?ceil(config('keyword_num')/1000):3;
        for ($i = 1 ;$i<=$page;$i++){
            $data = array_merge($data,$this->processResult($keyword,$i));
        }
        return $data;
    }


    /**
     * 获取保存的挖掘结果
     * @param $keyword    //关键词
     * @return mixed
     */
    private function getKeywordResult($keyword)
    {
        $result = $this->redis->get($this->getResultKey($keyword));
        if(empty($result)){
            $this->setKeywordResult($keyword);
            $result = $this->redis->get($this->getResultKey($keyword));
        }
        return $result;
    }


    /**
     * 分页获取关键词列表
     * @param $admin_id         //查询用户ID
     * @param $keyword          //关键词
     * @param $page             //页码
     * @param $limit            //每页条数
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getKeywordList($admin_id,$keyword,$page,$limit)
    {
        $result = $this->getKeywordResult($keyword);
        $this->setKeywordKeyByAdminId($admin_id,$keyword);
        $start = ($page-1)*$limit;
        $data['count'] = count($result);
        $data['list'] = array_slice($result,$start,$limit);
        foreach ($data['list']  as $key=>$val){
            $data['list'][$key]['price'] = ProductModel::getProductPriceList($val['baidu_index'], $val['bidword_kwc'],$val['bidword_pcpv']);
        }
        return $data;
    }

    /**
     * 分页获取关键词列表
     * @param $admin_id         //查询用户ID
     * @param $keyword          //关键词
     * @param $page             //页码
     * @param $limit            //每页条数
     * @return mixed
     */
    public function getKeywordListData($admin_id,$keyword,$page,$limit)
    {
        $result = $this->getKeywordResult($keyword);
        $this->setKeywordKeyByAdminId($admin_id,$keyword);
        $start = ($page-1)*$limit;
        $list  = array_slice($result,$start,$limit);
        return $list;
    }






    /**
     * 获取关键字数据
     * @param $admin_id     //用户ID
     * @return string
     * @throws Exception
     */
    public function getKeywordData($admin_id)
    {
        $keyword = $this->getKeywordKeyByAdminId($admin_id);
        if(empty($keyword)){
            throw new Exception("请先挖掘关键词！");
        }
        $data = array_column($this->getKeywordResult($keyword),'keyword');
        return implode("\n",$data);
    }

}