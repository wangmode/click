<?php

namespace app\common\Model;

use \think\model;

class NumModel extends Model{


    private function getNum($result,$index)
    {
        $data = array_column($result,"$index");
        return $data;
    }

    /**
     * @$result  //关键词信息
     * 百度关键词价格算法
     */
    public function AlgNum($result){

        $data_index = $this->getNum($result , 'baidu_index');   //百度指数
        $data_kwc = $this->getNum($result , 'bidword_kwc');     //竞价竞争激烈程度
        $data_pcpv = $this->getNum($result , 'bidword_pcpv');   //百度PC检索量

        foreach ($result as $key => $keywords) {    //所有关键词
            $keywordx[]= $keywords['keyword'];
        }

        if (empty($data_pcpv) | empty($data_kwc)| empty($data_index)){      //如果empty = 0
            $data_pcpv[] = 0;
            $data_kwc[] = 0;
            $data_index[] = 0;
        }
        foreach ($data_index as $val) {     //如果 X 等于 0 则 A = 1
            $baidu_indexnumA = (int)bcmul($val,0.03,3);
            //$baidu_indexnumA_1 = str_replace('0.03',1,$baidu_indexnumA);
            if ($baidu_indexnumA == 0) {
                $baidu_indexnumA = 1;
            }
            $data_index_1[] = $baidu_indexnumA;
        }
        foreach ($data_kwc as $val) {  //如果 Y 等于 0 则 B = 1
            $baidu_indexnumA = (int)bcmul($val,0.3);
            //$baidu_indexnumA_1 = str_replace('0.03',1,$baidu_indexnumA);
            if ($baidu_indexnumA == 0) {
                $baidu_indexnumA = 1;
            }
            $data_kwc_1[] = $baidu_indexnumA;
        }

        foreach ($data_pcpv as $val) {  //如果 Z 等于 0 则 C = 1
            $baidu_indexnumA = (int)bcmul($val, 0.07);
            //$baidu_indexnumA_1 = str_replace('0.03',1,$baidu_indexnumA);
            if ($baidu_indexnumA == 0) {
                $baidu_indexnumA = 1;
            }
            $data_pcpv_1[] = $baidu_indexnumA;
        }

        foreach($data_pcpv_1 as $key => $value){  //如果A+B+C < 4.5 则N位最低金4.5
            if ($value + $data_kwc_1[$key] + $data_index_1[$key] < 4.5) {
                $money[$key] = 4.5;
            }else {
                $money[$key] = $value + $data_kwc_1[$key] + $data_index_1[$key];
            }
        }
         $datas= array_combine($keywordx , $money); //将关键词对应价格 放入数组

         return $datas;
    }

}