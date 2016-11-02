<?php

/*
 * 菜单模型控制器
 * Auth : Ghj
 * Time : 2015年07月01日
 * QQ : 912524639
 * Email : 912524639@qq.com
 * Site : http://guanblog.sinaapp.com/
 */
namespace Admin\Controller;

class AuthRuleController extends AdminCoreController {
	public $Model = null;
	protected function _initialize() {
		parent::_initialize ();
		$this->Model = D ( 'AuthRule' );
	}
	
	/*
	 * 列表(默认首页)
	 * Auth : Ghj
	 * Time : 2015年07月01日
	 */
	public function index() {
		if (IS_POST) {
			$map = array ();
			$_list = $this->Model->where ( $map )->order ( 'sort asc' )->getField ( 'id,pid,name,title,icon,hide,status,sort' );
			$option ["hide"] = array (1 => '隐藏',0 => '显示');
			$option ["status"] = array (1 => '启用',0 => '禁用');
			foreach ( $_list as $list_key => $list_one ) {
				foreach ( $list_one as $list_one_key => $list_one_field ) {
					if ($option [$list_one_key] != '') {
						$_list [$list_key] [$list_one_key] = $option [$list_one_key] [$list_one_field];
					}
				}
				$operate_menu='';
				if(Is_Auth('Admin/AuthRule/add')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"UpdateTabs('AuthRule','" . U ( 'add', array ('pid' => $_list [$list_key] ['id'] ) ) . "','','iconfont icon-add');\">新增子菜单</a>";
				}
				if(Is_Auth('Admin/AuthRule/edit')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"UpdateTabs('AuthRule','" . U ( 'edit', array ('id' => $_list [$list_key] ['id'] ) ) . "','','iconfont icon-edit');\">编辑</a>";
				}
				if(Is_Auth('Admin/AuthRule/del')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Data_Remove('" . U ( 'del', array ('id' => $_list [$list_key] ['id'] ) ) . "','AuthRule_Data_List');\">删除</a>";
				}
				$_list [$list_key] ['operate'] = $operate_menu;
			}
			$data = list_to_tree ( $_list, 'id', 'pid', 'children' );
			$this->ajaxReturn ( $data );
		} else {
			$this->display ();
		}
	}
	
	/*
	 * 添加
	 * Auth : Ghj
	 * Time : 2015年07月01日
	 */
	public function add() {
		if (IS_POST) {
			$post_data = I ( 'post.' );
			
			$data = $this->Model->create ( $post_data );
			if ($data) {
				$result = $this->Model->add ( $data );
				if ($result) {
					action_log('Add_AuthRule', 'AuthRule', $result);
					$this->success ( "操作成功！", U ( 'index' ) );
				} else {
					$error = $this->Model->getError ();
					$this->error ( $error ? $error : "操作失败！" );
				}
			} else {
				$error = $this->Model->getError ();
				$this->error ( $error ? $error : "操作失败！" );
			}
		} else {
			$this->display ();
		}
	}
	
	/*
	 * 编辑
	 * Auth : Ghj
	 * Time : 2015年07月01日
	 */
	public function edit() {
		if (IS_POST) {
			$post_data = I ( 'post.' );
			
			$data = $this->Model->create ( $post_data );
			if ($data) {
				$result = $this->Model->where ( array (
						'id' => $post_data ['id'] 
				) )->save ( $data );
				if ($result) {
					action_log('Edit_AuthRule', 'AuthRule', $post_data['id']);
					$this->success ( "操作成功！", U ( 'index' ) );
				} else {
					$error = $this->Model->getError ();
					$this->error ( $error ? $error : "操作失败！" );
				}
			} else {
				$error = $this->Model->getError ();
				$this->error ( $error ? $error : "操作失败！" );
			}
		} else {
			$_info = I ( 'get.' );
			$_info = $this->Model->where ( array (
					'id' => $_info ['id'] 
			) )->find ();
			$this->assign ( '_info', $_info );
			$this->display ();
		}
	}
	
	/*
	 * 删除
	 * Auth : Ghj
	 * Time : 2015年07月01日
	 */
	public function del() {
		$id = I ( 'get.id' );
		empty ( $id ) && $this->error ( '参数不能为空！' );
		$res = $this->Model->delete ( $id );
		if (! $res) {
			$this->error ( $this->Model->getError () );
		} else {
			action_log('Del_AuthRule', 'AuthRule', $id);
			$this->success ( '删除成功！' );
		}
	}
	
}