<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/12
 * Time: 上午12:27
 */

namespace Home\Controller;

class CartController extends CommonController
{
    /***
     * 购物车首页
     */
    public function index()
    {
        //查出当前用户的购物车所有商品
        $carts = $this->cart->alias('a')->field('a.id as cart_id,a.num,b.*')
            ->join('product as b ON b.id=a.product_id', 'LEFT')
            ->where(['a.customer_id' => $_SESSION['customer']['id']])->select();
        //如果购物车为空，跳转到空购物车页面
        if (empty($carts)) {
            $this->redirect("shop.php/Home/Cart/em");
        }
        $total = 0;
        foreach ($carts as $k => $v) {
            $total += floatval($v['price']) * intval($v['num']);
        }
        $this->assign(compact('carts', 'total'));
        $this->display();
    }

    /***
     * 加入购物车:只把商品加入购物车，不跳转
     * 注：判断购物车是否已经存在该商品，如果存在，只增加该商品的数量，否则新增一条记录。
     * 立即购买：把商品加入购物车并跳转。
     */
    public function add_cart()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            if (!isset($_SESSION['customer'])) {
                $_SESSION['url'] = "/Home/Index/show/id/" . $id . ".html";
                $this->error('您还没有登录，请登录后再访问');
            }
            $data['product_id'] = I('post.id');
            $data['customer_id'] = $_SESSION['customer']['id'];

            $product = $this->cart->where($data)->find();
            if ($product) {
                $this->cart->where($data)->setInc('num');
                $info = array('status' => 1, 'msg' => '已添加到购物车');
            } else {
                $data['num'] = 1;
                $this->cart->add($data);
                $info = array('status' => 1, 'msg' => '已添加到购物车');
            }
            $this->ajaxReturn($info);
        }
    }

    /***
     * 购物车添加数量
     */
    public function add_num()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $this->cart->where("id = '$id'")->setInc('num');
        }
    }

    /***
     * 购物车减少数量
     */
    public function sub_num()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $this->cart->where("id = '$id'")->setDec('num');
        }
    }

    /***
     * 改变数量
     */
    public function change_num()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $data['num'] = I('post.num');
            $this->cart->where("id = '$id'")->save($data);
        }
    }


    /***
     * 移除商品
     */
    public function remove_cart()
    {
        if (IS_AJAX) {
            $cartId = I('post.cartId');
            $this->cart->delete($cartId);
        }
    }

    /***
     * 加载空购物车页面
     */
    public function em()
    {
        $this->display();
    }
}