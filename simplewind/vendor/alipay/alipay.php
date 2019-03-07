<?php
class alipay{
    public $alipay_config = array();// 支付宝支付配置参数
    /**
     * 析构流函数
     */
    public function  __construct() {
        //parent::__construct();
        $this->alipay_config['app_id']= '2018042660059262';
        $this->alipay_config['merchant_private_key']= 'MIIEowIBAAKCAQEA2pjsqTvqmgmq8uXvu+oCtEfP1bhbbJHDOkHPCjv3QBPl6/ohUXPHx8gYPMocF2CzbEmbKX7/zyLSyupA+CCt73WEEhJ8cQxV0b13Ynz2Jix8LT1LoLifTlf4XLWlO7SMt7P2yL91HEc0cbM+SOqCHJ+KbStmDuI96nMTHot1DCQY1QPBP3fT3eRXANLmfxiqAaFhBWrGugYKvj0BhAtoF8LBy1zSvK7+XyU6eNH2/LyM4ZO9KRsqcMP1KITJpx3Gdlea+IpEoSPov94I8kmn4C0bxeMMWWnqLRhaPBbYle/o/6SpCufGCYXN9+XSbgyn8wX9bdwkSJtyfSuyYIaw9QIDAQABAoIBAQCu0Nt63+LXO2IODJrzQcrV2BQ7C8t5gPyLC3QC8D/ka8VVOV0J4bjkaX4Qp2VdwMTcleTTg7AaO/QtuLcqeCABv45WUZhZZA8BxC7EgcDseGj6WOmxZ9Rm9+00X0P8AoECrJZwpsSGT99+CmWGNJzHPUOb2LgusPnpFAw3Qjnh8UWtXTlSh2cdiKRSnhH7Nde/vW1CT0DWTb3Lcz1Wwhc9KFtC8hvIWEQxF76ntW7E1etXdrxB3bZLPTGOVIs0rqOxkZuKg5jByQFGSNGB064G9ZBcL/bXonv+XwJF8Mp0VtEgrwRkPe6sNWyYjo4kiUPrbJWfwIIfofBivASAzBIFAoGBAPO1T40p4Y4j6sEKZuLKuwUnVPbxCq9ica7AN3ZcLYQ78iuKv4Jn8vJtH48iLSiq8Up6zDGipXVYXrX9cSq1Ucr0hG2BnPkIH9oEQ5dw11K06uNVft1crTHk0mkvAG0BcitV1WPoFqLev9bXBFHPq8k6ZI0+kxCgAF6EfGXDITHHAoGBAOWfY6y6IarJVnl3HvWCSALF+63QV4mqUdPWMsoaFyfW6aqogqGZ+0EEr57sv34KuzkJPJGLey6fBNaLlCIxq/pBdLTdyxBIoh5lWlSxosmQE356iikAN90zKZqIYY5d2iccmVDaQu0p50TEZwaZaK2Uf2rq6DflVEcLiC7rDAdjAoGAOYYJUeuZxsApkAkRgeSSQkQnZOY+PGmDJdlO/gwB3l176tUkIPbCPICPW2yYtimrLIZRnkGixlDmghRhWtBTjxEqFOLsF0fYpNAu2BcVa/syGhi7Ciru4oD9PUCP3CrkNOBcrulANo9XPrGf3mOjS6sRwtkLQ3hQvf6NkkN9mU8CgYBk3kiV1sn1US6IexiBdrKVbU0qxGu/0K7TZLO47g8f9Bt/WTjRLmgd3qYbJRrVjndCDdmqgeAh0b75VCVFhBZs/5X54bhTNeTpf1JYBBRjgMPfeor4id0AcXokJSbduEKdjcWXq9lcf1zVa4Vqc7d1ENeMKxbJfxcvglRXb+8/ywKBgAYHFuyof86yAPshf6YjPcYRgbBvw67mQgUg5OIag/XHJ72mZz6KqU/iKPwAQ3loXHzDnxPP6cueaFewX4mkFHfFLxDVoGykmnEU/oFkauGKi3KPkswcJBZYB7zaOH75OSbD5pCOFlxYHqNiK8nAOCuiyHeXJWOOIrkWaItbePxh';
        $this->alipay_config['notify_url']= 'http://daili.xiongzhangke.com'.url('Recharge/notifyUrl');
        $this->alipay_config['return_url']= 'http://daili.xiongzhangke.com'.url('Recharge/returnUrl');
        $this->alipay_config['charset']= 'UTF-8';
        $this->alipay_config['sign_type']= 'RSA2';
        $this->alipay_config['gatewayUrl']= 'https://openapi.alipay.com/gateway.do';
        $this->alipay_config['alipay_public_key']= 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjNIx3CoLcxwUJAFMGiW3B2XtNgZjic12PlISwl2PnB/TYTMUAabAZlGwUNtsaAQEapdkFLdS3qDlPBMQvvbYlboZtnjQdOefOMiF7QiY50F1Mt+mlQ0FRZpqALqmLdcR54s2QqBzXEk33P9VK/bQA+zkgCCgtPZXpYo3DoTBFQiX1sPHwobKvgeIeFlPZk0UCWocKrt5splRb9J+jeQl17hnkZFQD4pUjmTJqwavnuTof+r9dcV8QpGPgzTowbwOn1AUktkiW0AiA2x4zsjZwnY8zlU6800nj4HqWjHC2DG+L04g2orSO/lBHXDNLlW9ZK+0XT/QH+2iwVUUOScJTQIDAQAB';


    }
    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $config_value    支付方式信息
     */
    function get_code($order){
        require_once("pagepay/service/AlipayTradeService.php");
        require_once("pagepay/buildermodel/AlipayTradePagePayContentBuilder.php");

        $payRequestBuilder = new AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($order['body']);
        $payRequestBuilder->setSubject($order['subject']);
        $payRequestBuilder->setTotalAmount($order['amount']);
        $payRequestBuilder->setOutTradeNo($order['order_sn']);
        $aop = new AlipayTradeService($this->alipay_config);
        $response = $aop->pagePay($payRequestBuilder, $this->alipay_config['return_url'], $this->alipay_config['notify_url']);
        return $response;
    }

    function response(){
        require_once 'pagepay/service/AlipayTradeService.php';
        $arr=$_POST;
        $alipaySevice = new AlipayTradeService($this->alipay_config);
        $alipaySevice->writeLog(var_export($arr,true));
        $result = $alipaySevice->check($arr);

        if($result) {//验证成功
            //商户订单号
            $out_trade_no=input('post.out_trade_no');

            //支付宝交易号
            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status=input('post.trade_status');

            //交易总额
            $total_amount = input('post.total_amount');
            //交易成功且结束，即不可再做任何操作。
            if($trade_status == 'TRADE_FINISHED') {
                $amount = db('recharge')->where(array('order_sn'=>$out_trade_no))->value('amount');
                if($amount!=$total_amount){
                    echo "fail";exit;
                }
                update_order_info($out_trade_no);
            }//交易成功，且可对该交易做操作，如退款等。
            else if ($trade_status == 'TRADE_SUCCESS') {
                $amount = db('recharge')->where(array('order_sn'=>$out_trade_no))->value('amount');
                if($amount!=$total_amount){
                    echo "fail";exit;
                }
                update_order_info($out_trade_no);
            }
            echo "success";	//请不要修改或删除
        }else{
            //验证失败
            echo "fail";
        }
    }

    function page_response(){
        require_once 'pagepay/service/AlipayTradeService.php';
        $arr=$_GET;
        $alipaySevice = new AlipayTradeService($this->alipay_config);
        //$alipaySevice->writeLog(var_export(input('request.'),true));
        $result = $alipaySevice->check($arr);
        if($result){
            $out_trade_no = $_GET['out_trade_no']; //商户订单号
            return array('status'=>1,'order_sn'=>$out_trade_no);//跳转至成功页面
        }else{
            return array('status'=>0,'order_sn'=>$_GET['out_trade_no']);//跳转至失败页面
        }
    }

}