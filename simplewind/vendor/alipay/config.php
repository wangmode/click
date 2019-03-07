<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2018042602597255",

		//商户私钥
		'merchant_private_key' => "MIIEowIBAAKCAQEA2pjsqTvqmgmq8uXvu+oCtEfP1bhbbJHDOkHPCjv3QBPl6/ohUXPHx8gYPMocF2CzbEmbKX7/zyLSyupA+CCt73WEEhJ8cQxV0b13Ynz2Jix8LT1LoLifTlf4XLWlO7SMt7P2yL91HEc0cbM+SOqCHJ+KbStmDuI96nMTHot1DCQY1QPBP3fT3eRXANLmfxiqAaFhBWrGugYKvj0BhAtoF8LBy1zSvK7+XyU6eNH2/LyM4ZO9KRsqcMP1KITJpx3Gdlea+IpEoSPov94I8kmn4C0bxeMMWWnqLRhaPBbYle/o/6SpCufGCYXN9+XSbgyn8wX9bdwkSJtyfSuyYIaw9QIDAQABAoIBAQCu0Nt63+LXO2IODJrzQcrV2BQ7C8t5gPyLC3QC8D/ka8VVOV0J4bjkaX4Qp2VdwMTcleTTg7AaO/QtuLcqeCABv45WUZhZZA8BxC7EgcDseGj6WOmxZ9Rm9+00X0P8AoECrJZwpsSGT99+CmWGNJzHPUOb2LgusPnpFAw3Qjnh8UWtXTlSh2cdiKRSnhH7Nde/vW1CT0DWTb3Lcz1Wwhc9KFtC8hvIWEQxF76ntW7E1etXdrxB3bZLPTGOVIs0rqOxkZuKg5jByQFGSNGB064G9ZBcL/bXonv+XwJF8Mp0VtEgrwRkPe6sNWyYjo4kiUPrbJWfwIIfofBivASAzBIFAoGBAPO1T40p4Y4j6sEKZuLKuwUnVPbxCq9ica7AN3ZcLYQ78iuKv4Jn8vJtH48iLSiq8Up6zDGipXVYXrX9cSq1Ucr0hG2BnPkIH9oEQ5dw11K06uNVft1crTHk0mkvAG0BcitV1WPoFqLev9bXBFHPq8k6ZI0+kxCgAF6EfGXDITHHAoGBAOWfY6y6IarJVnl3HvWCSALF+63QV4mqUdPWMsoaFyfW6aqogqGZ+0EEr57sv34KuzkJPJGLey6fBNaLlCIxq/pBdLTdyxBIoh5lWlSxosmQE356iikAN90zKZqIYY5d2iccmVDaQu0p50TEZwaZaK2Uf2rq6DflVEcLiC7rDAdjAoGAOYYJUeuZxsApkAkRgeSSQkQnZOY+PGmDJdlO/gwB3l176tUkIPbCPICPW2yYtimrLIZRnkGixlDmghRhWtBTjxEqFOLsF0fYpNAu2BcVa/syGhi7Ciru4oD9PUCP3CrkNOBcrulANo9XPrGf3mOjS6sRwtkLQ3hQvf6NkkN9mU8CgYBk3kiV1sn1US6IexiBdrKVbU0qxGu/0K7TZLO47g8f9Bt/WTjRLmgd3qYbJRrVjndCDdmqgeAh0b75VCVFhBZs/5X54bhTNeTpf1JYBBRjgMPfeor4id0AcXokJSbduEKdjcWXq9lcf1zVa4Vqc7d1ENeMKxbJfxcvglRXb+8/ywKBgAYHFuyof86yAPshf6YjPcYRgbBvw67mQgUg5OIag/XHJ72mZz6KqU/iKPwAQ3loXHzDnxPP6cueaFewX4mkFHfFLxDVoGykmnEU/oFkauGKi3KPkswcJBZYB7zaOH75OSbD5pCOFlxYHqNiK8nAOCuiyHeXJWOOIrkWaItbePxh",
		
		//异步通知地址
		'notify_url' => "http://admin.gusishu.com/alipay.trade.page.pay-PHP-UTF-8/notify_url.php",
		
		//同步跳转
		'return_url' => "http://admin.gusishu.com/alipay.trade.page.pay-PHP-UTF-8/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
        //沙箱

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjNIx3CoLcxwUJAFMGiW3B2XtNgZjic12PlISwl2PnB/TYTMUAabAZlGwUNtsaAQEapdkFLdS3qDlPBMQvvbYlboZtnjQdOefOMiF7QiY50F1Mt+mlQ0FRZpqALqmLdcR54s2QqBzXEk33P9VK/bQA+zkgCCgtPZXpYo3DoTBFQiX1sPHwobKvgeIeFlPZk0UCWocKrt5splRb9J+jeQl17hnkZFQD4pUjmTJqwavnuTof+r9dcV8QpGPgzTowbwOn1AUktkiW0AiA2x4zsjZwnY8zlU6800nj4HqWjHC2DG+L04g2orSO/lBHXDNLlW9ZK+0XT/QH+2iwVUUOScJTQIDAQAB",
);