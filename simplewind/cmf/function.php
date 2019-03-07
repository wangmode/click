<?php
use cmf\lib\Oss;
use \think\db;
/**
 * CURL请求
 * @param $url string 请求url地址
 * @param $method string 请求方法 get post
 * @param mixed $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method="GET", $postfields = null, $headers = array(), $debug = false)
{
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
        curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    }
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
    //return array($http_code, $response,$requestinfo);
}

/*
 * 获取文件夹下文件
 * @param $path string 文件夹
 * */
function get_tem($dir) {
    $files = array();
    if(@$handle = opendir($dir)) { //注意这里要加一个@，不然会有warning错误提示：）
        while(($file = readdir($handle)) !== false) {
            if($file != ".." && $file != ".") { //排除根目录；
                if(is_dir($dir."/".$file)) { //如果是子文件夹，就进行递归
                    $files[]=$file;
                }
            }
        }
        closedir($handle);
        return $files;
    }
}

/*
 * 获取文件夹下文件
 * @param $path string 文件夹
 * */
function get_tem_code($dir) {
    $files = array();
    if(@$handle = opendir($dir)) { //注意这里要加一个@，不然会有warning错误提示：）
        while(($file = readdir($handle)) !== false) {
            if($file != ".." && $file != ".") { //排除根目录；
                if(is_dir($dir."/".$file)) { //如果是子文件夹，就进行递归
                    $son_file = get_tem_code($dir.$file);
                    if($son_file)$files[$file] = $son_file;
                } else { //不然就将文件的名字存入数组；
                    $finfo = finfo_open(FILEINFO_MIME); // 返回 mime 类型
                    $filename = $dir."/".$file;
                    $mime = explode(';',finfo_file($finfo, $filename));
                    if($mime[0] == 'text/html')
                        $files[] = $file;
                }
            }
        }
        closedir($handle);
        return $files;
    }
}


/*
 * 下载远程文件
 * @param $url string 请求url地址
 * @param $save_dir string 保存路径
 * @param $filename string 文件名称
 * */
function getFile($url, $save_dir = '', $filename = '', $type = 0)
{
    if (trim($url) == '') {
        return false;
    }
    if (trim($save_dir) == '') {
        $save_dir = './';
    }
    if (0 !== strrpos($save_dir, '/')) {
        $save_dir .= '/';
    }
    //创建保存目录
    if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
        return false;
    }
    //获取远程文件所采用的方法
    if ($type) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);
    } else {
        ob_start();
        readfile($url);
        $content = ob_get_contents();
        ob_end_clean();
    }
    //echo $content;
    $size = strlen($content);
    //文件大小
    $fp2 = @fopen($save_dir . $filename, 'a');
    fwrite($fp2, $content);
    fclose($fp2);
    unset($content, $url);
    return array(
        'file_name' => $filename,
        'save_path' => $save_dir . $filename,
        'file_size' => $size
    );
}





/*
 * 获取汉字首字母
 *@param $str string 中文字符串
 * */
function getFirstCharter($str){
    if(empty($str)){return '';}
    $fchar=ord($str{0});
    if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
    $s1=iconv('UTF-8','gb2312',$str);
    $s2=iconv('gb2312','UTF-8',$s1);
    $s=$s2==$str?$s1:$str;
    $asc=ord($s{0})*256+ord($s{1})-65536;
    if($asc>=-20319&&$asc<=-20284) return 'A';
    if($asc>=-20283&&$asc<=-19776) return 'B';
    if($asc>=-19775&&$asc<=-19219) return 'C';
    if($asc>=-19218&&$asc<=-18711) return 'D';
    if($asc>=-18710&&$asc<=-18527) return 'E';
    if($asc>=-18526&&$asc<=-18240) return 'F';
    if($asc>=-18239&&$asc<=-17923) return 'G';
    if($asc>=-17922&&$asc<=-17418) return 'H';
    if($asc>=-17417&&$asc<=-16475) return 'J';
    if($asc>=-16474&&$asc<=-16213) return 'K';
    if($asc>=-16212&&$asc<=-15641) return 'L';
    if($asc>=-15640&&$asc<=-15166) return 'M';
    if($asc>=-15165&&$asc<=-14923) return 'N';
    if($asc>=-14922&&$asc<=-14915) return 'O';
    if($asc>=-14914&&$asc<=-14631) return 'P';
    if($asc>=-14630&&$asc<=-14150) return 'Q';
    if($asc>=-14149&&$asc<=-14091) return 'R';
    if($asc>=-14090&&$asc<=-13319) return 'S';
    if($asc>=-13318&&$asc<=-12839) return 'T';
    if($asc>=-12838&&$asc<=-12557) return 'W';
    if($asc>=-12556&&$asc<=-11848) return 'X';
    if($asc>=-11847&&$asc<=-11056) return 'Y';
    if($asc>=-11055&&$asc<=-10247) return 'Z';
    return 'A';
}


/**
 * 自定义函数递归的复制带有多级子目录的目录
 * 递归复制文件夹
 * @param $src string 原目录
 * @param $dst string 复制到的目录
 */
function recurse_copy($src, $dst)
{
    $now = time();
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== $file = readdir($dir)) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            }
            else {
                if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
                    if (!is_writeable($dst . DIRECTORY_SEPARATOR . $file)) {
                        exit($dst . DIRECTORY_SEPARATOR . $file . '不可写');
                    }
                    @unlink($dst . DIRECTORY_SEPARATOR . $file);
                }
                if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
                    @unlink($dst . DIRECTORY_SEPARATOR . $file);
                }
                $copyrt = copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                if (!$copyrt) {
                    echo 'copy ' . $dst . DIRECTORY_SEPARATOR . $file . ' failed<br>';
                }
            }
        }
    }
    closedir($dir);
}


/*
 * 递归删除文件夹
 * @param $path目录
 * @param $delDir bool 是否删除文件夹
 */
function delFile($path,$delDir = FALSE) {
    if(!is_dir($path))
        return FALSE;
    $handle = @opendir($path);
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir) return rmdir($path);
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
}

/*
 * 获取固定网址
 * @param $host string 网址
 */

function get_site_url($host){
    $host = explode('.',$host);
    $count = count($host);
    if($count==2){
        $url = $host[0].'_'.$host[1];
    }else{
        $url = $host[1].'_'.$host[2];
    }
    return $url;
}


/*
 * 获取随机字符串
 * @param $length int 长度
* @param $char string 字符串
 */
function str_rand($length = 18, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    if(!is_int($length) || $length < 0) {
        return false;
    }
    $string = '';
    for($i = $length; $i > 0; $i--) {
        $string .= $char[mt_rand(0, strlen($char) - 1)];
    }
    return $string;
}

/*
 * base64图片
 * @param $image_file string 图片地址
 */

function base64EncodeImage ($image_file) {
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}


/*function get_img_url($path){
    $url = config('siteUrl');
    return $url.'/'.$path;
}*/


/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
    $string = strip_tags($string);
    if(mb_strlen($string,'utf-8')>$length){
        $str = mb_substr($string, $start, $length,'utf-8');
        //return $str.'...';
        return $str;
    }else{
        return $string;
    }
}

/*
 * 获取图片地址
 * @param $object string 数据库存储文件地址
 * @param $type int 1缩放2水印3原图
 * @param $w int 宽度
 * @param $h int 高度
 * */
function get_img_url($object,$type=3,$w=0,$h=0){
    $url = config('siteUrl');
    $oss = new Oss();
    if($oss->doesObjectExist($object)!=1){
        //480*320
        $object = 'default.jpg';
    }
    //缩放
    if($type==1){
        $new_url = $url.'/'.$object.'?x-oss-process=image/resize,w_'.$w.',h_'.$h.'/auto-orient,1/quality,q_90/format,jpg';
    }elseif($type==2){//水印
        $water_info = cmf_get_option('water_info');
        //水印开关，1开启0关闭
        if($water_info['switch'] == 1){
            //水印位置
            $position_array = [1=>'nw',2=>'north',3=>'ne',4=>'west',5=>'center',6=>'east',7=>'sw',8=>'south',9=>'se'];
            if($water_info['position']==0){
                $position = array_rand($position_array);
            }else{
                $position = $position_array[$water_info['position']];
            }
            if($water_info['type']==2){//文字水印
                $text = base64_encode($water_info['txt']);
                $new_url = $url.'/'.$object.'?x-oss-process=image/resize,w_'.$w.',h_'.$h.'/auto-orient,1/quality,q_90/format,jpg/watermark,type_d3F5LXplbmhlaQ,size_30,text_'.$text.',color_FFFFFF,t_90,g_'.$position.',x_10,y_10';
            }else{//图片水印
                $water_img = 'ZGVmYXVsdC8yMDE4MTAxNi8wMDJiYTk0MDhjYTkwMDkyMGYxOTllMmRjOWM4MGI2Mi5wbmc=';
                $new_url = $url.'/'.$object.'?x-oss-process=image/resize,w_'.$w.',h_'.$h.'/auto-orient,1/quality,q_90/format,jpg/watermark,image_'.$water_img.',t_90,g_'.$position.',x_10,y_10';
            }
        }else{
            $new_url = $url.'/'.$object.'?x-oss-process=image/resize,w_'.$w.',h_'.$h.'/auto-orient,1/quality,q_90/format,jpg';
        }

    }else{//原图
        $new_url = $url.'/'.$object;
    }
    return $new_url;
}


/*
 * 账户变动记录
 *@param $user_id    用户id
 *@param $money  变动金额$order
 *@param $desc 变动描述
 *@param $order_id 订单id
 *@param $order_sn 订单编号
 * */
function accountLog($agent_id, $money = 0, $desc = '',$order_id = 0 ,$order_sn = '')
{
    $account_log = array(
        'agent_id' => $agent_id,
        'money' => $money,
        'desc' => $desc,
        'order_id' => $order_id,
        'order_sn' => $order_sn,
        'change_time' => time()
    );
    $update = Db::table('yzt_agent')
        ->where('id', $agent_id)
        ->update([
            'money' => Db::raw('money+' . $money),
        ]);
    if ($update > -1) {
        Db::name('account_log')->insert($account_log);
        return true;
    } else {
        return false;
    }
}

/*
 *
 * */
function yzt_compare_password($password, $passwordInDb)
{
    return  md5($password) == $passwordInDb;
}

/*
 * 支付完成，修改订单信息
 *@param    $order_sn订单号
 * */
function update_order_info($order_sn){
    $data['pay_status'] = 1;
    $data['pay_time'] = time();
    $order = Db::name('recharge')->where(['order_sn' => $order_sn, 'pay_status' => 0])->find();
    if(!$order)return false;
    //充值金额
    $money = $order['amount'];
    //更新订单信息
    Db::name('recharge')->where(array('order_sn'=>$order_sn))->update(array('pay_status'=>1,'pay_time'=>time()));
    //记录账户变动
    accountLog($order['agent_id'],$money,'代理商充值',$order['id'],$order['order_sn']);
}


function url_is_exists($url){
    //去除协议
    if(strstr($url,'http')){
        $url_info = explode('//',$url);
        $url = $url_info[1];
    }
    $url_array = explode('.',$url);
    if(count($url_array)==2){
        $count =Db::name('customer')->where('format_url',['eq',$url],['eq','www.'.$url],'or')->count();
    }else{
        if(strstr($url,'www.')){
            $url1 = str_replace('www.','',$url);
            $count = Db::name('customer')->where('format_url',['eq',$url],['eq',$url1],'or')->count();
        }else{
            $count = Db::name('customer')->where('format_url',$url)->count();
        }
    }
    return $count;
}

function get_product_list($level,$type){
    $where['level'] = $level;
    $where['type'] = $type;
    $product_list = Db::name('product')->where($where)->where('status',1)->select()->toArray();
    return $product_list;
}



