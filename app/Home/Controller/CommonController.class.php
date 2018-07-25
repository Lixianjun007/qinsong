<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/16
 * Time: 上午8:53
 */
namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller
{
    var $advert;
    var $product;
    var $category;
    var $gallery;
    var $cart;
    var $address;
    var $orders;
    var $order_product;

    function __construct()
    {
        parent::__construct();

        $this->advert = M('Advert');
        $this->product = M('Product');
        $this->category = M('Category');
        $this->gallery = M('Gallery');
        $this->cart = M('Cart');
        $this->address = M('Address');
        $this->orders = M('Orders');
        $this->order_product = M('Order_product');

        $this->check_customer();

        $_SESSION['url'] = $_SERVER['REQUEST_URI'];
    }

    /**
     * 检查用户是否登录
     */
    private function check_customer()
    {
        $customer_id = $_SESSION['customer']['id'];
        if(isset($customer_id)){
            //判断当前用户的购物车是否有商品，有就显示购物车图标，没有就隐藏
            $Cart = M('Cart');
            $this->product_num = $Cart->where("customer_id = '$customer_id'")->count();
        }
    }
}