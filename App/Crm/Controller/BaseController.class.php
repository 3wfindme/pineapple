<?php
/**
 * Crm基础控制器
 */
namespace Crm\Controller;
use Think\Controller;
class BaseController extends Controller
{
    public $uid = '';
    public $username = '';
    public function _initialize()
    {
        $this->initUser();
    }

    /**
     * 初始化用户信息
     */
    public function initUser(){
        $user_info = session('crm_user_info');
        if($user_info){
            $this->uid = $user_info['uid'];
            $this->username = $user_info['username'];
            $this->assign('user', array('name'=>$this->username, 'uid'=>$this->uid));
        }
    }

    public function isLogin(){
        if(!$this->checkLogin()){
            $this->redirect(U('Crm/User/login'));
        }
    }

    /**
     * 检测是否已经登录
     */
    public function checkLogin()
    {
        if(session('crm_user_info')){
            return true;
        }else{
            $user_info = cookie('crm_user_info');
            $this->login($user_info['username'], $user_info['password']);
        }
    }

    /**
     * 登录
     */
    protected function login($username, $password){
        $user_info = M('user')->where(array('username'=>$username, 'password'=>md5($password), 'ident'=>3))->find();
        if($user_info){
            $data['uid'] = $user_info['id'];
            $data['username'] = $user_info['username'];
            $data['password'] = $user_info['password'];
            cookie('crm_user_info', $data);
            session('crm_user_info', $data);
            return true;
        }else{
            return false;
        }
    }


}