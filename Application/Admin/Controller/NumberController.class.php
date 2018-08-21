<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

/**
 * Description of NumberController
 *
 * @author 60226
 */
class NumberController extends CommonController {
    //put your code here
    
    public function _initialize() {
        // 调用父类前置方法
        parent::_initialize();

        // 登录校验
        $this->Is_Login();

        // 权限校验
        $this->Is_Power();
    }
    
     public function addOrderNumber() {
         $order_id = 27;
        $data = M('water_order')->field('department')->find($order_id);
        $data['department_text'] = L('order_department_list')[$data['department']]; //获得部门
        list($pret, $numt) = explode('.', $data['department_text']);
        $per_num_tmp = M('tmp_num')->where('type = 1')->find();
        $data['order_number'] = 'water-' . $per_num_tmp['number'];

        $map['order_number'] = ['like', $data['order_number'] . '%'];
        $tmp_num = M('water_order')->where($map)->count();
        $tmp_num++;
        $final_order_num = $data['order_number'] . $pret . '-' . $tmp_num;

        M('water_order')->where(['id' => $order_id])->save(['order_number' => $final_order_num]);

        return true;
    }
    
    
    public function Index(){
        $number = I('number', 0);
        $type = I('type', 0);
        if($number && $type){
            $data['number'] = $number;
            M('tmp_num')->where('type', $type)->save($data);
        }
        $list = M('tmp_num')->select();
        
        // 参数
        $this->assign('num_list', $list);
        
        $this->display('Index');
    }
}
