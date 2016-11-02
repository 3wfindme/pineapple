<?php 
/*
 * 行为管理控制器
 * Auth   : Ghj
 * Time   : 2015年10月10日 
 * QQ     : 912524639
 * Email  : 912524639@qq.com
 * Site   : http://guanblog.sinaapp.com/
 */
 
namespace Admin\Controller;

class ActionController extends AdminCoreController {
	
    public $Model = null;

    protected function _initialize() {
        parent::_initialize();
        $this->Model = D('Action');
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
            $op_type=R("Admin/Function/get_config",array("ACTION_TYPE|type|title"));
                     foreach($op_type as $op_type_key=>$op_type_one){
                     	$option["type"][$op_type_one["type"]]=$op_type_one["title"];
                     }
                     $option["status"]=array (
  0 => '禁用',
  1 => '启用',
);
                     
          	foreach($_list as $list_key=>$list_one){
                foreach($list_one as $list_one_key=>$list_one_field){
                    if($option[$list_one_key]!=''){
                        $_list[$list_key][$list_one_key]=$option[$list_one_key][$list_one_field];
                    }
                }
                $_list [$list_key]["update_time"]=date("Y年m月d日",$_list[$list_key]["update_time"]); 
				$operate_menu='';
				if(Is_Auth('Admin/Action/edit')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Submit_Form('Action_Form','Action_Data_List','" . U ( 'edit', array ('id' => $_list [$list_key] ['id'] ) ) . "','','编辑数据','');\">编辑</a>";
				}
				if(Is_Auth('Admin/Action/del')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Data_Remove('" . U ( 'del', array ('id' => $_list [$list_key] ['id'] ) ) . "','Action_Data_List');\">删除</a>";
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
		/* 名称：标识 字段：name 类型：string*/
		if($post_data['s_name']!=''){
			$map['name']=array('like', '%'.$post_data['s_name'].'%');
		}
		/* 名称：标题 字段：title 类型：string*/
		if($post_data['s_title']!=''){
			$map['title']=array('like', '%'.$post_data['s_title'].'%');
		}
		/* 名称：描述 字段：remark 类型：textarea*/
		if($post_data['s_remark']!=''){
			$map['remark']=array('like', '%'.$post_data['s_remark'].'%');
		}
		/* 名称：类型 字段：type 类型：select*/
		if($post_data['s_type']!=''){
			$map['type']=$post_data['s_type'];
		}
		/* 名称：状态 字段：status 类型：select*/
		if($post_data['s_status']!=''){
			$map['status']=$post_data['s_status'];
		}
		/* 名称：修改时间 字段：update_time 类型：datetime*/
		if($post_data['s_update_time_min']!=''){
			$map['update_time'][]=array('gt',strtotime($post_data['s_update_time_min']));
		}
		if($post_data['s_update_time_max']!=''){
			$map['update_time'][]=array('lt',strtotime($post_data['s_update_time_max']));
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
					$this->Model->cache();
					action_log('Add_Action', 'Action', $result);
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
					$this->Model->cache();
					action_log('Edit_Action', 'Action', $post_data['id']);
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
			$this->Model->cache();
			action_log('Del_Action', 'Action', $id);
			$this->success('删除成功！');
		}
	}
}