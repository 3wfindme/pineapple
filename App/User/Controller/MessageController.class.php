<?php

namespace User\Controller;

class MessageController extends UserCoreController {
	
    public $Model = null;

    protected function _initialize() {
        parent::_initialize();
        $this->Model = D('Message');
    }

    /* 发送信息
     * Auth   : Ghj
     * Time   : 2015-10-20
     **/
	public function send(){
		if(IS_POST){
			$post_data=I('post.');
            if(!check_verify($post_data['verify'])){
                $this->error('验证码输入错误！');
            }
			$post_data['send_from_id']=$this->UserInfo['username'];
			$data=$this->Model->create($post_data);
			if($data){
				$result = $this->Model->add($data);
				if($result){
					$this->success ( "操作成功！",U('outbox'));
				}else{
					$error = $this->Model->getError();
					$this->error($error ? $error : "操作失败！");
				}
			}else{
                $error = $this->Model->getError();
                $this->error($error ? $error : "操作失败！");
			}
		}else{
        	$this->display();
		}
	}
	
	
    /* 发件箱
     * Auth   : Ghj
     * Time   : 2015-10-20
     **/
	public function outbox(){
		$map['send_from_id']=$this->UserInfo['username'];
		$map['outbox']=1;
		$total = $this->Model->where($map)->count();
		if($total==0){
			$_list='';
		}else{
			$_list = $this->Model->where($map)->order('id desc')->limit('0,15')->select();
		}
		$this->assign('total', $total);
		$this->assign('_list', $_list);
		$this->display();
	}
	
    /* 收件箱
     * Auth   : Ghj
     * Time   : 2015-10-20
     **/
	public function inbox(){
		$map['send_to_id']=$this->UserInfo['username'];
		$map['inbox']=1;
		$total = $this->Model->where($map)->count();
		if($total==0){
			$_list='';
		}else{
			$_list = $this->Model->where($map)->order('id desc')->limit('0,15')->select();
		}
		$this->assign('total', $total);
		$this->assign('_list', $_list);
		$this->display();
	}
}
