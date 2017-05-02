<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Db;
/*
 * 打印数组
 */
function p($array=[],$is_exit = 0){
    if(is_array($array) || is_object($array)) {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }else{
        echo $array."\n";
    }

    if($is_exit){
        exit;
    }
}
// 应用公共文件
/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author liuzm
 */
function is_login(){
    $user = session('user_auth');
    if ( empty($user) ) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}

// 生成四位数验证码
function get_verify(){
	$obj    = new \think\Validate();
	$rules['codeSet']  = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
	$rules['fontSize'] = '25';
	$rules['imageH']   = '30';
	$rules['imageW']   = '100';
	$rules['length']   = 5;
	$rules['reset']    = true;
	// $message = ['number'];
	$jiashu = $obj->make($rules,$message);
	return $jiashu;
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 * @author liuzm
 */
function data_auth_sign($data) {
    //数据类型检测
    if(!is_array($data)){
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 * @author liuzm
 */
function hxfy_encrypt($data, $key = '', $expire = 0) {
    $key  = md5(empty($key) ? config('hxfy_auth_key') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time():0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是hxfy_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 * @author liuzm
 */
function hxfy_decrypt($data, $key = ''){
    $key    = md5(empty($key) ? config('hxfy_auth_key') : $key);
    $data   = str_replace(array('-','_'),array('+','/'),$data);
    $mod4   = strlen($data) % 4;
    if ($mod4) {
       $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data,0,10);
    $data   = substr($data,10);

    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x      = 0;
    $len    = strlen($data);
    $l      = strlen($key);
    $char   = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 写日志
 * @param  string  $text          日志内容
 * @param  string  $log_name      日志文件名称
 */
function wlog($text, $log_name){
    file_put_contents(HJB_LOG_PATH.$log_name.'.'.date('Ymd').'.log',$text."\r\n\r\n",FILE_APPEND);
}

/**
 * 发送HTTP请求方法
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function http_curl($url, $params, $method = 'GET', $header = array(), $multi = false)
{
    $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
    );

    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            $opts[CURLOPT_URL] = $url;
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }

    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error) throw new Exception('请求发生错误：' . $error);
    return  $data;
}

// 根据卡密获取卡信息
function get_coupon_info($pass = ''){
    $coupon_arr = [];
    // 判断密码长度
    $passlen = strlen($pass);
    if($passlen == 20){
        $password   = encrypt_coupon($pass);
        $map['master_cid'] = $password->cid;
        $map['password']   = $password->password;
        $coupon_arr = Db::name('CouponInfo')->where($map)->find();
    }elseif($passlen == 16){
        $pass_array = Db::name('CouponInfo2')->select();
        if($pass_array){
            foreach ($pass_array as $key => $val) {
                $coupon_pass = hxfy_decrypt($val['coupon_pass'],$val['coupon_token']);
                if($coupon_pass == $pass){
                    $coupon_arr['cid'] = $val['coupon_id'];
                    $coupon_arr['tid'] = $val['coupon_type_id'];
                    $coupon_arr['mid'] = $val['coupon_merchant_id'];
                    $coupon_arr['pid'] = $val['coupon_product_id'];
                    $coupon_arr['number'] = $val['coupon_num'];
                    $coupon_arr['password'] = $val['coupon_pass'];
                    $coupon_arr['status'] = $val['coupon_status'];
                    $coupon_arr['create_time'] = $val['coupon_create_time'];
                    $coupon_arr['start_time'] = $val['coupon_start_time'];
                    $coupon_arr['end_time'] = $val['coupon_end_time'];
                    $coupon_arr['merchant_name'] = $val['coupon_merchant_name'];
                    $coupon_arr['product_name'] = $val['coupon_product_name'];
                    $coupon_arr['type_name'] = $val['coupon_type_name'];
                    $coupon_arr['channel_id'] = $val['channel_id'];
                    break;
                }
            }
        }else{
            return false;
        }
    }
    if(!empty($coupon_arr)){
        return $coupon_arr;
    }else{
        return false;
    }
}

// 加密电子卡
function encrypt_coupon($pass = ''){
    // 调取加密接口
    $url = config('yquan_api_url').'encrypt_password';
    $send_data['password'] = $pass;
    $send_res = http_curl($url,$send_data,'post');
    $result   = json_decode($send_res);
    if($result->code == 0){
        return $result->data;
    }else{
        return '';
    }
}

// 解密电子卡
function decrypt_coupon($pass = ''){
    // 调取解密接口
    $url = config('yquan_api_url').'decrypt_password';
    $send_data['password'] = $pass;
    $send_res = http_curl($url,$send_data,'post');
    $result   = json_decode($send_res);
    if($result->code == 0){
        return $result->data;
    }else{
        return '';
    }
}

// 把券码写入验码库
function add_coupon_to_verify($cid = 0){
    $data = Db::name('CouponInfo')->where('cid',$cid)->field('cid,mid,password')->find();
    $verify['cid']           = $data['cid'];
    $verify['password']      = $data['password'];
    $res = Db::name('CouponVerify')->insertGetId($verify);
    return $res;
}

/*
 * 对象数组转关联数组
 */
function object_to_array(&$data = ''){
    if(is_array($data)){
        foreach ($data as $key => &$val){
            if(is_object($val)){
                $val = (array)$val;
            }else{
                object_to_array($val);
            }
        }
    }else if(is_object($data)){
        $data = (array)$data;
        object_to_array($data);
    }

    return $data;
}

/**
 * 生成随机密码
 * @param  string  $pw_length    密码长度
 * @return string  $code         随机密码
 */
function create_password( $pw_length = 18 ) {
    static $code;
    $last = '';
    do{
        $last = $code;
        $t = explode(' ',microtime());
        $code = substr(base_convert(strtr($t[0].$t[1].$t[1],'.',''),10,36), 0, $pw_length);
    }while(preg_match('/^[0-9]*$/',$code));
    $code = strtoupper($code);
    return $code;
}

/**
 * 获取券码类别名称
 * @param  integer $tid [description]
 * @return [type]       [description]
 */
function get_coupon_type_name( $tid = 0 ) {
    $map['tid'] = $tid;
    return Db::name('CouponType')->where($map)->value('type_name');
}

/**
 * 隐藏券码卡密
 */
function hidden_coupon_pass($pass = ''){
    return substr($pass, 0,4).'*****'.substr($pass, -4,4);
}

function get_hxfy_stock( $tid = 0, $uid = 0 ) {
    if ($tid) {
        // 调取解密接口
        $url = config('yquan_api_url').'coupon_stock';
        $send_data['tid'] = $tid;
        $send_data['uid'] = $uid;
        $send_res = http_curl($url,$send_data,'post');
        $result   = json_decode($send_res);
        if($result->code == 0){
            return $result->data;
        }else{
            return 0;
        }
    }
}

// 根据ID获取平台名称
function get_channel_name_by_id($id = 0){
    return Db::name('Channel')->where('uid',$id)->value('name');
}

// 根据ID获取商户名称
function get_merchant_name_by_id($id = 0){
    return Db::name('Merchant')->where('mid',$id)->value('merchant_name');
}

// 获取短信息
function get_sms($sms_sn,$sms_type)
{
    $url = "http://10.24.189.5/sms/api/get_sms_content";
    $data['sms_sn'] = $sms_sn;
    $data['sms_type'] = $sms_type;
    $res = http_curl($url, $data, "POST");
    return $res;
}

// 获取活动产品名称
function get_active_pname_by_id($tid = 0){
    if ($tid == 3) {
        return '移动、电信、联通三网流量70M';
    }elseif ($tid == 4) {
        return '移动、电信、联通三网流量500M';
    }else{
        return Db::name('CouponType')->where('tid',$tid)->value('type_name');
    }
    
}

    /**
     * 生成单个卡密
     * @param  array   $item   [生成类型组合]
     * @param  integer $length [密码长度]
     * @return [string]        [密码]
     */
    function make_pass( $length = 18, $item = [1,2,3,4] ) {
        // 构成元素
        $chars = ['0123456789', 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', '!@#$%^&*'];
        $str = '';
        foreach ($item as $key => $val) {
            $str .=  $chars[$val-1];
        }
        $code = make_str($str, $length);
        return $code;
    }

    /**
     * 随机生成单个字符串
     * @param  string  $chars  [生成元素集合]
     * @param  integer $length [密码长度]
     * @return [string]        [密码]
     */
    function make_str( $chars = '', $length = 18 ) {
        static $code;
        $last = '';
        do{
            $last = $code;
            $code = '';
            for ( $i = 0; $i < $length; $i++ ) {
                $code .= $chars[ mt_rand(0, strlen($chars) - 1) ];
            }
        } while ( substr($code,0,1) === '0' );
        return $code;
    }

