<?php 
/*
 * 消息模型
 * Auth   : Ghj
 * Time   : 1444386899 
 * QQ     : 912524639
 * Email  : 912524639@qq.com
 * Site   : http://guanblog.sinaapp.com/
 */
 
namespace User\Model;
use Think\Model;

class MessageModel extends Model{

    //array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
    protected $_validate = array(
        array('send_from_id', 'require', '发件用户 不能为空！',1),
        array('send_to_id', 'require', '收件用户 不能为空！',1),
        array('subject', 'require', '标题不能为空！',1),
        array('content', 'require', '内容不能为空！',1),
    );
    //array(填充字段,填充内容,[填充条件,附加规则])
    protected $_auto = array(
        array('message_time', 'time', 3, 'function'),
    );
	
}