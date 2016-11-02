<?php

namespace User\Controller;
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
				$AGA_Data=M('AuthGroupAccess')->where (array('uid'=>$userinfo['id']))->find ();
				$AG_Data=M('AuthGroup')->where (array('id'=>$AGA_Data['group_id']))->find ();
				$userinfo['group_title']=$AG_Data['title'];
				session (C('AUTH_KEY'),$userinfo['id']);
				session ('UserInfo',$userinfo);
				session ('ModelKey.User',1);
				//action_log('Admin_Login', 'User', $userinfo ['id']);
				$this->success ( "登录成功！", U ( C ( 'AUTH_USER_INDEX' ) ) );
			} else {
				$this->error ( "用户名密码错误或者此用户已被禁用！", U ( C ( 'AUTH_USER_GATEWAY' ) ) );
			}
        } else {
			$ModelKey=session ('ModelKey.User');
            if(is_login() && $ModelKey==1){
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
			//action_log('Admin_Logout', 'User', is_login());
			session ( null );
			if (session ( C ( 'AUTH_KEY' ) )) {
				$this->error ( "退出失败", U ( C ( 'AUTH_USER_INDEX' ) ) );
			}else{
				$this->success ( "退出成功！", U ( C ( 'AUTH_USER_GATEWAY' ) ) );
			}
		}
    }
	
	//注册页面
    public function register() {
        if (C('USER_REGISTER')==1) {
            $this->error("系统不允许新会员注册！");
        }
        if (is_login()) {
            $this->success("您已经是登陆状态，无需注册！", U("Index/index"));
        } else {
			if(IS_POST){
				$username = I ( "post.username", "", "trim" );
				$email = I ( "post.email", "", "trim" );
				$nickname = I ( "post.nickname", "", "trim" );
				$password = md5(I ( "post.password", "", "trim" ));
				$password2 = md5(I ( "post.password2", "", "trim" ));
				if ($password!=$password2) {
					$this->error ( "重复密码有误,请重新输入！", U ( C ( 'AUTH_USER_GATEWAY' ) ) );
				}
				if (empty ( $username ) || empty ( $password )) {
					$this->error ( "用户名或者密码不能为空，请重新输入！", U ( C ( 'AUTH_USER_GATEWAY' ) ) );
				}
				$map = array (
						'username' => $username,
						'password' => $password,
						'email' => $email,
						'nickname' => $nickname,
						'status' => 1 
				);
				
				$data=D ( 'User' )->create($map);
				if($data){
					$result = D ( 'User' )->add($data);
					if($result){
						action_log('Reg_User', 'User', $result);
						$userinfo = M ( 'User' )->find ($result);
						session (C('AUTH_KEY'),$userinfo['id']);
						session ('userinfo',$userinfo);
						session ('ModelKey.User',1);
						//action_log('Admin_Login', 'User', $userinfo ['id']);
						$this->success ( "注册成功，正在跳转至会员中心！", U ( C ( 'AUTH_USER_INDEX' ) ) );
					}else{
						$error = D ( 'User' )->getError();
						$this->error($error ? $error : "注册失败！");
					}
				}else{
					$error = D ( 'User' )->getError();
					$this->error($error ? $error : "注册失败！");
				}
			}else{
				$this->display('register');
			}
        }
    }
	
    //忘记密码界面
    public function lostpassword() {
        $this->display();
    }

    public function verify(){
        $verify = new \Think\Verify();
		$verify->useCurve=false;
		$verify->length=4;
        $verify->entry(1);
    }
}
