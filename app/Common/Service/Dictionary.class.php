<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/18
 * Time: 上午10:31
 */

namespace Common\Service;
class Dictionary
{
    /***
     * 随机生成订单号
     * @return string
     */
    public static function make_order_no()
    {
        return date('YmdHis') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /***
     * 订单状态
     * @return array
     */
    public static function orderStatus()
    {
        return [
            0 => '待付款',
            1 => '待发货',
            2 => '已发货',
            3 => '已完成',
        ];
    }
}