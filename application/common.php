<?php
// 应用公共文件
    
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

    /**
     * 生成单个卡密
     * @param  array   $item   [生成类型组合]
     * @param  integer $length [密码长度]
     * @return [string]        [密码]
     */
    function randOnePass( $item = [1,2,3,4], $length = 18 ) {
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
    function make_str( $chars = '', $length = 18 )
    {
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