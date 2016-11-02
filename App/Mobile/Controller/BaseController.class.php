<?php
/**
 * 手机端基础控制器
 */
namespace Mobile\Controller;
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
        $user_info = session('user_info');
        if($user_info){
            $this->uid = $user_info['uid'];
            $this->username = $user_info['user_name'];
        }
    }

    /**
     * @desc 判断是否登录
     */
    public function isLogin(){
        if(!$this->checkLogin()){
            $this->redirect(U('Mobile/User/login'));
        }
    }

    /**
     * @desc 检测是否已经登录
     * @return bool
     */
    public function checkLogin()
    {
        if(session('user_info')){
            return true;
        }else{
            $user_info = cookie('user_info');
            if($user_info){
                $user_info = M('user')->where(array('username'=>$user_info['username'], 'password'=>$user_info['password']))->find();
                if($user_info){
                    $data['uid'] = $user_info['id'];
                    $data['username'] = $user_info['username'];
                    $data['password'] = $user_info['password'];
                    cookie('user_info', $data, 2692000);
                    session('user_info', $data);
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }

    /**
     * @desc 登录
     * @param $username string
     * @param $password string
     * @return bool
     */
    public function login($username = null, $password = null){

        $user_info = M('user')->where(array('username'=>$username, 'password'=>$password))->find();

        if($user_info){
            $data['uid'] = $user_info['id'];
            $data['username'] = $user_info['username'];
            $data['password'] = $user_info['password'];
            cookie('user_info', $data, 2692000);
            session('user_info', $data);
            return true;
        }else{
            return false;
        }
    }


}