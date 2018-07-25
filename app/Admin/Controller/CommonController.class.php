<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/10
 * Time: 上午12:34
 */

namespace Admin\Controller;

use Think\Controller;
use Think\Auth;

class CommonController extends Controller
{
    function __construct()
    {
        parent::__construct();
        $this->check_login();
        $this->all_menus();  //公共数据
        $this->sidebar();  //侧边栏统计
    }


    /***
     * 检查用户是否登录
     */
    public function check_login()
    {
        //如果有session，那么发送到模板
        if (!$_SESSION['user']) {
            $this->error('请登录后再访问', 'http://qinsongc.net/shop.php'.U("User/login"));
        }

        //如果cookie不存在，跳回登录页
        if (!$token = $_COOKIE['token']) {
            $this->error('您还没有登录，请登录后访问', 'http://qinsongc.net/shop.php'.U('User/login'));
        }

        //如token与数据库里面不匹配，表示用户伪造了token
        $User = M('User');
        $user = $User->where("token = '$token'")->find();
        if (!$user) {
            $this->error('请不要非法登录', 'http://qinsongc.net/shop.php'.U('User/login'));
        }

        //当前面的各个模块的增删改查完成后，开启下面的代码即可实现权限管理
        $Auth = new Auth();
        $name = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
        if (!$Auth->check($name, $_SESSION['user']['id'])) {
            $this->error('您没有权限访问当前页面,请与管理员联系', 'http://qinsongc.net/shop.php'.U('User/no_auth'));
        }
    }

    /***
     * 侧边栏数据
     */
    private function sidebar()
    {
        $count = [];
        $count['products'] = M('Product')->where("status = 1")->count(); //商品列表
        $count['trash'] = M('Product')->where("status = 0")->count(); //商品回收站
        $count['advert'] = M('Advert')->count();
        $count['customer'] = M('Customer')->count();
        $count['users'] = M('User')->count();  //统计管理员数量
        $count['groups'] = M('Auth_group')->count();  //统计用户组数量
        $count['orders'] = M('Orders')->count();  //订单数量统计
        $this->assign('count', $count);
    }

    /***
     * 公共数据
     */
    private function all_menus()
    {
        //查出所有菜单权限
        $AuthRule = M('AuthRule');
        //一级菜单
        $menus = $AuthRule->where("parent_id = 0")->order('sort asc')->select();

        //二级菜单
        foreach ($menus as $key => $value) {
            $children = $AuthRule->where("parent_id = '$value[id]'")->order('sort asc')->select();
            $menus[$key]['children'] = $children;

            //三级菜单
            foreach ($menus[$key]['children'] as $k => $v) {
                $child = $AuthRule->where("parent_id = '$v[id]'")->order('sort asc')->select();
                $menus[$key]['children'][$k]['child'] = $child;
            }
        }
//        dump($menus);exit;
        $this->assign('menus', $menus);
    }
}