<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/12
 * Time: 上午12:27
 */

namespace Home\Controller;

use Think\Controller;

class CustomerController extends Controller
{
    var $customer;
    var $orders;
    var $order_product;
    var $product;

    function __construct()
    {
        parent::__construct();
        $this->customer = M('Customer');
        $this->orders = M('Orders');
        $this->order_product = M('Order_product');
        $this->product = M('Product');
        $this->customer_id = $_SESSION['customer']['id'];
    }

    /***
     * 会员中心首页
     */
    public function index()
    {
        if (!isset($this->customer_id)) {
            $this->error('您还没有登录，请登录后访问', 'http://qinsongc.net/shop.php'.U('Customer/login'));
        }
        //查出当前登录的用户信息
        $customerInfo = $this->customer->where("id = $this->customer_id")->find();
        //待发货
        $daifu = $this->orders->where("status = 0 and customer_id = $this->customer_id")->count();
        //待发货
        $daifa = $this->orders->where("status = 1 and customer_id = $this->customer_id")->count();
        //已发货
        $yifa = $this->orders->where("status = 2 and customer_id = $this->customer_id")->count();
        //已完成
        $wancheng = $this->orders->where("status = 3 and customer_id = $this->customer_id")->count();

        $this->assign(compact('customerInfo', 'daifu', 'daifa', 'yifa', 'wancheng'));
        $this->display();
    }


    /***
     * 会员中心--会员充值
     */
    public function inquiry()
    {
        $this->display();
    }

    /***
     * 会员中心---修改个人资料
     */
    public function info($id)
    {
        if (IS_AJAX) {
            $this->customer->create();
            $this->customer->where("id = $this->customer_id")->save();
            $info = array('status' => 1, 'msg' => '修改成功');
            $this->ajaxReturn($info);
        } else {
            $customer = $this->customer->find($id);
            $this->assign('customer', $customer);
            $this->display();
        }
    }

    /***
     * 编辑个性签名
     */
    public function editSign()
    {
        if (IS_AJAX) {
            $sign = trim(I('post.sign'));
            $result = $this->customer->where("id = $this->customer_id")->setField('sign', $sign);
            if ($result) {
                $info = array('status' => 1, 'msg' => '编辑成功');
            } else {
                $info = array('status' => 0, 'msg' => '编辑失败');
            }

            $this->ajaxReturn($info);
        }
    }

    /***
     * 会员中心---我的订单
     */
    public function orders()
    {
        $status = I('get.status');
        if ($status == 'all') {
            $order_map = array('customer_id' => $this->customer_id);
        } else {
            $order_map = array('customer_id' => $this->customer_id, 'status' => $status);
        }

        //连表查询 a是orders b是order_product c是product
//        $all_orders = $this->orders->alias('a')->field('a.id,a.order_no,a.total_price,a.status as order_status,b.number,b.order_id,b.product_id,c.id as pid,c.name,c.price,c.thumb')
//            ->join('order_product AS b ON b.order_id=a.id', 'LEFT')
//            ->join('product AS c ON c.id=b.product_id', 'LEFT')
//            ->where($order_map)->select();


        $all_orders = $this->orders->where($order_map)->order('id desc')->select();
        foreach ($all_orders as &$product) {
            //查出当前用户的订单
            $product['order_products'] = $this->order_product->where("order_id = '$product[id]'")->select();

            //根据订单商品表的product_id查出当前订单的商品详细信息
            foreach ($product['order_products'] as &$p) {
                $p['product'] = $this->product->where("id = '$p[product_id]'")->find();
            }
        }

        $this->assign(compact('all_orders', 'status'));
        $this->display();
    }

    /***
     * 取消订单
     * 注：只有待发货的订单显示 "取消订单"
     */
    public function cancel_order()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $status = I('post.status');
            $this->orders->where("id = '$id' and status = '$status'")->setField('status', 0);
        }
    }


    /***
     * 前端用户登录
     */
    public function login()
    {
        $Customer = M('Customer');
        if (IS_POST) {
            $map['nickname|email|mobile'] = trim(I('post.email'));
            $map['password'] = set_password(I('post.password'));

            //判断表单提交过来的用户名或密码跟数据库里面的是否一致。
            $customer = $Customer->where($map)->find();
            if (!$customer) {
                $this->error('用户名或密码输入错误');
            }

            $_SESSION['customer'] = $customer;

            //登录后跳回之前的页面
            if (isset($_SESSION['url'])) {
                $this->success('登录成功', $_SESSION['url']);
            } else {
                $this->redirect('Index/index');
            }
//            echo "<script> history.go(-3); </script>";
        } else {
            $this->display();
        }
    }


    /***
     * 前端用户注册
     */
    public function register()
    {
        $Customer = M('Customer');
        if (IS_AJAX) {
            $data['nickname'] = I('post.nickname');
            $data['customer_no'] = I('post.customer_no');
            $data['thumb'] = I('post.thumb');
            $data['email'] = I('post.email');
            $data['mobile'] = I('post.mobile');
            $data['sex'] = I('post.sex');
            $data['birthday'] = I('post.birthday');
            $data['created_at'] = time();
            $data['password'] = set_password(I('post.password'));
            $check_password = I('post.check_password');

            $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
            $telephone = "#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#";

            $nickname = $Customer->where("nickname = '$data[nickname]'")->find();
            $email = $Customer->where("email = '$data[email]'")->find();
            $mobile = $Customer->where("mobile = '$data[mobile]'")->find();

            $info = array('status' => 1, 'msg' => '注册成功');

            if ($nickname) {
                $info = array('status' => 0, 'msg' => '此用户名已被注册');
            } elseif ($email) {
                $info = array('status' => 0, 'msg' => '此邮箱已被注册');
            } elseif ($mobile) {
                $info = array('status' => 0, 'msg' => '该手机号已被注册');
            } elseif (strlen($data['nickname']) < 3) {
                $info = array('status' => 0, 'msg' => '用户名长度不能小于3位');
            } elseif (!preg_match($pattern, $data['email'])) {
                $info = array('status' => 0, 'msg' => '请输入正确的邮箱');
            } elseif (!preg_match($telephone, $data['mobile'])) {
                $info = array('status' => 0, 'msg' => '请输入正确的手机号');
            } elseif ($check_password != I('post.password')) {
                $info = array('status' => 0, 'msg' => '两次密码输入不一致');
            }

            if ($info['status'] == 1) {
                $Customer->add($data);
            }

            $this->ajaxReturn($info);
        } else {
            $this->display();
        }
    }


    /***
     * 用户退出
     */
    public function logout()
    {
//        rm_dir(RUNTIME_PATH);  使用cookie之前的退出

        //使用cookie之后清空当前用户的session
        $_SESSION = array();

        //删除session后, 再清除对应的cookie，session_name(), cookie中储存的session标识, 即key
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), "", time() - 3600, "/");
        }

        session_destroy();

        $this->success('您已成功退出', 'http://qinsongc.net/shop.php'.U('Customer/login'));
    }


}