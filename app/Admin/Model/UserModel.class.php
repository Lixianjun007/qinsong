<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/2
 * Time: 下午9:00
 */

namespace Admin\Model;

use Think\Model\RelationModel;

class UserModel extends RelationModel
{
    //一个用户属于多个用户组，一个用户组有多个用户，即多对多关联
    protected $_link = array(
        'auth_group' => array(
            'mapping_type' => self::MANY_TO_MANY,
            'mapping_name' => 'groups',
            'foreign_key' => 'uid',
            'relation_foreign_key' => 'group_id',
            'relation_table' => 'auth_group_access' //此处应显式定义中间表名称，且不能使用C函数读取表前缀
        )
    );

    //自动验证
    protected $_validate = array(
        array('username', 'require', '用户名必须！'), //默认情况下用正则进行验证
        array('email', 'require', '邮箱必须！'), //默认情况下用正则进行验证
        array('password', 'require', '密码必须！'), //默认情况下用正则进行验证
        array('mobile', 'require', '手机号必须！'), //默认情况下用正则进行验证
        array('username', '', '用户名已经存在！', 0, 'unique', 1), // 在新增的时候验证name字段是否唯一
        array('email', '', '邮箱已经存在！', 0, 'unique', 1), // 在新增的时候验证email字段是否唯一
        array('mobile', '', '手机号已经存在！', 0, 'unique', 1), // 在新增的时候验证mobile字段是否唯一
        array('username', 'checklength', '用户名长度必须在5-15位之间', 0, 'callback', 3, array(5, 15)),
        array('check_password', 'password', '确认密码不正确', 0, 'confirm'), // 验证确认密码是否和密码一致
        array('password', 6, 100, '密码的长度最低6位', 0, 'length'), // 自定义函数验证密码格式
        array('mobile', '/^1[34578]\d{9}$/', '请输入正确的手机号', 0, 'regex', 3),// 必填
    );


    /***
     * 自定义验证字符串长度
     * @param $str
     * @param $min
     * @param $max
     * @return bool
     */
    function checklength($str, $min, $max)
    {
        preg_match_all("/./u", $str, $matches);
        $len = count($matches[0]);
        if ($len < $min || $len > $max) {
            return false;
        } else {
            return true;
        }
    }

}