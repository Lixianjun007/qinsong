<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/12/22
 * Time: 下午1:21
 */

namespace Admin\Controller;

use Think\Controller;

class EchartsController extends Controller
{
    /***
     * 性别统计
     */
    public function sex_total()
    {
        $Customer = M('Customer');
        $data = [
            ['value' => $Customer->where("sex = 1")->count(), 'name' => '男'],
            ['value' => $Customer->where("sex = 0")->count(), 'name' => '女']
        ];

        $this->ajaxReturn($data);
    }


    /***
     * 省份统计
     */
    public function province_total()
    {
        $Customer = M('Customer');
        $customer_total = $Customer->query("select province as name, count(*) as value from customer group by province");
        $this->ajaxReturn($customer_total);
    }
}
