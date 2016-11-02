<?php
namespace User\Controller;
use Common\Controller\CoreController;

class UserCoreController extends CoreController {
	
	//会员中心 核心继承
    protected function _initialize() {
		//继承CoreController的初始化函数
        parent::_initialize();
		$AUTH_KEY=session(C('AUTH_KEY'));
		//判断认证key如果小于1 或 Admin模块登录Key不为1，跳转到后台登录网关
		if( $AUTH_KEY < 1  || session ('ModelKey.User')!=1) {
			redirect(U('Public/login'));
		}	
	}
}