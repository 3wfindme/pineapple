<?php
/**
 * 订单控制器
 */
namespace Mobile\Controller;
use Think\Controller;
use Think\Page;
use WeSDK\JsApiPay;
use WeSDK\WxPayUnifiedOrder;
use WeSDK\WxPayConfig;
use WeSDK\WxPayApi;
use WeSDK\WxPayException;

class OrderController extends BaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->isLogin();
    }

    /**
     * 确认订单,提交订单
     */
    public function confirm(){
        //查询收货地址
        $user_address = M('user_address')->where(array('uid'=>$this->uid))->find();
        if($user_address){
            $user_address['stores_type_name'] = M('stores_type')->where(array('id'=>$user_address['stores_type']))->getField('name');
        }
        $freight_price = M('config')->where(array('name'=>'ORDER_FREIGHT'))->getField('value');
        //查询优惠券列表
        $user_coupons = M('user_coupons')->where(array('uid'=>$this->uid, 'is_do'=>0))->order('price DESC')->select();

        $this->assign('user_coupons', $user_coupons);
        $this->assign('freight_price', $freight_price);
        $this->assign('user_address', $user_address);
        $this->display();
    }

    /**
     * 保存订单
     */
    public function saveOrder(){
        if(!$this->uid){
            $this->ajaxReturn(array('status'=>0, 'data'=>'', 'info'=>'登录超时,请重新登录!'));
        }
        $goods_list = explode('-', trim($_POST['goods_list'], '-'));
        if(count($goods_list) > 0){
            $total_price = 0;
            $data['name'] = '';
            $data['goods_count'] = 0;
            foreach($goods_list as $k=>$v){
                $goods_list[$k] = explode(':', $v);
                $goods_list[$k]['goods_info'] = M('goods')->where(array('id'=>$goods_list[$k][0]))->find();
                $goods_list[$k]['nums'] = $goods_list[$k][1];
                unset($goods_list[$k][0]);
                unset($goods_list[$k][1]);
                if(!$goods_list[$k]['goods_info']){
                    unset($goods_list[$k]);
                }else{
                    $total_price += $goods_list[$k]['goods_info']['price'] * $goods_list[$k]['nums'];
                    $data['name'] .= $goods_list[$k]['goods_info']['name'].'x'.$goods_list[$k]['nums'].',';
                    $data['goods_count'] += $goods_list[$k]['nums'];
                }
            }
            /* 配置订单 */
            $data['uid'] = $this->uid;
            $data['name'] = trim($data['name'], ',');
            $data['coupons_price'] = 0;
            $data['freight_price'] = M('config')->where(array('name'=>'ORDER_FREIGHT'))->getField('value');
            if($_POST['user_coupons_id'] > 0) {
                $coupons = M('user_coupons')->where(array('id' => $_POST['user_coupons_id'], 'uid' => $this->uid))->find();
                if($coupons && $coupons['is_do'] == 0){
                    $data['user_coupons_id'] = $_POST['user_coupons_id'];
                    $data['coupons_price'] = $coupons['price'];
                }
            }
            $data['pay_price'] = $total_price + $data['freight_price'] - $data['coupons_price'];
            $data['pay_status'] = 0;
            $data['price'] = $total_price;
            $data['pay_type'] = intval($_POST['pay_type']);
            $data['distribution_status'] = 0;
            $data['ctime'] = time();
            $data['pay_time'] = 0;
            $data['is_del'] = 0;
            //写入收货地址
            $user_address = M('user_address')->where(array('uid'=>$this->uid))->find();
            $data['freight_name'] = $user_address['name'];
            $data['freight_phone'] = $user_address['phone'];
            $data['freight_address'] = $user_address['address'];
            $data['freight_stores_type'] = $user_address['stores_type'];
            $order_id = M('order')->data($data)->add();
            if($order_id){
                //开始往商品表里面添加商品
                foreach($goods_list as $k=>$v){
                    $goods_data['uid'] = $this->uid;
                    $goods_data['order_id'] = $order_id;
                    $goods_data['goods_id'] = $v['goods_info']['id'];
                    $goods_data['goods_name'] = $v['goods_info']['name'];
                    $goods_data['count'] = $v['nums'];
                    $goods_data['price'] = $v['goods_info']['price'];
                    $goods_data['ctime'] = time();

                    M('order_goods')->data($goods_data)->add();
                }

                $this->ajaxReturn(array('status'=>1, 'data'=>'', 'url'=>U('Mobile/Order/finish', array('order_id'=>$order_id)), 'info'=>'成功提交订单!'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'data'=>'', 'info'=>'下单失败,请返回刷新重试!'));
            }
        }else{
            $this->ajaxReturn(array('status'=>0, 'data'=>$goods_list, 'info'=>'购物车为空,请返回重新添加商品!'));
        }
    }

    /**
     * 下单成功,开始支付
     */
    public function finish(){
        $order_id = intval($_GET['order_id']);
        $order_info = M('order')->where(array('id'=>$order_id))->find();
        if(!$order_info){
            $this->show('<script>alert("参数错误,请关闭重试!");</script>');
        }

        $this->assign('order_info', $order_info);
        $this->display();
    }

    /**
     * 我的订单
     */
    public function my(){
        $ORDER = M('order'); // 实例化User对象
        $where['uid'] = $this->uid;
        if($_GET['type'] == 'finish'){
            $where['distribution_status'] = 2;
        }else{
            $where['distribution_status'] = array('in', '0,1');
        }
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
        if($_GET['type'] == 'finish'){
            $order_list = $ORDER->where($where)->order('take_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $order_list = $ORDER->where($where)->order('ctime DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        if($count > 0){
            foreach($order_list as $k=>$v){
                $order_list[$k]['goods_list'] = M('order_goods')->where(array('order_id'=>$v['id']))->select();
            }
        }

        $this->assign('page',$show);// 赋值分页输出
        $this->assign('count', $count);
        $this->assign('order_list', $order_list);
        $this->display('list');
    }

    /**
     * 收货订单
     */
    public function freightFinish(){
        $this->display('list');
    }

    /**
     * 确认收货
     */
    public function take(){
        $data['distribution_status'] = 2;
        $data['distribution_time'] = time();
        M('order')->data($data)->where(array('id'=>$_POST['order_id']))->save();
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 支付成功
     */
    public function ok(){
        if(strstr($_SERVER['HTTP_REFERER'], '0464haocai.com/wxpay/example/jsapi.php')){
            $data['pay_status'] = 1;
            $data['pay_time'] = time();
            $data['pay_type'] = 2;
            M('order')->where(array('id'=>$_GET['order_id']))->data($data)->save();
            $this->redirect(U('Mobile/Order/my'));
        }else{
            $this->error('非法请求!');
        }
    }

}

