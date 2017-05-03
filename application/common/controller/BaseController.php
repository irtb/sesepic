<?php
namespace app\common\controller;
use think\Controller;
use think\Request;
use think\Db;
use encrypt\aes256cbc\Aes;

class BaseController extends Controller {

	private $iv;

    /**
     * 初始化
     */
    public function __construct() {
        parent::__construct();

        // 获取配置
		$this->iv  = config('encrypt_iv');
		$this->key = config('encrypt_key');
        
        // 接入IP控制
        $this->input_ip_filter();

        // 接出IP控制
        $this->output_ip_filter();

        // 判断登陆
        $this->is_login();
    }

    /**
     * 接入IP白名单
     */
    private function input_ip_filter()
    {
        $ip_list = config('access_ip_list');
        $input_ip = $this->request->ip();
        if (!in_array($input_ip, $ip_list)) {
            return 'You don\'t have permission to access! Access records will be tracked and recorded.';
            exit;
        }
    }

    /**
     * 接出IP白名单
     */
    private function output_ip_filter()
    {
        $ip_list = config('access_ip_list');
        $input_ip = $this->request->ip();
        if (!in_array($input_ip, $ip_list)) {
            return 'You don\'t have permission to access! Access records will be tracked and recorded.';
            exit;
        } 
    }

    /**
     * 加密字符串
     * @param  string $key [秘钥]
     * @param  string $iv  [向量]
     * @param  string $str [原始字符串]
     * @return [string]    [密文字符串]
     */
    protected function encrypt($password)
    {
        $aes = new Aes($this->key, $this->iv);
        $encrypted = $aes->encrypt($password);
		return str_replace(array('+','/','='),array('-','_',''),base64_encode($encrypted));
    }

    /**
     * 判断登陆
     */
    private function is_login() {
        $user = session('user_auth');
        if ( empty($user) ) {
            $this->redirect('/admin/index/index');
        }
        $auth_data = data_auth_sign($user);
        if (session('user_auth_sign') !== $auth_data) {
            $this->redirect('/admin/index/index');
        }
    }
}