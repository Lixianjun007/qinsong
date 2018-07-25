<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/15
 * Time: 下午8:02
 */

namespace Home\Controller;

use Common\Service\Dictionary;
use Think\Exception;

class OrderController extends CommonController
{
    function __construct()
    {
        parent::__construct();
        $this->customer_id = $_SESSION['customer']['id'];
    }

    /***
     * 订单列表
     */
    public function index()
    {
        //查出用户的收货地址
        $address = $this->address->where("customer_id = $this->customer_id")->find();

        //查出用户要购买的商品信息
        $products = $this->cart->alias('a')->field('a.id as cart_id,a.num,b.*')
            ->join('product as b ON b.id=a.product_id', 'LEFT')
            ->where(['a.customer_id' => $this->customer_id])->select();
        $total = 0;
        foreach ($products as $k => $v) {
            $total += floatval($v['price']) * intval($v['num']);
        }

        $this->assign(compact('products', 'total', 'address'));
        $this->display();
    }


    /***
     * 新增收货地址
     * 注：如果用户已经有了地址，只需更新用户的地址
     */
    public function add_address()
    {
        if (IS_AJAX) {
            $data['customer_id'] = $this->customer_id;
            $data['name'] = I('post.name');
            $data['tel'] = I('post.tel');
            $area = I('post.area');
            $address = I('post.address');
            $data['address'] = $area . ' ' . $address;
            $result = $this->address->where("customer_id = $this->customer_id")->find();
            if ($result) {
                $this->address->where("customer_id = $this->customer_id")->save($data);
            } else {
                $this->address->where("customer_id = $this->customer_id")->add($data);
            }
        }
    }

    /***
     * 立即支付（即下单）
     * 需求：订单数据插入订单表，订单商品数据插入订单商品表，
     *     1.下单时后台判断商品库存是否充足；
     *     2.对应减去商品的库存及清空购物车并记录商品已售数量；
     *     3：下单时加上事务处理并跳转
     */
    public function store()
    {
        //连表（a:cart表；b：product表）查出商品信息
        $products = $this->cart->alias('a')->field('a.id as cart_id,a.num,b.*')
            ->join('product as b ON b.id=a.product_id', 'LEFT')
            ->where(['a.customer_id' => $this->customer_id])->select();

        $this->orders->startTrans();
        try {

            $total = 0;
            foreach ($products as $k => $v) {
                $total += floatval($v['price']) * intval($v['num']);

                //判断商品库存
                if (intval($v['num']) > intval($v['stock'])) {
                    throw new Exception('此商品库存不足');
                }
            }

            //生成订单
            $data = [];
            $data['customer_id'] = $this->customer_id;
            $data['order_no'] = Dictionary::make_order_no();
            $data['created_at'] = time();
            $data['total_price'] = $total;
            $data['address_id'] = I('post.address_id');
            $data['status'] = 0;  //初始状态为0,即待付款
            //订单数据插入订单表
            $id = $this->orders->add($data);

            $orderProductInfo['order_id'] = $id;

            //插入订单商品表的数据
            foreach ($products as $k => $v) {
                $orderProductInfo['product_id'] = $v['id'];
                $orderProductInfo['number'] = $v['num'];
                $this->order_product->add($orderProductInfo);

                //支付成功后对应减去商品的库存及清空购物车
                $numbers = intval($orderProductInfo['number']);
                $product_id = $orderProductInfo['product_id'];
                $this->product->where("id = '$product_id'")->setInc('sale_done', $numbers);  //已售商品数量
                $this->product->where("id = '$product_id'")->setDec('stock', $numbers);  //减去库存
            }
            $this->cart->where("customer_id = $this->customer_id")->delete();  //清空购物车
            $info = array('status' => 1, 'msg' => '您已成功下单');
            $info['id'] = $id;

            $this->orders->commit();
        } catch (Exception $e) {
            $this->orders->rollback();
            $info['status'] = 0;
            $info['info'] = $e->getMessage();
        }

        $this->ajaxReturn($info);

    }


    /***
     *订单确认
     */
    public function confirm()
    {
        $id = I('get.id');

        //根据前端提交过来的订单id查出当前用户的订单
        $orders = $this->orders->find($id);

        //查出当前用户的下单地址
        $address = $this->address->where("customer_id = $this->customer_id")->find();

        //根据订单id查出当前用户下单的商品信息
        $order_products = $this->order_product->where("order_id = '$id'")->select();
        foreach ($order_products as &$order_product) {
            $order_product['product'] = $this->product->where("id = $order_product[product_id]")->find();
        }

        $this->assign(compact('order_products', 'address', 'orders'));
        $this->display();
    }


}