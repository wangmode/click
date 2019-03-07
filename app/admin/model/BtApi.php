<?php

namespace app\admin\model;

use think\Model;

use think\Db;

class BtApi extends Model{

    private static $bt_key = "Z2Qj3mghhMe6IBgSS9rTHiHeQjb8AXnF";  //接口密钥
    private static $bt_panel = "http://yzt.youzhantong.cc:8888";	   //面板地址
    private static $initConfigFlag = false;

    //如果希望多台面板，可以在实例化对象时，将面板地址与密钥传入
    public function __construct(){
        self::initConfig();
    }

    static private function initConfig()
    {
        if (self::$initConfigFlag) {
            return;
        }
        self::$bt_key     = config('BtKey') ?: '';
        self::$bt_panel = config('BtPanel') ?: '';
        self::$initConfigFlag  = true;
    }

    //示例取面板日志
    public function GetLogs(){
        //拼接URL地址
        $url = self::$bt_panel.'/data?action=getData';

        //准备POST数据
        $p_data = $this->GetKeyData();		//取签名
        $p_data['table'] = 'logs';
        $p_data['limit'] = 10;
        $p_data['tojs'] = 'test';

        //请求面板接口
        $result = $this->HttpPostCookie($url,$p_data);

        //解析JSON数据
        $data = json_decode($result,true);
        return $data;
    }

    //获取系统基础统计
    public function GetSystemTotal(){
        //拼接URL地址
        $url = self::$bt_panel.'/system?action=GetSystemTotal';

        //准备POST数据
        $p_data = $this->GetKeyData();		//取签名
//
        //请求面板接口
        $result = $this->HttpPostCookie($url,$p_data);

        //解析JSON数据
        $data = json_decode($result,true);
        return $data;
    }

    //获取磁盘分区信息
    public function GetDiskInfo(){
        //拼接URL地址
        $url = self::$bt_panel.'/system?action=GetDiskInfo';

        //准备POST数据
        $p_data = $this->GetKeyData();		//取签名
        //请求面板接口
        $result = $this->HttpPostCookie($url,$p_data);

        //解析JSON数据
        $data = json_decode($result,true);
        return $data;
    }

    //获取实时状态信息(CPU、内存、网络、负载)
    public function GetNetWork(){
        //拼接URL地址
        $url = self::$bt_panel.'/system?action=GetNetWork';

        //准备POST数据
        $p_data = $this->GetKeyData();		//取签名
        //请求面板接口
        $result = $this->HttpPostCookie($url,$p_data);

        //解析JSON数据
        $data = json_decode($result,true);
        return $data;
    }


    //检查是否有安装任务
    public function GetTaskCount(){
        //拼接URL地址
        $url = self::$bt_panel.'/ajax?action=GetTaskCount';

        //准备POST数据
        $p_data = $this->GetKeyData();		//取签名
        //请求面板接口
        $result = $this->HttpPostCookie($url,$p_data);

        //解析JSON数据
        $data = json_decode($result,true);
        return $data;
    }

    //获取网站列表
    public function GetWebList($page){
        //拼接URL地址
        $url = self::$bt_panel.'/data?action=getData&table=sites';

        //准备POST数据
        $p_data = $this->GetKeyData();		//取签名
        $p_data['p'] = $page;
        $p_data['limit'] = 15;
//        $p_data['type'] = -1;
        $p_data['order'] = "id desc";
        $p_data['tojs'] = "get_site_list";
//        $p_data['search'] ='目录';
        //请求面板接口
        $result = $this->HttpPostCookie($url,$p_data);

        //解析JSON数据
        $data = json_decode($result,true);
        return $data;
    }

    //获取域名列表
    public function GetDomainList($webId){
        //拼接URL地址
        $url = self::$bt_panel.'/data?action=getData&table=domain';

        //准备POST数据
        $p_data = $this->GetKeyData();		//取签名
        $p_data['search'] = $webId;
        $p_data['list'] = 'true';
        //请求面板接口
        $result = $this->HttpPostCookie($url,$p_data);

        //解析JSON数据
        $data = json_decode($result,true);
        return $data;
    }

    //添加域名
    public function AddDomain($domain,$webname,$webId){
        //拼接URL地址
        $url = self::$bt_panel.'/site?action=AddDomain';

        //准备POST数据
        $p_data = $this->GetKeyData();		//取签名
        $p_data['id'] = $webId;
        $p_data['webname'] = $webname;
        $p_data['domain'] = $domain;
        //请求面板接口
        $result = $this->HttpPostCookie($url,$p_data);

        //解析JSON数据
        $data = json_decode($result,true);
        return $data;
    }

    //删除域名
    public function DelDomain($domain,$webname,$webId,$port = 80){
        //拼接URL地址
        $url = self::$bt_panel.'/site?action=DelDomain';

        //准备POST数据
        $p_data = $this->GetKeyData();		//取签名
        $p_data['id'] = $webId;
        $p_data['webname'] = $webname;
        $p_data['domain'] = $domain;
        $p_data['port'] = $port;
        //请求面板接口
        $result = $this->HttpPostCookie($url,$p_data);

        //解析JSON数据
        $data = json_decode($result,true);
        return $data;
    }




    /**
     * 构造带有签名的关联数组
     */
    private function GetKeyData(){
        $now_time = time();
        $p_data = array(
            'request_token'	=>	md5($now_time.''.md5(self::$bt_key)),
            'request_time'	=>	$now_time
        );
        return $p_data;
    }


    /**
     * 发起POST请求
     * @param String $url 目标网填，带http://
     * @param Array|String $data 欲提交的数据
     * @return string
     */
    private function HttpPostCookie($url, $data,$timeout = 60)
    {
        //定义cookie保存位置
        $cookie_file='./'.md5(self::$bt_panel).'.cookie';
        if(!file_exists($cookie_file)){
            $fp = fopen($cookie_file,'w+');
            fclose($fp);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }



}