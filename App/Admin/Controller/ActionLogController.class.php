<?php 
/*
 * 日志管理控制器
 * Auth   : Ghj
 * Time   : 2015年10月09日 
 * QQ     : 912524639
 * Email  : 912524639@qq.com
 * Site   : http://guanblog.sinaapp.com/
 */
 
namespace Admin\Controller;

class ActionLogController extends AdminCoreController {
	
    public $Model = null;

    protected function _initialize() {
        parent::_initialize();
        $this->Model = D('ActionLog');
    }
	
    /* 列表(默认首页)
     * Auth   : Ghj
     * Time   : 2015年10月09日 
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
             
			$option ["status"] = array (
					0 => '禁用',
					1 => '启用' 
			);
          	foreach($_list as $list_key=>$list_one){
                foreach($list_one as $list_one_key=>$list_one_field){
                    if($option[$list_one_key]!=''){
                        $_list[$list_key][$list_one_key]=$option[$list_one_key][$list_one_field];
                    }
                }
				$_list[$list_key]['action_title']=get_action($_list [$list_key]['action_id'],'title');
				$_list[$list_key]['user_nickname']=get_nickname($_list [$list_key]['user_id']);
				$_list [$list_key] ["create_time"] = date ( "Y年m月d日", $_list [$list_key] ["create_time"] );
				$operate_menu='';
				if(Is_Auth('Admin/ActionLog/edit')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Submit_Form('ActionLog_Form','ActionLog_Data_List','" . U ( 'edit', array ('id' => $_list [$list_key] ['id'] ) ) . "','','编辑数据','');\">查看行为日志</a>";
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
     * Time   : 2015年10月09日 
     **/
	protected function _search() {
		$map = array ();
		$post_data=I('post.');
		/* 名称：行为id 字段：action_id 类型：string*/
		if($post_data['s_action_id']!=''){
			$map['action_id']=array('like', '%'.$post_data['s_action_id'].'%');
		}
		/* 名称：执行用户id 字段：user_id 类型：string*/
		if($post_data['s_user_id']!=''){
			$map['user_id']=array('like', '%'.$post_data['s_user_id'].'%');
		}
		/* 名称：执行行为者ip 字段：action_ip 类型：string*/
		if($post_data['s_action_ip']!=''){
			$map['action_ip']=array('like', '%'.$post_data['s_action_ip'].'%');
		}
		/* 名称：触发行为的表 字段：model 类型：string*/
		if($post_data['s_model']!=''){
			$map['model']=array('like', '%'.$post_data['s_model'].'%');
		}
		/* 名称：触发行为的数据id 字段：record_id 类型：string*/
		if($post_data['s_record_id']!=''){
			$map['record_id']=array('like', '%'.$post_data['s_record_id'].'%');
		}
		/* 名称：日志备注 字段：remark 类型：string*/
		if($post_data['s_remark']!=''){
			$map['remark']=array('like', '%'.$post_data['s_remark'].'%');
		}
		/* 名称：状态 字段：status 类型：string*/
		if($post_data['s_status']!=''){
			$map['status']=array('like', '%'.$post_data['s_status'].'%');
		}
		/* 名称：执行行为的时间 字段：create_time 类型：string*/
		if($post_data['s_create_time']!=''){
			$map['create_time']=array('like', '%'.$post_data['s_create_time'].'%');
		}
		return $map;
	}
    
    /**
     * 查看行为日志
     */
    public function edit($id = 0){
		if(!IS_POST){
			empty($id) && $this->error('参数错误！');
			$_info = M('ActionLog')->field(true)->find($id);
			$this->assign('_info', $_info);
			$this->display();
		}else{
            $this->error('系统禁止修改日志！');
		}
    }

    /**
     * 清空日志
     */
    public function del(){
        $res = M('ActionLog')->where('1=1')->delete();
        if($res !== false){
            $this->success('日志清空成功！');
        }else {
            $this->error('日志清空失败！');
        }
    }
}