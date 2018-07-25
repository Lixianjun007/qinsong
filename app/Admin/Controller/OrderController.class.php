<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/18
 * Time: 上午12:13
 */

namespace Admin\Controller;

use Common\Service\Dictionary;

class OrderController extends CommonController
{
    private $address;

    function __construct()
    {
        parent::__construct();
        $this->address = M('Address');
    }

    /***
     * 订单管理首页
     */
    public function index()
    {
        $orderObj = M('Orders');
        $map = [];
        if (IS_GET) {
            $order_no = I('get.order_no');
            $tel = I('get.tel');
            $created_at = I('get.created_at');
            $status = $_GET['status'];

            if (!empty($order_no)) {
                $map['order_no'] = array('like', "%" . $order_no . "%");
            }
            if (!empty($tel)) {
                $map['b.tel'] = array('like', "%" . $tel . "%");   //按手机号
            }

            //按统计时间
            if (!empty($created_at)) {
                $time = explode(" ~ ", $created_at);  //把字符串转换成数组
                $times[] = strtotime($time[0] . ' 00:00:00'); //这里单引号前面要打一空格
                $times[] = strtotime($time[1] . ' 23:59:59');

                $map['created_at'] = array('between', $times);
            }

            //按订单状态
            if (!empty($status)) {
                $map['status'] = $status; //按订单状态
            }
        }

        //符合条件的记录数
        $infoCount = $orderObj->count();
        $pageSize = 10;
        $page = new \Common\Util\Page($infoCount, $pageSize);
        $show_page = $page->show();

        $orders = $orderObj->alias('a')->order('created_at desc')->field('a.*,b.address,b.tel,b.name')
                            ->join('address AS b ON b.id=a.address_id', 'LEFT')
                            ->where($map)->limit($page->firstRow . ',' . $page->listRows)
                            ->select();

        //调用静态方法查出所有订单状态
        $orderStatus = Dictionary::orderStatus();

        $this->assign(compact('orders', 'orderStatus', 'show_page'));
        $this->display();
    }

    /***
     * 查看订单
     */
    public function show($id)
    {
        $productObj = M('Product');
        $orderObj = M('Orders');
        //查出用户地址和订单信息
        $order = $orderObj->find($id);
        $address_id = $order['address_id'];
        $address = $this->address->where("id = '$address_id'")->find();

        //商品信息 2表连表查询(a:product b:order_product,c:order) 当用户选择多个商品一起购买时，商品订单表会同时插入多条数据
        $productsInfo = $productObj->alias('a')
                                    ->field('a.name,a.price,b.number,a.price*b.number as total,c.total_price')//这里计算商品小计
                                    ->join('order_product AS b ON b.product_id=a.id', 'LEFT')
                                    ->join('orders AS c ON c.id=b.order_id', 'LEFT')
                                    ->where(array('b.order_id' => $id))
                                    ->select();

        $this->assign(compact('address', 'productsInfo'));
        $this->display();
    }



}