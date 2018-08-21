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
