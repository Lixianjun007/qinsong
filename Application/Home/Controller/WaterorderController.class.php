<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

/**
 * Description of Waterorder
 *
 * @author 60226
 */
class WaterorderController extends CommonController {
    //put your code here

    /**
     * [_initialize 前置操作-继承公共前置方法]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function _initialize() {
        // 调用父类前置方法
        parent::_initialize();

        // 登录校验
        $this->Is_Login();
    }

    public function add() {
        $params = $this->order_add_params_checked();
        //备注
        $remark = isset($_POST['remarks']) ? $_POST['remarks'] : '';
        //计算总价
        $price = 0;
        foreach ($params['courier'] as $k => $v) {
            $price += $v['price'] * $v['number'];
        }
        $price = sprintf("%.2f", $price);

        $order = [
            'user_id' => $this->user['id'],
            'user_name' => $params['name'],
            'mobile_phone' => $params['mobile_phone'],
            'department' => $params['department'],
            'ship' => $params['ship'],
            'courier' => serialize($params['courier']),
            'price' => $price,
            'status' => 1,
            'add_time' => time(),
            'user_note' => $remark,
        ];
        $result = M('water_order')->data($order)->add();
        if ($result) {
            $this->ajaxReturn($result, 0);
        } else {
            M('water_order')->rollback();
            $this->ajaxReturn('订单提交失败，请稍后再试');
        }
    }

    /**
     * [order_add_params_checked 订单添加参数校验]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-03-02T17:26:43+0800
     * @return   [array]           [请求参数]
     */
    private function order_add_params_checked() {
        // 请求参数
        $params = [
            [
                'checked_type' => 'empty',
                'key_name' => 'name',
                'error_msg' => '姓名不能为空',
            ],
            [
                'checked_type' => 'empty',
                'key_name' => 'mobile_phone',
                'error_msg' => '手机不能为空',
            ],
            [
                'checked_type' => 'isset',
                'key_name' => 'department',
                'error_msg' => '请选择部门',
            ],
            [
                'checked_type' => 'isset',
                'key_name' => 'ship',
                'error_msg' => '请选择船号',
            ],
            [
                'checked_type' => 'empty',
                'key_name' => 'courier',
                'error_msg' => '单号信息不能为空',
            ]
        ];
        $ret = params_checked($_POST, $params);
        if ($ret !== true) {
            $this->ajaxReturn($ret);
        }

        // 参数处理
        $_POST['courier'] = json_decode($_POST['courier'], true);

        return $_POST;
    }
    
    
    /**
     * [Index 订单列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-02-22T16:50:32+0800
     */
    public function Index() {
        $m          = M('water_order');
        $number     = 10;
        $page       = intval(I('page', 1));
        $where      = $this->GetIndexWhere();
        $total      = $m->where($where)->count();
        $page_total = ceil($total / $number);
        $start      = intval(($page - 1) * $number);
        $data       = $m->where($where)->limit($start, $number)->order('id desc')->select();
        if (empty($data)) {
            $this->ajaxReturn('没有数据');
        } else {
            foreach ($data as &$v) {
                $v['add_time_text'] = date('Y-m-d H:i', $v['add_time']);
                $v['courier']       = unserialize($v['courier']);
            }
            $result = [
                'total'      => $total,
                'page_total' => $page_total,
                'data'       => $data,
            ];
            $this->ajaxReturn('成功', 0, $result);
        }
    }
    
    /**
     * [GetIndexWhere 获取条件]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-03-03T19:33:15+0800
     */
    private function GetIndexWhere() {
        $keywords = I('keywords');
        $where    = ['user_id' => $this->user['id']];
        if (!empty($keywords)) {
            $where['courier'] = ['like', '%' . $keywords . '%'];
        }
        return $where;
    }
    
    /**
     * [Cancel 订单取消]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-03-04T23:17:10+0800
     */
    public function Cancel() {
        // 请求参数
        $params = [
            [
                'checked_type' => 'empty',
                'key_name'     => 'order_id',
                'error_msg'    => '订单id为空 order id not empty',
            ]
        ];
        $ret    = params_checked($_POST, $params);
        if ($ret !== true) {
            $this->ajaxReturn($ret);
        }

        $order_id = I('order_id');
        $m        = M('water_order');

        /* 订单 */
        $order = $m->find($order_id);
        if (empty($order)) {
            $this->ajaxReturn('订单不存在 order there is no');
        }
        if ($order['status'] > 1) {
            $this->ajaxReturn('不可取消[' . L('order_status')[$order['status']] . '] Cannot be cancelled');
        }
        if ($m->where(['id' => $order_id])->save(['status' => 4, 'upd_time' => time()])) {
            $this->ajaxReturn('成功 success', 0);
        } else {
            $this->ajaxReturn('失败 failure');
        }
    }
    
    /**
     * [Pay 订单支付]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-03-03T21:20:07+0800
     */
    public function Pay() {
        // 请求参数
        $params = [
            [
                'checked_type' => 'empty',
                'key_name'     => 'order_id',
                'error_msg'    => '订单id不能为空 order id not empty',
            ]
        ];
        $ret    = params_checked($_POST, $params);
        if ($ret !== true) {
            $this->ajaxReturn($ret);
        }

        $order_id = I('order_id');
        $m        = M('water_order');

        /* 订单 */
        $order = $m->find($order_id);
        if (empty($order)) {
            $this->ajaxReturn('订单不存在 order there is no');
        }
        if ($order['status'] != 1) {
            $this->ajaxReturn('不可支付[' . L('order_status')[$order['status']] . '] do not pay');
        }

        /* 金额大于0则进入支付环节 */
        if ($order['price'] <= 0.00) {
            $this->ajaxReturn('订单金额有误 order price error');
        }

        /* 支付 */
        $notify_url = __MY_URL__ . 'Notify/waterorder.php';
        $pay_data   = array(
            'out_user'    => md5($this->user['id']),
            'order_sn'    => 'water'.$order_id,
            'name'        => '【' . $order['user_name'] . '-' . $order['id'] . '】购水费用',
            'total_price' => $order['price'],
            'notify_url'  => $notify_url,
        );
        $result     = (new \My\Alipay())->SoonPay($pay_data, C("alipay_key_secret"));
        if (empty($result)) {
            $this->ajaxReturn('支付接口异常 api error');
        }
        $this->ajaxReturn('成功 success', 0, $result);
    }
    
    /**
     * [Success 订单确认收货]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-03-04T23:17:10+0800
     */
    public function Success() {
        // 请求参数
        $params = [
            [
                'checked_type' => 'empty',
                'key_name'     => 'order_id',
                'error_msg'    => '订单id不能为空 order id not empty',
            ]
        ];
        $ret    = params_checked($_POST, $params);
        if ($ret !== true) {
            $this->ajaxReturn($ret);
        }

        $order_id = I('order_id');
        $m        = M('water_order');

        /* 订单 */
        $order = $m->find($order_id);
        if (empty($order)) {
            $this->ajaxReturn('订单不存在 order there is no');
        }
        if ($order['status'] != 2) {
            $this->ajaxReturn('不可确认[' . L('order_status')[$order['status']] . '] Do not confirm');
        }
        if ($m->where(['id' => $order_id])->save(['status' => 3, 'upd_time' => time()])) {
            $this->ajaxReturn('成功 success', 0);
        } else {
            $this->ajaxReturn('失败 failure');
        }
    }

}
