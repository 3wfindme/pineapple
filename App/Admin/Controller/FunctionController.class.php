<?php

/*
 * 公共方法控制器
 * Auth : Ghj
 * Time : 2015年4月11日
 * QQ : 912524639
 * Email : 912524639@qq.com
 * Site : http://guanblog.sinaapp.com/
 */
namespace Admin\Controller;
use Common\Controller\CoreController;

class FunctionController extends CoreController {
	
	//核心继承
    protected function _initialize() {
		//继承CoreController的初始化函数
        parent::_initialize();
		if(session(C('AUTH_KEY'))){
			return false;
		}
	}
	
	/*
	 * 根据传入config的名称获取config然后返回
	 * Auth : Ghj
	 * Time : 2015年4月16日
	 */
	public function get_config($cname = '') {
		if ($cname == '') {
			$cname = I ( 'get.cname' );
		}
		$extra_option_arr = explode ( '|', $cname );
		$ops = C ( $extra_option_arr [0] );
		if($extra_option_arr[1]==''){
			$ops_arr = explode ( '|', $ops );
			$data_ls ['type'] = '';
			$data_ls ['value'] = '请选择一个选项';
			$data [] = $data_ls;
			foreach ( $ops_arr as $ops_arr_val ) {
				$val_ls = explode (':',$ops_arr_val );
				$data_ls ['type'] = $val_ls[0];
				$data_ls ['value'] = $val_ls[1];
				$data [] = $data_ls;
			}
		}else{
			$data_ls [$extra_option_arr [1]] = '';
			$data_ls [$extra_option_arr [2]] = '请选择一个选项';
			$data [] = $data_ls;
			$ops_arr = explode ( '|', $ops );
			foreach ( $ops_arr as $ops_arr_val ) {
				$val_ls = explode (':',$ops_arr_val );
				$data_ls [$extra_option_arr [1]] = $val_ls[0];
				$data_ls [$extra_option_arr [2]] = $val_ls[1];
				$data [] = $data_ls;
			}
		}
		$r_type = I ( 'get.r_type' );
		if ($r_type == 'json') {
			$this->ajaxReturn ( $data );
		} else {
			return $data;
		}
	}
	
	/*
	 * 获取用户组树
	 * Auth : Ghj
	 * Time : 2015年4月16日
	 */
	public function get_auth_group($_pid = '0') {
		if ($_pid == '0') {
			$_pid = I ( 'get.pid', 0 );
		}
		$map ['status'] = 1;
		$_list = M ( 'AuthGroup' )->where ( $map )->order ( 'id asc' )->field ( 'id,title as text' )->select ();
		$r_type = I ( 'get.r_type' );
		if ($r_type == 'json') {
			$this->ajaxReturn ( $_list );
		} else {
			return $_list;
		}
	}
	
	/*
	 * 获取节点树
	 * Auth : Ghj
	 * Time : 2015年4月16日
	 */
	public function get_auth_rule($_pid = '0') {
		if ($_pid == '0') {
			$_pid = I ( 'get.pid', 0 );
		}
		$map ['status'] = 1;
		$_list = M ( 'AuthRule' )->where ( $map )->order ( 'sort asc' )->getField ( 'id,pid,title as text' );
		if ($_pid == '-1') {
			$_list [] = array (
					'id' => '0',
					'pid' => '-1',
					'text' => '请选择' 
			);
			$data = list_to_tree ( $_list, 'id', 'pid', 'children', '-1' );
		} else {
			$data = list_to_tree ( $_list, 'id', 'pid', 'children' );
		}
		$r_type = I ( 'get.r_type' );
		if ($r_type == 'json') {
			echo json_encode ( $data );
		} else {
			return $data;
		}
	}
	
	/*
	 * 获取指定字段类型配置文件
	 * Auth : Ghj
	 * Time : 2015年4月16日
	 */
	public function field_setting($fieldtype = '') {
		if ($fieldtype == '') {
			$fieldtype = I ( 'get.fieldtype', 'num' );
		}
		$f_type = I ( 'get.f_type', 'add' );
		$field_config = C ( 'FIELD_LIST' ); // 获取字段类型列表
		$field = $field_config [$fieldtype] ['field'];
		$fiepath = MODULE_PATH . 'Fields/' . $fieldtype . '/';
		ob_start ();
		include $fiepath . "field_" . $f_type . "_form.inc.php";
		$data_setting = $data_setting . ob_get_contents ();
		ob_end_clean ();
		$settings = array (
				'field' => $field,
				'extra' => $data_setting 
		);
		$r_type = I ( 'get.r_type' );
		if ($r_type == 'json') {
			echo json_encode ( $settings );
		} else {
			return $settings;
		}
	}
	/*
	 * 根据传入ID获取字段配置中的参数中的option，并且解析
	 * Auth : Ghj
	 * Time : 2015年4月16日
	 */
	public function get_field_option($f_id = '') {
		if ($f_id == '') {
			$f_id = I ( 'get.f_id', 0 );
		}
		$extra_ys = M ( 'ModelField' )->where ( 'id=' . $f_id )->getField ( 'extra' );
		$extra = unserialize ( $extra_ys );
		$ops = model_field_attr ( $extra ['option'] );
		foreach ( $ops as $opkey => $opkeyval ) {
			$data_ls ['key'] = $opkey;
			$data_ls ['val'] = $opkeyval;
			$data [] = $data_ls;
		}
		$r_type = I ( 'get.r_type' );
		if ($r_type == 'json') {
			$this->ajaxReturn ( $data );
		} else {
			return $data;
		}
	}
	/*
	 * 获取允许子模型的模型
	 * Auth : Ghj
	 * Time : 2015年4月16日
	 */
	public function get_extend_model() {
		$map ['status'] = 1;
		$map ['is_extend'] = 1;
		$map ['extend'] = 0;
		$_list = M ( 'Model' )->where ( $map )->order ( 'sort asc' )->getField ( 'id,title' );
		$_list [0] = array (
				'id' => '0',
				'title' => '独立模型' 
		);
		$r_type = I ( 'get.r_type' );
		if ($r_type == 'json') {
			$this->ajaxReturn ( $_list );
		} else {
			return $_list;
		}
	}
}
