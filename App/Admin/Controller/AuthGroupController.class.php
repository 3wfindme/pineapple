<?php 
/*
 * 用户组管理控制器
 * Auth   : Ghj
 * Time   : 2015年10月10日 
 * QQ     : 912524639
 * Email  : 912524639@qq.com
 * Site   : http://guanblog.sinaapp.com/
 */
 
namespace Admin\Controller;

class AuthGroupController extends AdminCoreController {
	
    public $Model = null;

    protected function _initialize() {
        parent::_initialize();
        $this->Model = D('AuthGroup');
    }
	
    /* 列表(默认首页)
     * Auth   : Ghj
     * Time   : 2015年10月10日 
     **/
	public function index(){
		if(IS_POST){
			$map = array();
			$_list = $this->Model->where($map)->order('sort asc')->getField('id,title,status,sort');
            
            $op_status=R("Admin/Function/get_config",array("USERGROUP_STATUS_TYPE|status|title"));
                     foreach($op_status as $op_status_key=>$op_status_one){
                     	$option["status"][$op_status_one["status"]]=$op_status_one["title"];
                     }
                      
          	foreach($_list as $list_key=>$list_one){
				foreach($list_one as $list_one_key=>$list_one_field){
					if($option[$list_one_key]!=''){
						$_list[$list_key][$list_one_key]=$option[$list_one_key][$list_one_field];
					}
				}
 
				$operate_menu='';
				if(Is_Auth('Admin/AuthGroup/edit')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Submit_Form('AuthGroup_Form','AuthGroup_Data_List','" . U ( 'edit', array ('id' => $_list [$list_key] ['id'] ) ) . "','','编辑数据','');\">编辑</a>";
				}
				if(Is_Auth('Admin/AuthGroup/del')){
					$operate_menu = $operate_menu."<a href='#' onclick=\"Data_Remove('" . U ( 'del', array ('id' => $_list [$list_key] ['id'] ) ) . "','AuthGroup_Data_List');\">删除</a>";
				}
				$_list [$list_key] ['operate'] = $operate_menu;
            }
			$data = list_to_tree($_list, 'id', 'pid','children');
			$this->ajaxReturn ( $data );
		}else{
        	$this->display();
		}
	}
	
    /* 添加
     * Auth   : Ghj
     * Time   : 2015年10月10日 
     **/
	public function add(){
		if(IS_POST){
			$post_data=I('post.');
$post_data["rules"]=I("post.rules");$post_data["rules"]=implode(",",$post_data["rules"]); 
			$data=$this->Model->create($post_data);
			if($data){
				$result = $this->Model->add($data);
				if($result){
					action_log('Add_AuthGroup', 'AuthGroup', $result);
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
$post_data["rules"]=I("post.rules");$post_data["rules"]=implode(",",$post_data["rules"]); 
			$data=$this->Model->create($post_data);
			if($data){
				$result = $this->Model->where(array('id'=>$post_data['id']))->save($data);
				if($result){
					action_log('Edit_AuthGroup', 'AuthGroup', $post_data['id']);
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
			action_log('Del_AuthGroup', 'AuthGroup', $id);
			$this->success('删除成功！');
		}
	}
}