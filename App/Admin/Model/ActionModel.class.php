<?php 
/*
 * 行为管理模型
 * Auth   : Ghj
 * Time   : 1444370690 
 * QQ     : 912524639
 * Email  : 912524639@qq.com
 * Site   : http://guanblog.sinaapp.com/
 */
 
namespace Admin\Model;
use Think\Model;

class ActionModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('name', 'require', '行为标识必须', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '/^[a-zA-Z]\w{0,39}$/', '标识不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '标识已经存在', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
        array('title', 'require', '标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,80', '标题长度不能超过80个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('remark', 'require', '行为描述不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('remark', '1,140', '行为描述不能超过140个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('status', 1, self::MODEL_INSERT, 'string'),
        array('update_time', 'time', self::MODEL_BOTH, 'function'),
    );
	
	public function cache(){
		S('action_list',null);
		$action_list=$this->getField('id,name,title,remark,rule,log,type,status,update_time');
		S('action_list',$action_list);
	}
}