<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/11
 * Time: 下午7:18
 */

namespace Admin\Controller;

class CustomerController extends CommonController
{
    /***
     * 会员管理首页
     */
    public function index()
    {
        $Customer = M('Customer');
        $nickname = I('get.nickname');
        $mobile = I('get.mobile');
        $birthday = strtotime(I('get.birthday'));
        $map = [];
        if (isset($nickname)) {
            $map['nickname'] = array('like', "%" . $nickname . "%");
        }
        if (isset($mobile)) {
            $map['mobile'] = array('like', "%" . $mobile . "%");
        }

        if (!empty($birthday)) {
            $map['birthday'] = $birthday;
        }

        $infoCount = $Customer->count();
        $pageSize = 10;
        $Page = new \Common\Util\Page($infoCount, $pageSize);
        $pages = $Page->show();// 分页显示输出
        $customers = $Customer->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('created_at desc')->select();
        $this->assign(compact('customers', 'pages'));
        $this->display();
    }


}
