<?php 
/*
 * 用户管理控制器
 * Auth   : Ghj
 * Time   : 2015年10月10日 
 * QQ     : 912524639
 * Email  : 912524639@qq.com
 * Site   : http://guanblog.sinaapp.com/
 */
 
namespace Admin\Controller;

class UserController extends AdminCoreController {
	
    public $Model = null;
	public $ident_words = array('网站管理员', '网站管理员', '普通用户', '业务员');

    protected function _initialize() {
        parent::_initialize();
        $this->Model = D('User');
    }
	
    /* 列表(默认首页)
     * Auth   : Ghj
     * Time   : 2015年10月10日 
     **/
	public function index(){
		if(IS_POST){
			$post_data=I('post.');
			$post_data['first'] = $post_data['rows'] * ($post_data['page'] - 1);
			$map = array();
            $map = $this->_search();
			$total = $this->Model->where($map)->count();
			if($total==0){
				$_list='';
			}else{
				$_list = $this->Model->where($map)->order($post_data['sort'].' '.$post_data['order'])->limit($post_data['first'].','.$post_data['rows'])->select();
			}
            $op_status=R("Admin/Function/get_config",array("USER_STATUS_TYPE|type|title"));
                     foreach($op_status as $op_status_key=>$op_status_one){
                     	$option["status"][$op_status_one["type"]]=$op_status_one["title"];
                     }
                      
          	foreach($_list as $list_key=>$list_one){
                foreach($list_one as $list_one_key=>$list_one_field){
                    if($option[$list_one_key]!=''){
                        $_list[$list_key][$list_one_key]=$option[$list_one_key][$list_one_field];
                    }
                }
                $_list [$list_key]["last_login_time"]=date("Y年m月d日",$_list[$list_key]["last_login_time"]);                $_list [$list_key]["create_time"]=date("Y年m月d日",$_list[$list_key]["create_time"]);                $_list [$list_key]["update_time"]=date("Y年m月d日",$_list[$list_key]["update_time"]);
				$_list [$list_key]["ident"] = $this->ident_words[$_list [$list_key]["ident"]];
				$operate_menu='';
				if(Is_Auth('Admin/User/group')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Submit_Form('User_Form','User_Data_List','" . U ( 'group', array ('id' => $_list [$list_key] ['id'] ) ) . "','','用户授权','你确定要修改此用户的 用户组 ？');\">用户授权</a>";
				}
				if(Is_Auth('Admin/User/edit')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Submit_Form('User_Form','User_Data_List','" . U ( 'edit', array ('id' => $_list [$list_key] ['id'] ) ) . "','','编辑数据','');\">编辑</a>";
				}
				if(Is_Auth('Admin/User/del')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Data_Remove('" . U ( 'del', array ('id' => $_list [$list_key] ['id'] ) ) . "','User_Data_List');\">删除</a>";
				}
				$_list [$list_key] ['operate'] = $operate_menu;
            }
			$data = array('total'=>$total, 'rows'=>$_list);
			$this->ajaxReturn($data);
		}else{
        	$this->display();
		}
	}
	
    /* 搜索
     * Auth   : Ghj
     * Time   : 2015年10月10日 
     **/
	protected function _search() {
		$map = array ();
		$post_data=I('post.');
		/* 名称：用户名 字段：username 类型：string*/
		if($post_data['s_username']!=''){
			$map['username']=array('like', '%'.$post_data['s_username'].'%');
		}
		if($post_data['ident'] > 0){
			$map['ident'] = $post_data['ident'];
		}
		/* 名称：昵称/姓名 字段：nickname 类型：string*/
		if($post_data['s_nickname']!=''){
			$map['nickname']=array('like', '%'.$post_data['s_nickname'].'%');
		}
		/* 名称：上次登录时间 字段：last_login_time 类型：datetime*/
		if($post_data['s_last_login_time_min']!=''){
			$map['last_login_time'][]=array('gt',strtotime($post_data['s_last_login_time_min']));
		}
		if($post_data['s_last_login_time_max']!=''){
			$map['last_login_time'][]=array('lt',strtotime($post_data['s_last_login_time_max']));
		}
		/* 名称：上次登录IP 字段：last_login_ip 类型：string*/
		if($post_data['s_last_login_ip']!=''){
			$map['last_login_ip']=array('like', '%'.$post_data['s_last_login_ip'].'%');
		}
		/* 名称：邮箱 字段：email 类型：string*/
		if($post_data['s_email']!=''){
			$map['email']=array('like', '%'.$post_data['s_email'].'%');
		}
		/* 名称：创建时间 字段：create_time 类型：datetime*/
		if($post_data['s_create_time_min']!=''){
			$map['create_time'][]=array('gt',strtotime($post_data['s_create_time_min']));
		}
		if($post_data['s_create_time_max']!=''){
			$map['create_time'][]=array('lt',strtotime($post_data['s_create_time_max']));
		}
		/* 名称：更新时间 字段：update_time 类型：datetime*/
		if($post_data['s_update_time_min']!=''){
			$map['update_time'][]=array('gt',strtotime($post_data['s_update_time_min']));
		}
		if($post_data['s_update_time_max']!=''){
			$map['update_time'][]=array('lt',strtotime($post_data['s_update_time_max']));
		}
		/* 名称：状态 字段：status 类型：select*/
		if($post_data['s_status']!=''){
			$map['status']=$post_data['s_status'];
		}
		return $map;
	}
    
    /* 添加
     * Auth   : Ghj
     * Time   : 2015年10月10日 
     **/
	public function add(){
		if(IS_POST){
			$post_data=I('post.');
 
			$data=$this->Model->create($post_data);
			if($data){
				$result = $this->Model->add($data);
				if($result){
					action_log('Add_User', 'User', $result);
					$this->success ( "操作成功！",U('index'));
				}else{
					$error = $this->Model->getError();
					$this->error($error ? $error : "操作失败！");
				}
			}else{
                $error = $this->Model->getError();
                $this->error($error ? $error : "操作失败！");
			}
		}else{
        	$this->display();
		}
	}
	
    /* 编辑
     * Auth   : Ghj
     * Time   : 2015年10月10日 
     **/
	public function edit(){
		if(IS_POST){
			$post_data=I('post.');
 
			$data=$this->Model->create($post_data);
			if($data){
				$result = $this->Model->where(array('id'=>$post_data['id']))->save($data);
				if($result){
					action_log('Edit_User', 'User', $post_data['id']);
					$this->success ( "操作成功！",U('index'));
				}else{
					$error = $this->Model->getError();
					$this->error($error ? $error : "操作失败！");
				}
			}else{
                $error = $this->Model->getError();
                $this->error($error ? $error : "操作失败！");
			}
		}else{
			$_info=I('get.');
			$_info = $this->Model->where(array('id'=>$_info['id']))->find();
			$this->assign('_info', $_info);
        	$this->display();
		}
	}
	
    /* 删除
     * Auth   : Ghj
     * Time   : 2015年10月10日 
     **/
	public function del(){
		$id=I('get.id');
		empty($id)&&$this->error('参数不能为空！');
		$res=$this->Model->delete($id);
		if(!$res){
			$this->error($this->Model->getError());
		}else{
			action_log('Del_User', 'User', $id);
			$this->success('删除成功！');
		}
	}
	
    /* 选择用户组
     * Auth   : Ghj
     * Time   : 2015年10月09日 
     **/
	public function group(){
		if(IS_POST){
			$post_data["uid"]=I('post.id');
			$post_data["group"]=I("post.group");
			foreach($post_data["group"] as $group_key=>$group_one){
				$data_ls=D('AuthGroupAccess')->create(array('uid'=>$post_data["uid"],'group_id'=>$group_one));
				$data[]=$data_ls;
			}
			D('AuthGroupAccess')->where(array('uid'=>$post_data["uid"]))->delete();
			if(count($data)>0){
				$result = D('AuthGroupAccess')->addAll($data);
				if($result){
					action_log('User_Group', 'User', $post_data['id']);
					$this->success ( "操作成功！");
				}else{
					$error = D('AuthGroupAccess')->getError();
					$this->error($error ? $error : "操作失败！");
				}
			}else{
				$this->success ( "操作成功！");
			}
		}else{
			$_info=I('get.');
			$group_ids = D('AuthGroupAccess')->where(array('uid'=>$_info['id']))->getField('group_id',true);
			$_group_id=implode(",",$group_ids); 
			$this->assign('_info', $_info);
			$this->assign('_group_id', $_group_id);
        	$this->display();
		}
	}
}