<?php
/**
 * @Created by NetBeans.
 * @author  lixianjun
 * @date    2018-8-29 14:54:04
 */

namespace Admin\Controller;
use \extend\Redis\RedisHelper;
class TestController  extends CommonController {

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
//        $this->Is_Login();

        // 权限校验
//        $this->Is_Power();
    }
    
    public function test(){
        
        $redis = RedisHelper::getRedisInstance();
//        $redis->setex('lxj', json_encode(L('menuInfo')));
        var_dump(json_decode($redis->get('lxj'), true));
//        var_dump(L('menuInfo'));
        
        echo 123;exit;
    }
    
}


