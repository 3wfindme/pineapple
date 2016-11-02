<?php
/**
 * WEB APP首页
 */
namespace Crm\Controller;
use Think\Controller;
use Think\Page;

class IndexController extends BaseController {

    public $pay_status_words = array('未付款','已付款');
    public $distribution_status_words = array('未发货','配送中','已收货');

    public function _initialize(){
        parent::_initialize();
        $this->isLogin();
    }

    /**
     * 显示首页
     */
    public function index(){

        $this->display();
    }

    /**
     * 我的客户列表
     */
    public function myUser(){
        $USER = M('custom'); // 实例化User对象
        $where['sale_uid'] = $this->uid;
        if($_POST['username']) {
            $where['username'] = array('like', '%' . $_POST['username'] . '%');
        }
        $count      = $USER->where($where)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,10, array('type'=>$_GET['type']));// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('header','个订单');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','末页');
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $USER->where($where)->order('ctime DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        if($count > 0){
            foreach($list as $k=>$v){
                $list[$k]['phone'] = M('user_address')->where(array('uid'=>$v['id']))->getField('phone');
                $list[$k]['stores_type'] = M('stores_type')->where(array('id'=>$v['stores_type']))->getField('name');
            }
        }

        //查询我的客户今日下单总斤数
        //查询我的客户ID
        $customs = M('custom')->where(array('sale_uid'=>$this->uid))->select();
        if($customs){
            $uids = '';
            foreach($customs as $k=>$v){
                $customs[$k]['uid'] = M('user')->where(array('username'=>$v['username']))->getField('id');
                if($customs[$k]['uid']){
                    $uids .= $customs[$k]['uid'].',';
                }
            }
            $uids = trim($uids,',');
        }
        $time = strtotime(date("Y-m-d").' 0:0:0');
        $all_count = M('order')->where(array('ctime'=>array('gt',$time), 'uid'=>array('in', $uids)))->sum('goods_count');

        //查询我的客户本月总斤数量
        $time = strtotime(date("Y-m").'-01 0:0:0');
        $all_count_month = M('order')->where(array('ctime'=>array('gt',$time), 'uid'=>array('in', $uids)))->sum('goods_count');

        $this->assign('all_count', $all_count);
        $this->assign('all_count_month', $all_count_month);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('count', $count);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 添加新客户
     */
    public function addUser(){
        if(IS_POST){
            //注册添加到用户表
            $data = array();
            $data['username'] = $_POST['username'];
            $data['phone'] = $_POST['phone'];
            $data['nickname'] = $data['username'];

            if(empty($data['username'])){
                $this->ajaxReturn($data, '用户名不得为空!', 0);
            }
            if(empty($data['phone'])){
                $this->ajaxReturn($data, '手机号不得为空!', 0);
            }
            $user_info = M('user')->where(array('username'=>$data['username'], 'phone'=>$data['phone']))->find();
            if($user_info){
                if(M('custom')->where(array('username'=>$data['username']))->find()){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'该用户名已经被添加, 请更换!'));
                }
                if(M('custom')->where(array('phone'=>$data['phone']))->find()){
                    $this->ajaxReturn(array('status'=>0, 'msg'=>'该手机号已经被添加, 请更换!'));
                }
            }else{
                $this->ajaxReturn(array('status'=>0, 'msg'=>'添加的客户名称或者手机号不存在!'));
            }

            $data['sale_uid'] = $this->uid;
            $time = time();
            $data['ctime'] = $time;
            $data['utime'] = $time;
            $user_address = M('user_address')->where(array('uid'=>$user_info['id']))->find();
            $data['address'] = $user_address['address'];
            $data['stores_type'] = $user_address['stores_type'];
            $uid = M('custom')->data($data)->add();
            if($uid){
                M('user')->where(array('id'=>$user_info['id']))->data(array('who_uid'=>$this->uid))->save();
                $this->ajaxReturn(array('status'=>1, 'msg'=>'添加客户成功!'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'msg'=>'添加客户失败!'));
            }
        }else{
            $this->display();
        }
    }

    /**
     * 用户详情
     */
    public function userInfo(){
        $info = M('custom')->where(array('id'=>$_GET['uid']))->find();
        $info['stores_type'] = M('stores_type')->where(array('id'=>$info['stores_type']))->getField('name');
        $uid = M('user')->where(array('username'=>$info['username']))->getField('id');
        $time = strtotime(date("Y-m-d").' 0:0:0');
        $info['all_count'] = M('order')->where(array('ctime'=>array('gt',$time), 'uid'=>$uid))->sum('goods_count');
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 修改用户资料
     */
    public function editUser(){
        //注册添加到用户表
        $data = array();
        $data['username'] = $_POST['username'];
        $data['nickname'] = $data['username'];

        if(empty($data['username'])){
            $this->ajaxReturn($data, '用户名不得为空!', 0);
        }

        if(M('user')->where(array('username'=>$data['username'], 'id'=>array('neq', $_POST['uid'])))->find()){
            $this->ajaxReturn(array('status'=>0, 'msg'=>'该用户名已经被人使用, 请更换!'));
        }

        if(!empty($_POST['password'])){
            $data['password'] = md5($_POST['password']);
        }

        $data['update_time'] = time();
        M('user')->where(array('id'=>$_POST['uid']))->data($data)->save();

        //更新收货地址
        $address_data['uid'] = $_POST['uid'];
        $address_data['name'] = $_POST['real_name'];
        $address_data['phone'] = $_POST['phone'];
        $address_data['address'] = $_POST['address'];
        $address_data['stores_type'] = $_POST['stores_type'];
        M('user_address')->where(array('uid'=>$_POST['uid']))->data($address_data)->save();
        $this->ajaxReturn(array('status'=>1, 'msg'=>'成功更新客户资料!'));
    }

    /**
     * 订单管理
     */
    public function order(){
        $ORDER = M('order'); // 实例化User对象
        //查询业务员的客户
        $user_ids = M('user')->field('id')->where(array('who_uid'=>$this->uid))->select();
        $user_ids_str = 0;
        if($user_ids){
            foreach($user_ids as $k=>$v){
                $user_ids_str .= $v['id'].',';
            }
            $user_ids_str = trim($user_ids_str, ',');
        }
        $where['uid'] = array('in', $user_ids_str);
//        if($_GET['type'] == 'finish'){
//            $where['distribution_status'] = 2;
//        }else{
//            $where['distribution_status'] = array('in', '0,1');
//        }
        $where['is_del'] = 0;
        $count      = $ORDER->where($where)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,10, array('type'=>$_GET['type']));// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('header','个订单');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','末页');
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $order_list = $ORDER->where($where)->order('ctime DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        if($count > 0){
            foreach($order_list as $k=>$v){
                $order_list[$k]['pay_status'] = $this->pay_status_words[$order_list[$k]['pay_status']];
                $order_list[$k]['distribution_status'] = $this->distribution_status_words[$order_list[$k]['distribution_status']];
            }
        }

        $this->assign('page',$show);// 赋值分页输出
        $this->assign('count', $count);
        $this->assign('list', $order_list);
        $this->display();
    }

    /**
     * 订单详情
     */
    public function orderInfo(){
        $order_info = M('order')->where(array('id'=>$_GET['id']))->find();
        $order_info['goods_list'] = M('order_goods')->where(array('order_id'=>$order_info['id']))->select();
        $this->assign('ol', $order_info);
        $this->display();
    }

    /**
     * 查找客户
     */
    public function searchUser(){
        $this->display();
    }


}