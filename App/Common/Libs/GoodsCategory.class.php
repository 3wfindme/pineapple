<?php
/**
 * Created by PhpStorm.
 * User: bobchu
 * Date: 15/11/9
 * Time: 下午2:18
 */
namespace Common\Libs;
use Think\Db;
class GoodsCategory
{
    protected $table = 'goods_category';

    /**
     * 获取全部的商品分类，并排序
     * @return array
     */
    public function getList(){
        $list = M($this->table)->where(array('pid'=>0))->order('list_order ASC')->select();
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['children'] = M($this->table)->where(array('pid'=>$v['id']))->order('list_order ASC')->select();
            }
            return $list;
        }else{
            return $list;
        }
    }
}