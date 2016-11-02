<?php
/**
 * WEB APP首页
 */
namespace Mobile\Controller;
use Think\Controller;
use \Common\Libs\GoodsCategory;
use WeSDK\wechatCallbackapiTest;
use Vendor\Curl;
use WeSDK\WxPayConfig;

class IndexController extends Controller {

    /**
     * 显示首页
     */
    public function index(){

        $this->close();

        //查询分类
        $GC = new GoodsCategory();
        $category_list = $GC->getList();
        //查询搜索结果

        if($category_list){
            foreach($category_list as $kk=>$vv){
                foreach($category_list[$kk]['children'] as $k=>$v) {
                    $category_list[$kk]['children'][$k]['goods'] = M('goods')->where(array('cid' => $v['id'],
                        'status' => 1))->order('list_order ASC')->select();
                    if ($category_list[$kk]['children'][$k]['goods']) {
                        foreach ($category_list[$kk]['children'][$k]['goods'] as $kkk => $vvv) {
                            $category_list[$kk]['children'][$k]['goods'][$kkk]['url'] = 'http://' . $_SERVER['SERVER_NAME'] . '/Uploads/' . $vvv['pic'];
                        }
                    }
                }
            }
        }

        if($_GET['keywords']) {
            $search_list = M('goods')->where(array('name' => array('like', '%' . $_GET['keywords'] . '%'),
                'status' => 1))->order('list_order ASC')->select();
            if($search_list){
                foreach($search_list as $k=>$v){
                    $search_list[$k]['url'] = 'http://' . $_SERVER['SERVER_NAME'] . '/Uploads/' . $v['pic'];
                }
            }
            $this->assign('search_list', $search_list);
        }
        $this->assign('category_list', $category_list);
        $this->display();
    }

    /**
     * 读取购物车最新菜品信息
     */
    public function loadCartGoodsList(){
        $goods_list = explode('-', trim($_POST['goods_list'], '-'));
        if(count($goods_list) > 0){
            foreach($goods_list as $k=>$v){
                $goods_list[$k] = explode(':', $v);
                $goods_list[$k]['goods_info'] = M('goods')->where(array('id'=>$goods_list[$k][0]))->find();
                $goods_list[$k]['nums'] = $goods_list[$k][1];
                unset($goods_list[$k][0]);
                unset($goods_list[$k][1]);
                if(!$goods_list[$k]['goods_info']){
                    unset($goods_list[$k]);
                }
            }
            $this->ajaxReturn(array('status'=>1, 'data'=>$goods_list, 'info'=>'拉取信息成功!'));

        }else{
            $this->ajaxReturn(array('status'=>0, 'data'=>$goods_list, 'info'=>'拉取信息失败!'));
        }
    }

    /**
     * 获取微信token
     */
    public function verifyToken(){
        require_once 'WeSDK/wechatCallbackapiTest.php';
        //define your token
        define("TOKEN", "haocaiqitaihe");
        $wechatObj = new wechatCallbackapiTest();
        $wechatObj->valid();
    }

    /**
     * 刷新access_token
     */
    public function refreshAccessToken(){
        require_once 'WeSDK/example/WxPay.Config.php';
        //检查access_token是否存在,并且是否过期 ? 存在没过期直接返回
        $wat = M('config')->field('name,create_time,update_time,value')->where(array('name'=>'WECHAT_ACCESS_TOKEN'))->find();
        $wate = M('config')->field('name,create_time,update_time,value')->where(array('name'=>'WECHAT_ACCESS_TOKEN_EXPIRES_IN'))->find();
        $time = time();
        if($wat['value'] && ($wat['update_time']+$wate['value']-60*10) > $time){
            return $wat['value'];
        }else{
            //重新获取access_token,并存储
            $curl = new Curl();
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.WxPayConfig::APPID.'&secret='.WxPayConfig::APPSECRET;
            $return = (array)json_decode($curl->_simple_call('get', $url));
            if($return['access_token']){
                $data['update_time'] = time();
                $data['value'] = $return['access_token'];
                M('config')->where(array('name'=>'WECHAT_ACCESS_TOKEN'))->data($data)->save();
                M('config')->where(array('name'=>'WECHAT_ACCESS_TOKEN_EXPIRES_IN'))->data(array(
                    'update_time'=>$time,
                    'value'=>$return['expires_in']
                    ))->save();
                return $return['access_token'];
            }else{
                $this->error('刷新access_token失败!');
            }
        }
    }

    /**
     * 创建自定义菜单接口
     */
    public function createMenu(){
        require_once 'WeSDK/example/WxPay.Config.php';
        $access_token = $this->refreshAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        $curl = new Curl();
        $body = array('button'=>array(
            array('type'=>'view', 'name'=>'下单购买', 'url'=>'http://www.0464haocai.com'),
            array('type'=>'view', 'name'=>'我的订单', 'url'=>'http://www.0464haocai.com/Mobile/Order/my'),
            array('type'=>'view', 'name'=>'收货地址', 'url'=>'http://www.0464haocai.com/Mobile/Profile/editProfile')
        ));
        dump(json_encode($body));
        $return = $curl->_simple_call('post', $url, json_encode($body));
        dump($return);
    }

    /**
     * 获取openid
     */
    public function getWechatOpenId(){
        $str = "appid=wx1a16aa24768e6a3e&attach=七台河守斌好菜配送&body=支付订单&goods_tag=haocai&mch_id=1298218901&nonce_str=dw5ap5q3p1ytaejavi90rukl920m73ut&notify_url=http://www.0464haocai.com/Mobile/Order/notify&openid=oO4ymwKyldZP2y1AMXgbrwJH47h4&out_trade_no=145069107248&spbill_create_ip=123.122.20.5&time_expire=20151221175432&time_start=20151221174432&total_fee=1&trade_type=JSAPI&key=192006250b4c09247ec02edce69f0464";
        $str = '123456';
        dump(md5(urlencode($str)));
        dump(urlencode("http://www.0464haocai.com/Mobile/Order/notify"));
    }

    /**
     * 站点关闭
     */
    protected function close(){

        $user_info = session('user_info');
        $user_info['uid'];

        if($user_info['uid'] != 1) {
            $info = M('Config')->where(array('name' => 'WEB_SITE_BEGAN_TIME'))->find();
            $time = time();
            $close_time = strtotime(date('Y-m-d 0:0:0'));
            $begin_time = strtotime(date('Y-m-d ' . $info['value'] . ':00'));
            if ($time > $close_time && $time < $begin_time) {
                die('<body style="background: #1abf85;text-align: center;font-family: "microsoft yahei", "黑体""><h1 style="margin-top:30rem;;margin-left:40px;color:#fff;font-size:4rem;">菜品更新中,请稍候访问!</h1></body>');
            }
        }
    }
}