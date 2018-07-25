<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/2
 * Time: 下午6:07
 */

namespace Admin\Model;

use Think\Model\RelationModel;

class AuthRuleModel extends RelationModel
{
    //一个一级菜单有很多二级菜单，类似于分类的自关联
    protected $_link = array(
        'AuthRule' => array(
            'mapping_type' => self::HAS_MANY,
            'mapping_name' => 'children',
            'parent_key' => 'parent_id',
            'mapping_order' => 'sort asc',
        ),
    );

    /**
     * 无限级分类
     * @param $data
     * @param int $id
     * @return array
     */
    function resort($data, $id = 0)
    {
        $list = array();
        foreach ($data as $v) {
            if ($v['parent_id'] == $id) {
                $v['children'] = self::resort($data, $v['id']);
                if (empty($v['son'])) {
                    unset($v['son']);
                }
                array_push($list, $v);
            }
        }
        return $list;
    }

    /***
     * 返回分类树
     * @return array
     */
    public function categoryTree()
    {
        $data = $this->order('sort asc')->select();
        return $this->resort($data);
    }

}