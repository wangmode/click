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
namespace cmf\lib;
use OSS\OssClient;
use OSS\Core\OssException;

import('aliyun-oss.autoload', EXTEND_PATH);
class Oss
{
    static private $initConfigFlag = false;
    static private $accessKeyId = '';
    static private $accessKeySecret = '';
    static private $endpoint = '';
    static private $bucket = '';
    static private $siteUrl = '';

    static private $ossClient = null;

    public function __construct()
    {
        self::initConfig();
    }

    static private function initConfig()
    {
        if (self::$initConfigFlag) {
            return;
        }

        self::$accessKeyId     = config('AccessKeyId') ?: '';
        self::$accessKeySecret = config('AccessKeySecret') ?: '';
        self::$endpoint        = config('EndPoint') ?: '';
        self::$bucket          = config('bucket') ?: '';
        self::$siteUrl          = config('siteUrl') ?: '';
        self::$initConfigFlag  = true;
    }
    static private function getOssClient()
    {
        if (!self::$ossClient) {
            self::initConfig();
            try {
                self::$ossClient = new OssClient(self::$accessKeyId, self::$accessKeySecret, self::$endpoint, false);
            } catch (OssException $e) {
                self::$errorMsg = "创建oss对象失败，".$e->getMessage();
                return null;
            }
        }
        return self::$ossClient;
    }

    //上传文件
    public function upload_file($filePath,$object )
    {
        $ossClient = self::getOssClient();
        if (!$ossClient) {
            return false;
        }
        if (is_null($object)) {
            $object = $filePath;
        }

        try {
            $ossClient = new OssClient(self::$accessKeyId, self::$accessKeySecret, self::$endpoint, false);
            $ossClient->uploadFile(self::$bucket, $object, $filePath);
        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }
        return $this->get_url($object);
    }

    //上传文件
    public function doesObjectExist($object)
    {
        try{
            $ossClient = new OssClient(self::$accessKeyId, self::$accessKeySecret, self::$endpoint, false);
            $exist = $ossClient->doesObjectExist(self::$bucket, $object);
        } catch(OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }
        //print(__FUNCTION__ . ": OK" . "\n");
        return $exist;
    }

    //获取文件地址
    public function get_url($object)
    {
        if(self::$siteUrl ){
            return "http://" .self::$siteUrl.$object;
        }else{
            return "http://" .self::$bucket . "." . self::$endpoint.$object;
        }
    }
    /*
     *获取添加水印图片
     * @param string $object 原图片
     * @$water_img string $water_img 水印图片
     * */
    public function watermark($object,$water_img)
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $water_img,
            OssClient::OSS_PROCESS => "image/watermark,text_SGVsbG8g5Zu-54mH5pyN5YqhIQ");
        $ossClient = self::getOssClient();
        if (!$ossClient) {
            return false;
        }
        $ossClient->getObject(self::$bucket, $object, $options);
    }
}