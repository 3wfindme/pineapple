<?php
/**
 * 订单管理.
 * User: bobchu
 * Date: 15/11/9
 * Time: 下午1:29
 */

namespace Admin\Controller;

use Think\Db;
use \Common\Libs\Database;
use \Common\Libs\GoodsCategory;
use Think\Model;

class OrderController extends AdminCoreController
{
    public $pay_status_name_words = array('未支付', '已支付');
    public $distribution_status_name_words = array('未发货', '配送中', '已收货');
    public $pay_type_name_words = array('其他', '货到付款', '微信支付', '支付宝支付');
//    public $status_name_words = array('下架', '上架');
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 订单列表
     */
    public function index(){
        if(IS_POST){
            $post_data=I('post.');
            $post_data['first'] = $post_data['rows'] * ($post_data['page'] - 1);
            $map = array();
            $map = $this->_search();
            $total = M('order')->where($map)->count();
            $total_price = M('order')->where($map)->sum('pay_price');
            if($total==0){
                $_list='';
            }else{
                $_list = M('order')->where($map)->order($post_data['sort'].' '.$post_data['order'])->limit($post_data['first'].','.$post_data['rows'])->select();
            }

            foreach($_list as $list_key=>$list_one){
                $_list [$list_key]["username"] = M('user')->where(array('id'=>$list_one['uid']))->getField('username');
                $_list [$list_key]["ctime"]=date("Y年m月d日 H:i:s",$_list[$list_key]["ctime"]);
                if($_list[$list_key]["pay_status"]){
                    $_list [$list_key]["pay_time"]=date("Y年m月d日 H:i:s",$_list[$list_key]["pay_time"]);
                }else{
                    $_list [$list_key]["pay_time"]='未支付';
                }
                $_list [$list_key]['pay_status_name'] = $this->pay_status_name_words[$list_one['pay_status']];
                $_list [$list_key]['distribution_status_name'] = $this->distribution_status_name_words[$list_one['distribution_status']];
                $_list [$list_key]['pay_type_name'] = $this->pay_type_name_words[$list_one['pay_type']];
                $_list[$list_key]['freight_info'] = $_list[$list_key]['freight_name'].',<br />'.$_list[$list_key]['freight_phone'].',<br />'.$_list[$list_key]['freight_address'];
                $_list[$list_key]['sum_total_price'] = $total_price;
                $operate_menu='';
                if(Is_Auth('Admin/Order/printIt')){
                    $operate_menu = $operate_menu.'<a href=\''.U ( 'printIt', array ('id' => $_list [$list_key] ['id'] ) ).'\' target="_blank" >打印</a>';
                }
                if(Is_Auth('Admin/Order/edit')){
                    $operate_menu = $operate_menu."<a href='#' onclick=\"Submit_Form('Order_Form','Order_Data_List','" . U ( 'edit', array ('id' => $_list [$list_key] ['id'] ) ) . "','','编辑订单','');\">编辑</a>";
                }
                if(Is_Auth('Admin/Order/del')){
                    $operate_menu = $operate_menu."<a href='#' onclick=\"Data_Remove('" . U ( 'del', array ('id' => $_list [$list_key] ['id'] ) ) . "','Order_Data_List');\">删除</a>";
                }
                $_list [$list_key] ['operate'] = $operate_menu;
            }
            $data = array('total'=>$total, 'rows'=>$_list);
            $this->ajaxReturn($data);
        }else{
            $this->assign('stores_type', M('stores_type')->select());
            $this->display();
        }
    }

    /**
     * 搜索
     **/
    protected function _search() {
        $map = array ();
        $post_data = I('post.');

        $map['is_del'] = 0;

        if($post_data['uid']){
            $map['uid'] = $post_data['uid'];
        }

        if($post_data['ctime_begin'] && $post_data['ctime_end']){
            $map['ctime'] = array(array('gt', strtotime($post_data['ctime_begin'])), array('lt', strtotime($post_data['ctime_end'])), 'AND');
        }

        if($post_data['freight_name']){
            $map['freight_name'] = array('like', '%'.$post_data['freight_name'].'%');
        }

        if($post_data['freight_address']){
            $map['freight_address'] = array('like', '%'.$post_data['freight_address'].'%');
        }

        if($post_data['freight_phone']){
            $map['freight_phone'] = $post_data['freight_phone'];
        }

        /* 名称：状态 字段：status 类型：select*/
        if($post_data['pay_status'] > 0){
            $map['pay_status'] = $post_data['pay_status'];
        }
        if($post_data['distribution_status'] > 0){
            $map['distribution_status'] = $post_data['distribution_status'];
        }
        if($post_data['stores_type'] > 0){
            $map['stores_type'] = $post_data['stores_type'];
        }
        return $map;
    }

    /**
     * 添加商品
     */
    public function add(){
        if(IS_POST){
            if(empty($_POST['name'])){
                $this->error('商品名称不能为空!');
            }
            $data = $_POST;
            if(!is_numeric($data['price'])){
                $this->error('商品价格必须为数字!');
            }
            if($data['price'] <= 0){
                $this->error('商品价格必须大于0!');
            }
            if($data['cid'] <= 0){
                $this->error('请选择合适的商品分类!');
            }
            $data['original_price'] = $data['price'];
            $data['ctime'] = time();
            $data['utime'] = $data['ctime'];
            $goods_id = M('goods')->data($data)->add();
            if($goods_id){
                $this->success('商品添加成功!');
            }else{
                $this->error('商品添加失败!');
            }
        }else{
            /* 查询已有分类 */
            $GC = new GoodsCategory();
            $category_list = $GC->getList();
            $this->assign('category_list', $category_list);
            $this->display();
        }
    }

    /**
     * 编辑订单
     */
    public function edit(){
        if(IS_POST){
            $data = $_POST;
            $info = M('order')->where(array('id'=>$data['order_id']))->find();
            if($info['pay_type'] == 1){
                if($info['pay_status'] != $data['pay_status']){
                    if($data['pay_status'] == 1){
                        //改为支付状态
                        $save_data['pay_status'] = 1;
                        $save_data['pay_time'] = time();
                    }
                }
            }
            $save_data['remark'] = $data['remark'];
            $save_data['distribution_status'] = $data['distribution_status'];
            if($save_data['distribution_status'] == 1 && $save_data['distribution_status'] != $info['distribution_status']){
                $save_data['distribution_time'] = time();
            }elseif($save_data['distribution_status'] == 2 && $save_data['distribution_status'] != $info['distribution_status']){
                $save_data['take_time'] = time();
            }
            $save_data['utime'] = $data['utime'];
            $save_data['u_uid'] = getAdminUid();
            M('order')->data($save_data)->where(array('id'=>$info['id']))->save();
            $this->success('订单更新成功!');
        }else{
            $info = M('order')->where(array('id'=>$_GET['id']))->find();
            $info['pay_status_name'] = $this->pay_status_name_words[$info['pay_status']];
            $info['distribution_status_name'] = $this->distribution_status_name_words[$info['distribution_status']];
            $info['pay_type_name'] = $this->pay_type_name_words[$info['pay_type']];
            $goods_list = M('order_goods')->where(array('order_id'=>$info['id']))->select();
            $this->assign('info', $info);
            $this->assign('goods_list', $goods_list);
            $this->display();
        }
    }

    /**
     * 删除商品
     */
    public function del(){

        $id=I('get.id');
        empty($id)&&$this->error('参数不能为空！');
//        $res=M('order')->delete($id);
        $res = M('order')->data(array('is_del'=>1))->where(array('id'=>$id))->save();
        if(!$res){
            $this->error('删除失败！');
        }else{
            $this->success('删除成功！');
        }
    }

    /**
     * 拉取门店类型
     */
    public function loadStoresType(){
        $this->ajaxReturn(M('stores_type')->select());
    }

    /**
     * 打印订单
     */
    public function printIt(){
        $info = M('order')->where(array('id'=>$_GET['id']))->find();
        $info['username'] = M('user')->where(array('id'=>$info['uid']))->getField('username');
        $goods_list = M('order_goods')->where(array('order_id'=>$_GET['id']))->select();
        $info['pay_type_name'] = $this->pay_type_name_words[$info['pay_type']];
        $this->assign('info', $info);
        $this->assign('goods_list', $goods_list);
        $this->display();
    }

    /**
     * 订单统计
     */
    public function tongji(){
        if(IS_POST){
            $post_data=I('post.');
            $post_data['first'] = $post_data['rows'] * ($post_data['page'] - 1);
            $map = array();
            $total = M('goods')->where($map)->count();
            if($total==0){
                $_list='';
            }else{
                $_list = M('goods')->where($map)->order($post_data['sort'].' '.$post_data['order'])->limit($post_data['first'].','.$post_data['rows'])->select();
            }
            //查询订单ID
            if($post_data['ctime_begin']){
                $begin_time = strtotime($post_data['ctime_begin']);
                $end_time = strtotime($post_data['ctime_end']);
            }else{
                $begin_time = strtotime(date("Y-m-d 0:0:0"));
                $end_time= $begin_time + 60*60*24;
            }
            $order_where['ctime'] = array(array('egt',$begin_time), array('elt',$end_time), 'and');
            $order_where['is_del'] = 0;
            $order_id = M('order')->field('id')->where($order_where)->select();
            $order_ids = 0;
            if($order_id){
                foreach($order_id as $k=>$v){
                    $order_ids .= $v['id'].',';
                }
                $order_ids = trim($order_ids, ',');
            }
            foreach($_list as $list_key=>$list_one){
                $_list [$list_key]["cname"] = M('goods_category')->where(array('id'=>$list_one['cid']))->getField('name');
                if($order_ids == 0){
                    $_list [$list_key]["count"] = 0;
                }else{
                    $_list [$list_key]["count"] = M('order_goods')->where(array('order_id' => array('in', $order_ids), 'goods_id' => $list_one['id']))->sum('count');
                }
                $_list [$list_key]["total_price"] = $_list [$list_key]["count"] * $_list [$list_key]["price"] .' 元';
                $_list [$list_key]["price"] .= ' 元';
            }
            $data = array('total'=>$total, 'rows'=>$_list);
            $this->ajaxReturn($data);
        }else{
            /* 查询已有分类 */
            $GC = new GoodsCategory();
            $category_list = $GC->getList();
            $this->assign('category_list', $category_list);
            $this->display();
        }
    }

    /**
     * 打印统计订单
     */
    public function printTongji(){
        $post_data=I('post.');
        $_list = M('goods')->select();
        //查询订单ID
        if($post_data['ctime_begin']){
            $begin_time = strtotime($post_data['ctime_begin']);
            $end_time = strtotime($post_data['ctime_end']);
        }else{
            $begin_time = strtotime(date("Y-m-d 0:0:0"));
            $end_time= $begin_time + 60*60*24 -1;
        }
        $order_where['ctime'] = array(array('egt',$begin_time), array('elt',$end_time), 'and');
        $order_where['is_del'] = 0;

        $order_id = M('order')->field('id')->where($order_where)->select();

        $order_ids = 0;
        if($order_id){
            foreach($order_id as $k=>$v){
                $order_ids .= $v['id'].',';
            }
            $order_ids = trim($order_ids, ',');
        }
        $total_count = 0;
        $total_price = 0;
        foreach ($_list as $list_key => $list_one) {
            $_list [$list_key]["cname"] = M('goods_category')->where(array('id' => $list_one['cid']))->getField('name');
            if($order_ids == 0){
                $_list [$list_key]["count"] = 0;
            }else{
                $_list [$list_key]["count"] = M('order_goods')->where(array('order_id' => array('in', $order_ids), 'goods_id' => $list_one['id']))->sum('count');
            }
            $_list [$list_key]["total_price"] = $_list [$list_key]["count"] * $_list [$list_key]["price"] . ' 元';
            $_list [$list_key]["price"] .= ' 元';
            $total_count += $_list [$list_key]["count"];
            $total_price += $_list [$list_key]["total_price"];
        }
        $this->assign('list', $_list);
        $this->assign('begin_time', date('Y-m-d H:i:s', $begin_time));
        $this->assign('end_time', date('Y-m-d H:i:s', $end_time));
        $this->assign('total_count', $total_count);
        $this->assign('total_price', $total_price);
        $this->display();
    }
}