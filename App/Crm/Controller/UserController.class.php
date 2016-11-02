<?php
/**
 * WEB APP首页
 */
namespace Crm\Controller;
use Think\Controller;

class UserController extends BaseController {

    /**
     * 显示首页
     */
    public function index(){
        $this->isLogin();
        $this->display();
    }

    /**
     * 登录控制器
     */
    public function login(){
        $this->display();
    }

    /**
     * 处理用户登录
     */
    public function doLogin()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $user_info = M('user')->where(array('username'=>$username))->find();
        if($user_info){
            if($user_info['ident'] != 3){
                $this->ajaxReturn(array('status'=>0, 'msg'=>'身份认证未通过 !'));
            }
            if($user_info['password'] == md5($password)){
                $data['uid'] = $user_info['id'];
                $data['username'] = $user_info['username'];
                $data['password'] = $user_info['password'];
                cookie('crm_user_info', $data, 2592000);
                session('crm_user_info', $data);
                $this->ajaxReturn(array('status'=>1, 'msg'=>'登录成功,欢迎回来!'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'msg'=>'密码输入错误,请重新输入!'));
            }
        }else{
            $this->ajaxReturn(array('status'=>0, 'msg'=>'该用户名不存在!'));
        }
    }

    /**
     * 退出登录
     */
    public function logout(){
        session('crm_user_info', null);
        cookie('crm_user_info', null);
        $this->success('退出登录成功! ');
//        $this->ajaxReturn(array('status'=>1, 'msg'=>'退出登录成功!'));
    }

    /**
     * ajax验证用户名是否被注册
     */
    public function validateUsername(){
        $user_info = M('user')->where(array('username'=>$_POST['username']))->find();
        if($user_info){
            $this->ajaxReturn(array('status'=>0, 'msg'=>'该用户名已经被注册!'));
        }else{
            $this->ajaxReturn(array('status'=>1, 'msg'=>'该用户名未被使用!'));
        }
    }

    /**
     * 修改密码
     */
    public function editPassword(){
        if(IS_POST){
            $user_info = session('crm_user_info');
            if($user_info['password'] == md5($_POST['password'])){
                M('user')->data(array('password'=>md5($_POST['new_password']), 'update_time'=>time()))->where(array('id'=>$user_info['uid']))->save();
                session('crm_user_info', null);
                cookie('crm_user_info', null);
                $this->ajaxReturn(array('status'=>1, 'msg'=>'密码修改成功,请使用新密码重新登录!'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'msg'=>'原密码不正确,请重新输入!'));
            }
        }else{
            $this->display();
        }
    }
}