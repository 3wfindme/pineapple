<?php
/*
 * 核心模型控制器
 * Auth : Ghj
 * Time : 2015年07月26日
 * QQ : 912524639
 * Email : 912524639@qq.com
 * Site : http://guanblog.sinaapp.com/
 */
namespace Admin\Controller;

class ModelController extends AdminCoreController {
	
	private $Model = null;
	private $ModelInfo = null;
	private $ModelField = null;
	private $fiepath = null;
	
	protected function _initialize() {
		parent::_initialize ();
		$this->Model = D ( 'Model' );
		$this->fiepath = './App/Admin/Fields/';
	}
	/*
	 * 列表(默认首页)
	 * Auth : Ghj
	 * Time : 2015年07月26日
	 */
	public function index() {
		if (IS_POST) {
			$post_data = I ( 'post.' );
			$post_data ['first'] = $post_data ['rows'] * ($post_data ['page'] - 1);
			$map = array ();
			$map = $this->_search ();
			$total = $this->Model->where ( $map )->count ();
			if ($total == 0) {
				$_list = '';
			} else {
				$_list = $this->Model->where ( $map )->order ( $post_data ['sort'] . ' ' . $post_data ['order'] )->limit ( $post_data ['first'] . ',' . $post_data ['rows'] )->select ();
			}
			$option ["is_extend"] = array (
					1 => '是',
					0 => '否' 
			);
			$op_extend = R ( "Admin/Function/get_extend_model" );
			foreach ( $op_extend as $op_extend_key => $op_extend_one ) {
				$option ["extend"] [$op_extend_one ["id"]] = $op_extend_one ["title"];
			}
			$option ["list_type"] = array (
					0 => '普通',
					1 => '树形' 
			);
			$option ["status"] = array (
					0 => '禁用',
					1 => '启用' 
			);
			$option ["engine_type"] = array (
					'MyISAM' => 'MyISAM',
					'InnoDB' => 'InnoDB',
					'MEMORY' => 'MEMORY',
					'BLACKHOLE' => 'BLACKHOLE',
					'MRG_MYISAM' => 'MRG_MYISAM',
					'ARCHIVE' => 'ARCHIVE' 
			);
			
			foreach ( $_list as $list_key => $list_one ) {
				foreach ( $list_one as $list_one_key => $list_one_field ) {
					if ($option [$list_one_key] != '') {
						$_list [$list_key] [$list_one_key] = $option [$list_one_key] [$list_one_field];
					}
				}
				
				$operate_menu='';
				if(Is_Auth('Admin/Model/build')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Submit_Form('Model_Form','Model_Data_List','" . U ( 'Model/build?model_id=' . $_list [$list_key] ['id'] ) . "','','你确定要生成此模型文件？一旦生成将覆盖原模型文件！','生成文件','');\">生成文件</a>";
				}
				if(Is_Auth('Admin/ModelField/index')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"UpdateTabs('ModelField','" . U ( 'ModelField/index?model_id=' . $_list [$list_key] ['id'] ) . "','字段管理','iconfont icon-viewlist');\">字段管理</a>";
				}
				if(Is_Auth('Admin/Model/edit')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Submit_Form('Model_Form','Model_Data_List','" . U ( 'Model/edit?id=' . $_list [$list_key] ['id'] ) . "','','编辑','');\">编辑</a>";
				}
				if(Is_Auth('Admin/Model/del')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Data_Remove('" . U ( 'del?id=' . $_list [$list_key] ['id'] ) . "','Model_Data_List');\">删除</a>";
				}
				$_list [$list_key] ['operate'] = $operate_menu;
			}
			$data = array (
					'total' => $total,
					'rows' => $_list 
			);
			$this->ajaxReturn ( $data );
		} else {
			$this->display ();
		}
	}
	
	/*
	 * 搜索
	 * Auth : Ghj
	 * Time : 2015年07月26日
	 */
	protected function _search() {
		$map = array ();
		$post_data = I ( 'post.' );
		/* 名称：标识 字段：name 类型：string */
		if ($post_data ['s_name'] != '') {
			$map ['name'] = array (
					'like',
					'%' . $post_data ['s_name'] . '%' 
			);
		}
		/* 名称：名称 字段：title 类型：string */
		if ($post_data ['s_title'] != '') {
			$map ['title'] = array (
					'like',
					'%' . $post_data ['s_title'] . '%' 
			);
		}
		/* 名称：允许子模型 字段：is_extend 类型：select */
		if ($post_data ['s_is_extend'] != '') {
			$map ['is_extend'] = $post_data ['s_is_extend'];
		}
		/* 名称：继承的模型 字段：extend 类型：select */
		if ($post_data ['s_extend'] != '') {
			$map ['extend'] = $post_data ['s_extend'];
		}
		/* 名称：列表类型 字段：list_type 类型：select */
		if ($post_data ['s_list_type'] != '') {
			$map ['list_type'] = $post_data ['s_list_type'];
		}
		/* 名称：状态 字段：status 类型：select */
		if ($post_data ['s_status'] != '') {
			$map ['status'] = $post_data ['s_status'];
		}
		/* 名称：数据库引擎 字段：engine_type 类型：select */
		if ($post_data ['s_engine_type'] != '') {
			$map ['engine_type'] = $post_data ['s_engine_type'];
		}
		return $map;
	}
	
	/*
	 * 添加
	 * Auth : Ghj
	 * Time : 2015年07月26日
	 */
	public function add() {
		if (IS_POST) {
			$post_data = I ( 'post.' );
			$data = $this->Model->create ( $post_data );
			if ($data) {
				$result = $this->Model->update ( $data );
				if ($result) {
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
	 * Time : 2015年07月26日
	 */
	public function edit() {
		if (IS_POST) {
			$post_data = I ( 'post.' );
			
			$data = $this->Model->create ( $post_data );
			if ($data) {
				$result = $this->Model->update ( $data );
				if ($result) {
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
			$_info = $this->Model->where ( array ('id' => $_info ['id'] ) )->find ();
			$this->assign ( '_info', $_info );
			$this->display ();
		}
	}
	
	/*
	 * 删除
	 * Auth : Ghj
	 * Time : 2015年07月26日
	 */
	public function del() {
		$id = I ( 'get.id' );
		empty ( $id ) && $this->error ( '参数不能为空！' );
		$res = $this->Model->del ( $id );
		print_r($res);
		dump($res);
		if (! $res) {
			$this->error ( $this->Model->getError () );
		} else {
			$this->success ( '删除成功！' );
		}
	}
	
	/*
	 * 系统化数据模型
	 * Auth : Ghj
	 * Time : 2015年10月11日
	 */
	public function generate() {
		if (! IS_POST) {
			// 获取所有的数据表
			$tables = D ( 'Model' )->getTables ();
			$this->assign ( 'tables', $tables );
			$this->display ();
		} else {
			$table = I ( 'post.table' );
			empty ( $table ) && $this->error ( '请选择要生成的数据表！' );
			$res = D ( 'Model' )->generate ( $table, I ( 'post.name' ), I ( 'post.title' ) );
			if ($res) {
				$this->success ( '生成模型成功！', U ( 'index' ) );
			} else {
				$this->error ( D ( 'Model' )->getError () );
			}
		}
	}
	
	/*
	 * 生成文件
	 * Auth : Ghj
	 * Time : 2015年10月11日
	 */
	public function build() {
		if (IS_POST) {
			//获取模型ID
			$map ['id'] = I ( 'post.model_id', 0 );
			if ($map ['id'] == 0) {
				$this->error ( '请选择模型' );
			}
			//获取模型信息
			$BM_Info = $this->Model->where ( $map )->find ();
			//将模信息存至共有变量
			$this->ModelInfo = $BM_Info;
			//获取模型字段列表
			$this->ModelField = M ( 'ModelField' )->where ( array ('model_id' => $BM_Info ['id'] ) )->select ();
			//获取表单提交的生成文件需求信息
			$build = I ( 'post.' );
			//生成控制器
			if ($build ['build_action'] == 1) {$this->build_action ();}
			//生成模型
			if ($build ['build_model'] == 1) {$this->build_model ();}
			//生成菜单
			if ($build ['build_rule'] == 1) {$this->build_rule ();}
			//生成index页面
			if ($build ['build_tpl_index'] == 1) {$this->build_tpl_index ();}
			//生成add页面
			if ($build ['build_tpl_add'] == 1) {$this->build_tpl_add ();}
			//生成edit页面
			if ($build ['build_tpl_edit'] == 1) {$this->build_tpl_edit ();}
			//过程中无错误返回 成功信息
			$this->success ( '生成文件成功！', U ( 'index' ) );
		} else {
			//获取模型ID
			$model_id = I ( 'get.model_id', '' );
			if (empty ( $model_id )) {
				$this->error ( '参数不能为空！' );
			}
			//变量替换
			$this->assign ( 'model_id', $model_id );
			//解析页面
			$this->display ();
		}
	}
	
	/*
	 * 生成控制器文件
	 * Auth : Ghj
	 * Time : 2015年10月11日
	 */
	protected function build_action() {
		//设置生成文件的路径 末前支持在Admin模块下的生成
		$file = APP_PATH . "Admin/Controller/" . $this->ModelInfo ['name'] . "Controller.class.php";
		//停止视图布局
		layout ( false );
		
		//获取在BULID函数中设置的字段信息
		$ModelField = $this->ModelField; // 获取字段信息
		//对字段信息进行二维数组排序 根据sort_l和id 来排序
		$sort = array ();
		$ids = array ();
		foreach ( $ModelField as $one ) {
			$sort [] = $one ['sort_l'];
			$ids [] = $one ['id'];
		}
		array_multisort ( $sort, SORT_ASC, $ids, SORT_ASC, $ModelField );
		$G_from_type = 'index';
		foreach ( $ModelField as $key => $field_one ) {
			if ($field_one ['sort_l'] > 0 && $field_one ['status'] > 0) {
				$_List [$field_one ['name']] = $field_one;
			}
		}
		//列表显示字段变量替换
		$this->assign ( '_List', $_List );
		//=========================================================================================
		//获取在BULID函数中设置的字段信息
		$ModelField = $this->ModelField; // 获取字段信息
		//对字段信息进行二维数组排序 根据sort_s和id 来排序
		$sort = array ();
		$ids = array ();
		foreach ( $ModelField as $one ) {
			$sort [] = $one ['sort_s'];
			$ids [] = $one ['id'];
		}
		array_multisort ( $sort, SORT_ASC, $ids, SORT_ASC, $ModelField );
		$G_from_type = 'search';
		foreach ( $ModelField as $key => $field_one ) {
			if ($field_one ['sort_s'] > 0 && $field_one ['status'] > 0) {
				$_Search [$field_one ['name']] = $field_one;
			}
		}
		//搜索显示字段变量替换
		$this->assign ( '_Search', $_Search );
		//=========================================================================================
		//获取在BULID函数中设置的字段信息
		$ModelField = $this->ModelField; // 获取字段信息
		//对字段信息进行二维数组排序 根据sort_a和id 来排序
		$sort = array ();
		$ids = array ();
		foreach ( $ModelField as $one ) {
			$sort [] = $one ['sort_a'];
			$ids [] = $one ['id'];
		}
		array_multisort ( $sort, SORT_ASC, $ids, SORT_ASC, $ModelField );
		foreach ( $ModelField as $key => $field_one ) {
			if ($field_one ['sort_a'] > 0 && $field_one ['status'] > 0) {
				$_Add [$field_one ['name']] = $field_one;
			}
		}
		//新增显示字段变量替换
		$this->assign ( '_Add', $_Add );
		//=========================================================================================
		//获取在BULID函数中设置的字段信息
		$ModelField = $this->ModelField; // 获取字段信息
		//对字段信息进行二维数组排序 根据sort_a和id 来排序
		$sort = array ();
		$ids = array ();
		foreach ( $ModelField as $one ) {
			$sort [] = $one ['sort_'];
			$ids [] = $one ['id'];
		}
		array_multisort ( $sort, SORT_ASC, $ids, SORT_ASC, $ModelField );
		foreach ( $ModelField as $key => $field_one ) {
			if ($field_one ['sort_'] > 0 && $field_one ['status'] > 0) {
				$_Edit [$field_one ['name']] = $field_one;
			}
		}
		//新增显示字段变量替换
		$this->assign ( '_Edit', $_Edit );
		//=========================================================================================
		//模型的配置信息变量替换
		$this->assign ( 'ModelInfo', $this->ModelInfo );
		//根据模型的列表结构决定控制器的模版文件
		if ($this->ModelInfo ['list_type'] == 0) {//普通表格
			//解析但不输出页面
			$content = $this->fetch ( 'build_controller' );
		} else {
			//解析但不输出页面
			$content = $this->fetch ( 'build_controller' . $this->ModelInfo ['list_type'] );//普通表格树形表格
		}
		//将上一步解析的模版文件存至 文件
		file_put_contents ( $file, '<?php ' . $content );
	}
	
	/*
	 * 生成模型文件
	 * Auth : Ghj
	 * Time : 2015年10月11日
	 */
	protected function build_model() {
		//设置生成文件的路径 末前支持在Admin模块下的生成
		$file = APP_PATH . "Admin/Model/" . $this->ModelInfo ['name'] . "Model.class.php";
		//停止视图布局
		layout ( false );
		//模型的配置信息变量替换
		$this->assign ( 'ModelInfo', $this->ModelInfo );
		//获取在BULID函数中设置的字段信息
		$ModelField = $this->ModelField; // 获取字段信息
		//字段信息变量替换
		$this->assign ( 'ModelField', $ModelField );
		//解析但不输出页面
		$content = $this->fetch ( 'build_model' );
		//将上一步解析的模版文件存至 文件
		file_put_contents ( $file, '<?php ' . $content );
	}
	
	/*
	 * 生成菜单
	 * Auth : Ghj
	 * Time : 2015年10月11日
	 */
	protected function build_rule() {
		//这只是一个为了方便写完模型后不用手动添加重复菜单的函数 实际上 他还待扩
		//获取在BULID函数中设置的模型
		$ModelInfo = $this->ModelInfo;
		$id = M ( 'AuthRule' )->add ( array ('pid' => 0,'name' => 'Admin/' . $ModelInfo ['name'] . '/index','title' => $ModelInfo ['title'],'hide' => 0,'sort' => 1) );
		M ( 'AuthRule' )->add ( array ('pid' => $id,'name' => 'Admin/' . $ModelInfo ['name'] . '/add','title' => '新增','hide' => 0,'sort' => 1) );
		M ( 'AuthRule' )->add ( array ('pid' => $id,'name' => 'Admin/' . $ModelInfo ['name'] . '/edit','title' => '编辑','hide' => 0,'sort' => 2) );
		M ( 'AuthRule' )->add ( array ('pid' => $id,'name' => 'Admin/' . $ModelInfo ['name'] . '/del','title' => '删除','hide' => 1,'sort' => 3) );
	}
	
	/**
	 * 生成模型列表页面
	 */
	protected function build_tpl_index() {
		//设置生成文件的路径 末前支持在Admin模块下的生成
		$path = APP_PATH . "Admin/View/" . $this->ModelInfo ['name'] . "/";
		//查看目录路径是否存在 不存在就创建一个文件夹
		if (! file_exists ( $path )) {
			mkdir ( $path );
		}
		//停止视图布局
		layout ( false );
		//获取在BULID函数中设置的字段信息
		$ModelField = $this->ModelField; // 获取字段信息
		//对字段信息进行二维数组排序 根据sort_l和id 来排序
		$sort = array ();
		$ids = array ();
		foreach ( $ModelField as $one ) {
			$sort [] = $one ['sort_l'];
			$ids [] = $one ['id'];
		}
		array_multisort ( $sort, SORT_ASC, $ids, SORT_ASC, $ModelField );
		$G_from_type = 'index';
		foreach ( $ModelField as $key => $field_one ) {
			if ($field_one ['sort_l'] > 0 && $field_one ['status'] > 0) {
				$_List [$field_one ['name']] = $field_one;
			}
		}
		//列表显示字段变量替换
		$this->assign ( '_List', $_List );
		
		//获取在BULID函数中设置的字段信息
		$ModelField = $this->ModelField; // 获取字段信息
		//对字段信息进行二维数组排序 根据sort_s和id 来排序
		$sort = array ();
		$ids = array ();
		foreach ( $ModelField as $one ) {
			$sort [] = $one ['sort_s'];
			$ids [] = $one ['id'];
		}
		array_multisort ( $sort, SORT_ASC, $ids, SORT_ASC, $ModelField );
		$G_from_type = 'search';
		foreach ( $ModelField as $key => $field_one ) {
			if ($field_one ['sort_s'] > 0 && $field_one ['status'] > 0) {
				$_Search [$field_one ['name']] ['title'] = $field_one ['title'];
				$_Search [$field_one ['name']] ['form'] = include $this->fiepath . $field_one ['type'] . "/form.inc.php";
			}
		}
		//搜索显示字段变量替换
		$this->assign ( '_Search', $_Search );
		
		//模型的配置信息变量替换
		$this->assign ( 'ModelInfo', $this->ModelInfo );
		//根据模型的列表结构决定控制器的模版文件
		if ($this->ModelInfo ['list_type'] == 0) {//普通表格
			//解析但不输出页面
			$content = $this->fetch ( 'build_index' );
		} else {//普通表格树形表格
			//解析但不输出页面
			$content = $this->fetch ( 'build_index' . $this->ModelInfo ['list_type'] );
		}
		//将上一步解析的模版文件存至 文件
		file_put_contents ( $path . 'index.html', $content );
	}
	
	/**
	 * 生成模型新增页面
	 */
	protected function build_tpl_add() {
		$path = APP_PATH . "Admin/View/" . $this->ModelInfo ['name'] . "/";
		if (! file_exists ( $path )) {
			mkdir ( $path );
		}
		layout ( false );
		$ModelField = $this->ModelField; // 获取字段信息
		                                 
		// 将列表的字段信息载入
		$sort = array ();
		$ids = array ();
		foreach ( $ModelField as $one ) {
			$sort [] = $one ['sort_a'];
			$ids [] = $one ['id'];
		}
		
		array_multisort ( $sort, SORT_ASC, $ids, SORT_ASC, $ModelField );
		$G_from_type = 'add';
		foreach ( $ModelField as $key => $field_one ) {
			if ($field_one ['sort_a'] > 0 && $field_one ['status'] > 0) {
				$Form[$field_one ['name']] ['name'] = $field_one ['name'];
				$Form[$field_one ['name']] ['title'] = $field_one ['title'];
				$Form[$field_one ['name']] ['type'] = $field_one ['type'];
				$Form[$field_one ['name']] ['form'] = include $this->fiepath . $field_one ['type'] . "/form.inc.php";
				$Form[$field_one ['name']] ['remark'] = $field_one ['remark'];
			}
		}
		$this->assign ( 'Form', $Form );
		$this->assign ( 'ModelInfo', $this->ModelInfo ); // 将模型的配置信息载入
		// 渲染模版但不输出
		$content = $this->fetch ( 'build_add' );
		// 将渲染结果存入文件
		file_put_contents ( $path . 'add.html', $content );
	}
	
	/**
	 * 生成模型更改页面
	 */
	protected function build_tpl_edit() {
		$path = APP_PATH . "Admin/View/" . $this->ModelInfo ['name'] . "/";
		if (! file_exists ( $path )) {
			mkdir ( $path );
		}
		layout ( false );
		$ModelField = $this->ModelField; // 获取字段信息
		                                 
		// 将列表的字段信息载入
		$sort = array ();
		$ids = array ();
		foreach ( $ModelField as $one ) {
			$sort [] = $one ['sort_e'];
			$ids [] = $one ['id'];
		}
		
		array_multisort ( $sort, SORT_ASC, $ids, SORT_ASC, $ModelField );
		$G_from_type = 'edit';
		foreach ( $ModelField as $key => $field_one ) {
			if ($field_one ['sort_a'] > 0 && $field_one ['status'] > 0) {
				$Form[$field_one ['name']] ['name'] = $field_one ['name'];
				$Form[$field_one ['name']] ['title'] = $field_one ['title'];
				$Form[$field_one ['name']] ['type'] = $field_one ['type'];
				$Form[$field_one ['name']] ['form'] = include $this->fiepath . $field_one ['type'] . "/form.inc.php";
				$Form[$field_one ['name']] ['remark'] = $field_one ['remark'];
			}
		}
		$this->assign ( 'Form', $Form );
		$this->assign ( 'ModelInfo', $this->ModelInfo ); // 将模型的配置信息载入
		                                                 
		// 渲染模版但不输出
		$content = $this->fetch ( 'build_edit' );
		// 将渲染结果存入文件
		file_put_contents ( $path . 'edit.html', $content );
	}
}