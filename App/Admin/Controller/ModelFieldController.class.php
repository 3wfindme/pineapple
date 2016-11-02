<?php

/*
 * 字段属性控制器
 * Auth : Ghj
 * Time : 2015年4月11日
 * QQ : 912524639
 * Email : 912524639@qq.com
 * Site : http://guanblog.sinaapp.com/
 */
namespace Admin\Controller;

use Common\Controller\AdminCore;

class ModelFieldController extends AdminCoreController {
	private $Model = null;
	private $ModelInfo = null;
    private $modelfield, $fields;
	
	protected function _initialize() {
		parent::_initialize ();
		$this->Model = D ( 'ModelField' );
        //字段类型存放目录
        $this->fields = MODULE_PATH . 'Fields/';
        $modelid = I('get.modelid', 0, 'intval');
	}
	public function index() {
		$app_id = I ( 'get.app_id' );
		if (IS_POST) {
			$post_data = I ( 'post.' );
			$post_data ['first'] = $post_data ['rows'] * ($post_data ['page'] - 1);
			$map = array ();
			$map = $this->_search ();
			$map ['model_id'] = I ( 'get.model_id' );
			$total = $this->Model->where ( $map )->count ();
			if ($total == 0) {
				$_list = '';
			} else {
				$_list = $this->Model->where ( $map )->order ( $post_data ['sort'] . ' ' . $post_data ['order'] )->limit ( $post_data ['first'] . ',' . $post_data ['rows'] )->select ();
			}
			$field_extar ["type"] = array (
					'num' => '数字',
					'string' => '文本框',
					'textarea' => '文本区域',
					'datetime' => '日期时间',
					'select' => '下拉框',
					'checkbox' => '选择',
					'editor' => '编辑器',
					'pictures' => '图片',
					'files' => '附件' 
			);
			$field_extar ["status"] = array (
					0 => '禁用',
					1 => '启用' 
			);
			$field_extar ["validate_time"] = array (
					1 => '新增',
					2 => '修改',
					3 => '全部' 
			);
			$field_extar ["auto_time"] = array (
					1 => '新增',
					2 => '修改',
					3 => '全部' 
			);
			foreach ( $_list as $list_key => $list_one ) {
				foreach ( $list_one as $list_one_key => $list_one_field ) {
					if ($field_extar [$list_one_key] != '') {
						$_list [$list_key] [$list_one_key] = $field_extar [$list_one_key] [$list_one_field];
					}
				}
				$operate_menu='';
				if(Is_Auth('Admin/ModelField/edit')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Submit_Form('ModelField_Form','ModelField_Data_List','" . U ( 'edit', array ('id' => $_list [$list_key] ['id'],'model_id' => I ( 'get.model_id' )) ) . "','','编辑','');\">编辑</a>";
				}
				if(Is_Auth('Admin/ModelField/del')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Data_Remove('" . U ( 'del', array (
'id' => $_list [$list_key] ['id'],'model_id' => I ( 'get.model_id' )) ). "','ModelField_Data_List');\">删除</a>";
				}
				$_list [$list_key] ['operate'] = $operate_menu;
			}
			$data = array (
					'total' => $total,
					'rows' => $_list 
			);
			$this->ajaxReturn ( $data );
		} else {
			$this->assign ( 'operate', $operate );
			$this->assign ( 'app_id', $app_id );
			$this->display ();
		}
	}
	protected function _search() {
		$map = array ();
		$post_data = I ( 'post.' );
		if ($post_data ['s_name'] != '') {
			$map ['name'] = $post_data ['s_name'];
		}
		if ($post_data ['s_title'] != '') {
			$map ['title'] = $post_data ['s_title'];
		}
		if ($post_data ['s_type'] != '') {
			$map ['type'] = $post_data ['s_type'];
		}
		if ($post_data ['s_remark'] != '') {
			$map ['remark'] = $post_data ['s_remark'];
		}
		if ($post_data ['s_status'] != '') {
			$map ['status'] = $post_data ['s_status'];
		}
		return $map;
	}
	public function add() {
		if (IS_POST) {
			$post_data = I ( 'post.' );
			$post_data ['extra'] = I ( 'post.extra' );
			$post_data ['extra'] = serialize ( $post_data ['extra'] );
			if (empty ( $post_data ['id'] ) && empty ( $post_data ['model_id'] )) {
				$this->error ( '参数缺失！' );
			}
			$res = $this->Model->update ( $post_data );
			if (! $res) {
				$this->error ( $this->Model->getError () );
			} else {
				action_log('Add_ModelField', 'ModelField', $result);
				$this->success ( "操作成功！", U('index'));
			}
		} else {
			$model_id = I ( 'get.model_id' );
			$field_sort = $this->Model->where ( array ('model_id' => $model_id) )->order ( 'sort_l desc' )->getField ( 'sort_l' );
			$this->assign ( 'field_sort', $field_sort + 1 );
			$this->assign ( 'model_id', $model_id );
			$this->assign ( 'operate', $operate );
			$this->display ();
		}
	}
	public function edit() {
		if (IS_POST) {
			$id = I ( 'post.id' );
			$model_id = I ( 'post.model_id' );
			if (empty ( $id ) && empty ( $model_id )) {
				$this->error ( '参数缺失！' );
			}
			$post_data = I ( 'post.' );
			$post_data ['extra'] = I ( 'post.extra' );
			$post_data ['extra'] = serialize ( $post_data ['extra'] );
			$res = $this->Model->update ( $post_data );
			if (! $res) {
				$this->error ( $this->Model->getError () );
			} else {
				action_log('Edit_ModelField', 'ModelField', $post_data ['id']);
				$this->success ( "操作成功！", U('index'));
			}
		} else {
			$id = I ( 'get.id' );
			$model_id = I ( 'get.model_id' );
			if (empty ( $id ) && empty ( $model_id )) {
				$this->error ( '参数缺失！' );
			}
			$_info = I ( 'get.' );
			$_info = $this->Model->where ( array (
					'id' => $id 
			) )->find ();
			
			$extra = unserialize ( $_info ['extra'] );
			$fiepath = $this->fields . $_info ['type'] . '/';
			
			ob_start ();
			include $fiepath . "field_edit_form.inc.php";
			$form_data = ob_get_contents ();
			ob_end_clean ();
			
			$this->assign ( '_info', $_info );
			$this->assign ( 'form_data', $form_data );
			$this->display ();
		}
	}
	public function del() {
		$id = I ( 'get.id' );
		empty ( $id ) && $this->error ( '参数错误！' );
		$Model = D ( 'ModelField' );
		$info = $Model->getById ( $id );
		empty ( $info ) && $this->error ( '该字段不存在！' );
		// 删除属性数据
		$res = $Model->delete ( $id );
		// 删除表字段
		$Model->deleteField ( $info );
		if (! $res) {
			$this->error ( D ( 'ModelField' )->getError () );
		} else {
			action_log('Del_ModelField', 'ModelField', $id);
			$this->success ( "操作成功" );
		}
	}
}
