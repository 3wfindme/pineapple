<?php

namespace Admin\Controller;
use Common\Controller\CoreController;

class PublicController extends CoreController {
	
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
			$username = I ( "post.username", "", "trim" );
			$password = md5(I ( "post.password", "", "trim" ));
			if (empty ( $username ) || empty ( $password )) {
				$this->error ( "用户名或者密码不能为空，请重新输入！", U ( C ( 'AUTH_USER_GATEWAY' ) ) );
			}
			$map = array (
					'username' => $username,
					'password' => $password,
					'status' => 1 
			);
			$userinfo = M ( 'User' )->where ( $map )->find ();
			if ($userinfo) {
				session (C('AUTH_KEY'),$userinfo['id']);
				session ('userinfo',$userinfo);
				session ('ModelKey.Admin',1);
				action_log('Admin_Login', 'User', $userinfo ['id']);
				$this->success ( "登录成功！", U ( C ( 'AUTH_USER_INDEX' ) ) );
			} else {
				$this->error ( "用户名密码错误或者此用户已被禁用！", U ( C ( 'AUTH_USER_GATEWAY' ) ) );
			}
        } else {
            if(is_login()){
                $this->redirect('Index/index');
            }else{
                $this->display();
            }
        }
    }

    /* 退出登录 */
    public function logout(){
		if (!is_login()) {
			$this->error ( "尚未登录", U ( C ( 'AUTH_USER_GATEWAY' ) ) );
		}else{
			action_log('Admin_Logout', 'User', is_login());
			session ( null );
			if (session ( C ( 'AUTH_KEY' ) )) {
				$this->error ( "退出失败", U ( C ( 'AUTH_USER_INDEX' ) ) );
			}else{
				$this->success ( "退出成功！", U ( C ( 'AUTH_USER_GATEWAY' ) ) );
			}
		}
    }

}
