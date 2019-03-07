<?php

namespace app\admin\model;

use think\Model;

use think\Db;
use OSS\OssClient;
use OSS\Core\OssException;

import('aliyun-oss.autoload', EXTEND_PATH);
class Oss extends Model
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

    //获取文件地址
    public function get_url($object)
    {
        if(self::$siteUrl ){
            return "http://" .self::$siteUrl.$object;
        }else{
            return "http://" .self::$bucket . "." . self::$endpoint.$object;
        }
    }




}
?>