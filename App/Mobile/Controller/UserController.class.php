<?php
/**
 * 用户控制器
 */
namespace Mobile\Controller;
use Think\Controller;

class UserController extends BaseController
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 用户首页
     */
    public function index(){
        $this->isLogin();
        $this->display();
    }

    /**
     * 注册页面
     */
    public function register()
    {
        $this->assign('stores_type', M('stores_type')->select());
        $this->display();
    }

    /**
     * 处理用户注册
     */
    public function doRegister()
    {
        //注册添加到用户表
        $data = array();
        $data['username'] = $_POST['username'];
        $data['nickname'] = $data['username'];
        if(empty($data['username'])){
            $this->ajaxReturn($data, '用户名不得为空!', 0);
        }

        if(M('user')->where(array('username'=>$data['username']))->find()){
            $this->ajaxReturn(array('status'=>0, 'msg'=>'该用户名已经被人使用,请更换!'));
        }

        if(empty($_POST['password'])){
            $this->ajaxReturn(array('status'=>0, 'msg'=>'密码不得为空!'));
        }else{
            $data['password'] = md5($_POST['password']);
        }

        $data['last_login_time'] = time();
        $data['create_time'] = $data['last_login_time'];
        $data['update_time'] = $data['last_login_time'];
        $data['status'] = 1;
        $uid = M('user')->data($data)->add();
        if($uid){
            //添加收货地址
            $address_data['uid'] = $uid;
            $address_data['name'] = $_POST['real_name'];
            $address_data['phone'] = $_POST['phone'];
            $address_data['address'] = $_POST['address'];
            $address_data['stores_type'] = $_POST['stores_type'];
            M('user_address')->data($address_data)->add();
            $this->ajaxReturn(array('status'=>1, 'msg'=>'注册成功,正在进入首页...'));
        }else{
            $this->ajaxReturn(array('status'=>0, 'msg'=>'新用户注册失败!'));
        }
    }

    /**
     * 登录页面
     */
    public function login()
    {
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
            if($user_info['password'] == md5($password)){
                $data['uid'] = $user_info['id'];
                $data['username'] = $user_info['username'];
                $data['password'] = $user_info['password'];
                cookie('user_info', $data, 2592000);
                session('user_info', $data);
                $this->ajaxReturn(array('status'=>1, 'msg'=>'登录成功,欢迎回来!'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'msg'=>'密码输入错误,请重新输入!'));
            }
        }else{
            $this->ajaxReturn(array('status'=>0, 'msg'=>'该用户名不存在!'));
        }
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
     * 退出登录
     */
    public function logout(){
        session('user_info', null);
        cookie('user_info', null);
        $this->ajaxReturn(array('status'=>1, 'msg'=>'退出登录成功!'));
    }
}