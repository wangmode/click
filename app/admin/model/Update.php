<?php
// +----------------------------------------------------------------------
// | 优站通 [ 一站到底，让排名不是梦 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.youzhantong.vip All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: WangMode <wangmode@163.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;
use think\Db;
class Update extends Model
{
    protected $app_path;
    protected $version_txt_path;
    protected $current_version;
    protected $service_url;


    /**
     * 析构函数
     */
    function  __construct() {
        $this->app_path = CMF_ROOT; // 当前项目路径
        $this->version_txt_path = $this->app_path.'app/admin/version.txt'; // 版本文件路径
        $this->current_version = file_get_contents($this->version_txt_path); // 记录版本的常量文件
        $this->service_url = url('Admin/Api/checkVersion','','',config('api_url'));
    }

    //检查是否有更新包
    public function checkVersion() {
        $url = $this->service_url."&v=".$this->current_version;
        $serviceVersion = httpRequest($url, $method="POST");
        $version = json_decode($serviceVersion,true);
        return $version;
    }


    //一键更新
    public function oneKeyUpdate(){
        //error_reporting(0);//关闭所有错误报告
        $url = $this->service_url."&v=".$this->current_version;
        $serviceVersion = httpRequest($url, $method="POST");
        $serviceVersion = json_decode($serviceVersion,true);
        if($serviceVersion['status'] == 0)return "没找到升级信息";
        $version_info = $serviceVersion['version_info'];
        clearstatcache();
        $quanxian = substr(base_convert(@fileperms($this->app_path),10,8),-4);
        if(!in_array($quanxian,array('0777','0666','0222')))
            return "网站根目录不可写,无法升级.";
        if(!is_writeable($this->version_txt_path))//版本文件
            return '版本记录文件不可写,不能升级!!!';

        // 下载文件
        //$result = $this->downloadFile($serviceVersion['down_url'],$serviceVersion['file_md5']);

        $result = $this->downloadFile($version_info['down_url']);

        if($result != 1) return $result;

        $downFileName = explode('/', $version_info['down_url']);
        $downFileName = end($downFileName);
        $folderName = str_replace(".zip","",$downFileName);  // 文件夹
        // 解压文件
        $zip = new \ZipArchive();//新建一个ZipArchive的对象
        if($zip->open($this->app_path.'backup/'.$downFileName)!=TRUE)
            return "升级压缩文件读取失败!";
        $zip->extractTo($this->app_path.'backup/');//假设解压缩到在当前路径下backup文件夹内
        $zip->close();//关闭处理的zip文件

/*        if(!file_exists($this->app_path.'backup/'.$folderName.'/www/application/admin/conf/version.php'))
            return "缺少version文件,请联系客服";*/
        if(file_exists($this->app_path.'backup/'.$folderName.'/app/database.php'))
            return "不得修改db文件,请联系客服";
        //var_dump($this->app_path.'backup/'.$folderName);exit;
        // 递归复制文件夹

        recurse_copy($this->app_path.'backup/'.$folderName,$this->app_path);

        // 升级的 sql文件
        if(count($version_info['sql_file']) > 0)
        {
            foreach($version_info['sql_file'] as $key => $val)
            {
                $sqlpath = $this->app_path.'backup/'.$folderName.'/sql/'.trim($val);
                $execute_sql = file_get_contents($sqlpath);
                //$execute_sql = iconv("gbk","utf-8//IGNORE",$execute_sql);
                $execute_sql = explode(';', $execute_sql);
                //print_r($execute_sql);
                foreach($execute_sql as $k => $v)
                    trim($v) && Db::execute($v); // 遍历执行 升级sql语句
            }
        }
        // 修改version.txt 文件
        file_put_contents($this->version_txt_path,$version_info['version']);
        // 删除下载的升级包
        delFile($this->app_path.'backup/'.$folderName,true);
        unlink($this->app_path.'backup/'.$downFileName);
        return 1;
    }

    /**
     * @param $fileUrl string 下载文件地址
     * @param $md5File string 文件MD5 加密值 用于对比下载是否完整
     * @return string 错误或成功提示
     */
    public function downloadFile($fileUrl)
    {
        $downFileName = explode('/', $fileUrl);
        $downFileName = end($downFileName);
        $saveDir = CMF_ROOT.'backup/'; // 保存目录

        if(!file_get_contents($fileUrl,0,null,0,1)){
            return "下载升级文件不存在"; //文件不存在直接退出
        }
        //下载文件
        getFile($fileUrl,$saveDir,$downFileName);

        return 1;
    }

/*    public function downloadFile($fileUrl,$md5File)
    {
        $downFileName = explode('/', $fileUrl);
        $downFileName = end($downFileName);
        $saveDir = CMF_ROOT.'backup/'; // 保存目录

        if(!file_get_contents($fileUrl,0,null,0,1)){
            return "下载升级文件不存在"; //文件存在直接退出
        }
        //下载文件
        getFile($fileUrl,$saveDir,$downFileName);

        if($md5File != md5_file($saveDir.$downFileName))
        {
            return "下载的文件有损害, 请重试!";
        }
        return 1;
    }*/

}