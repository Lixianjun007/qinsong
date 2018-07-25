<?php

namespace Home\Controller;

/**
 * 订单通知
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class OrderNotifyController extends CommonController
{
    /**
     * [_initialize 前置操作-继承公共前置方法]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function _initialize()
    {
        // 调用父类前置方法
        parent::_initialize();
    }

    /**
     * [PayNotify 支付异步处理]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-03-04T14:35:38+0800
     */
    public function PayNotify()
    {
        $data = (new \My\Alipay())->Respond(C("alipay_key_secret"));
        if($data == 'no') exit('error');

        /* 是否已支付过 */
        $m = M('order');
        $order = $m->find($data['out_trade_no']);

        if(empty($order)) exit('error');
        if($order['status'] > 1) exit('success');

        /* 兼容web版本支付参数 */
        $buyer_email = isset($data['buyer_logon_id']) ? $data['buyer_logon_id'] : (isset($data['buyer_email']) ? $data['buyer_email'] : '');
        $total_amount = isset($data['total_amount']) ? $data['total_amount'] : (isset($data['total_fee']) ? $data['total_fee'] : '');

        /* 更新支付状态 */
        $where = ['id' => $order['id']];
        if($m->where($where)->save(['status' => 2, 'upd_time' => time()]))
        {
            /* 写入账单支付日志 */
            $pay_log_data = [
                'order_id'      => $order['id'],
                'trade_no'      => $data['trade_no'],
                'user'          => $buyer_email,
                'total_fee'     => $total_amount,
                'subject'       => $data['subject'],
                'add_time'      => time(),
            ];
            M('order_pay_log')->add($pay_log_data);
            exit('success');
        }
        exit('error');
    }
}
?>