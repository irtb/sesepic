<?php
namespace app\admin\controller;
use app\common\controller\BaseController;
use think\Controller;
use think\Request;
use think\Db;
use encrypt\aes256cbc\Aes;
class IndexController extends Controller {

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
     * 登陆页
     * @return [type] [description]
     */
    public function index() {
    	return $this->fetch();
    }

    /**
     * 登录
     * @return [type] [description]
     */
    public function login() {
    	if ($this->request->isPost()) {
			$phone    = $this->request->post('user/d');
			$password = $this->request->post('password/s');
			if (!$phone || !$password) {
				$this->error('必须登录！');
			}
			if (strlen($phone) !== 11) {
				$this->error('用户名错误！');
			}
			$res = Db::name('admin_user')->where('phone', $phone)->find();
			if (!empty($res)) {
				$pwd = $this->encrypt($password);
				if ($res['password'] === $pwd) {
					// 记录登录SESSION
		            $auth = [
								'id'              =>$res['id'],
								'name'            =>$res['name'],
								'phone'           =>$res['phone'],
								'last_login_time' =>time(),
								'last_login_ip'   =>$this->request->ip(),
		            		];
					session('user_auth', $auth);
					session('user_auth_sign', data_auth_sign($auth));
					$data['last_login_ip']   = $this->request->ip();
					$data['last_login_time'] = time();
					Db::name('admin_user')->where('id', $res['id'])->update($data);
					$this->success('登录成功','/admin/pic/index',2);
				} else {
					$this->error('密码错误！');
				}
			} else {
				$this->error('用户名错误！');
			}
    	} else {
			$this->redirect('/admin/index/index');	
    	}
    }

    /**
     * 退出
     * @return [type] [description]
     */
    public function logout() {
        session('user_auth', null);
        session('user_auth_sign', null);
        session('[destroy]');
        $this->success('退出成功！','/admin/index/index',1);
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
}