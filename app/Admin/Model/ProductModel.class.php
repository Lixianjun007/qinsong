<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/7
 * Time: 下午8:44
 */

namespace Admin\Model;

use Think\Model;

class ProductModel extends Model
{
    //自动验证
    protected $_validate = array(
        array('name', 'require', '商品名称必填！'), //默认情况下用正则进行验证
        array('price', 'require', '商品单价必填！'), //默认情况下用正则进行验证
        array('stock', 'require', '商品库存必填！'), // 当值不为空的时候判断是否在一个范围内
        array('created_at', 'require', '发布时间必填！'), // 当值不为空的时候判断是否在一个范围内
        array('description', 'require', '商品描述必填！'), // 当值不为空的时候判断是否在一个范围内
    );

    //自动完成
    protected $_auto = array(
        array('created_at', 'set_time', 3, 'callback'), // 对name字段在新增和编辑的时候回调getName方法
        array('description', 'set_description', 3, 'callback'), // 对update_time字段在更新的时候写入当前时间戳
    );

    //将标准时间转换成Unix时间戳
    function set_time($created_at)
    {
        return strtotime($created_at);
    }

    //商品描述信息转义
    function set_description($description)
    {
        return htmlspecialchars_decode($description);
    }

    /***
     * 查出所有商品
     * @return mixed
     */
    public function all_products()
    {
        $Category = M('Category');
        $products = $this->order('sort_order desc')->select();
        foreach ($products as $key => $value) {
            $category_id = $value['category_id'];
            $category = $Category->find($category_id);
            $products[$key]['category'] = $category;
        }
        return $products;
    }
}
