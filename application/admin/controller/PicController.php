<?php
namespace app\admin\controller;
use app\common\controller\BaseController;
use think\Db;
class PicController extends BaseController {
    /**
     * 上传图片新建组
     * @return [type] [description]
     */
    public function index() {
    	if ($this->request->isPost()) {
    		$data['group_name'] = $this->request->post('group_name/s');
    		$group_id = Db::name('group')->insertGetId($data);
    		$this->redirect('/admin/pic/upload_pic/group_id/'.$group_id.'/group_name/'.$data['group_name']);
    	} else {
    		return $this->fetch();	
    	}
    }

    /**
     * 上传图片模板
     * @return [type] [description]
     */
    public function upload_pic() {
    	$group_name = $this->request->param('group_name/s');
    	$group_id = $this->request->param('group_id/d');
    	$this->assign('group_name', $group_name);
    	$this->assign('group_id', $group_id);
		return $this->fetch();
    }

    /**
     * 上传
     * @return [type] [description]
     */
    public function upload_done() {
    	// Make sure file is not cached (as it happens for example on iOS devices)
    	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    	header("Cache-Control: no-store, no-cache, must-revalidate");
    	header("Cache-Control: post-check=0, pre-check=0", false);
    	header("Pragma: no-cache");

    	$file = $this->request->file('file');
	    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
	    if($info){
	    	$pic_name = $info->getSaveName();
	    	$data['pic'] = $pic_name;
	    	$pid = Db::name('pic')->insertGetId($data);
	    	return $pid;
	    }else{
	        return $file->getError();
	    }
    }

    /**
     * 保存图片
     * @return [type] [description]
     */
    public function save_pic() {
    	$pid = $this->request->post('pid/s');
    	$data['gid'] = $this->request->post('group_id/d');
    	$data['group_name'] = $this->request->post('group_name/s');
    	$res = Db::name('pic')->where('id','in',$pid)->update($data);
    	if ($res !== false) {
    		return json(['code'=>0,'msg'=>'保存成功','data'=>''],200);
    	}
    }

    /**
     * 图片列表
     * @return [type] [description]
     */
    public function pic_list() {
    	$data = Db::name('pic')->where('1=1')->select();
    	$this->assign('data', $data);
    	return $this->fetch();
    }
}