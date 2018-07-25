<?php

namespace Home\Controller;

use Think\Controller;

/**
 * 前台
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class CommonController extends Controller {

    // 用户信息
    protected $user;

    /**
     * [__construt 构造方法]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:29:53+0800
     * @param    [string]       $msg  [提示信息]
     * @param    [int]          $code [状态码]
     * @param    [mixed]        $data [数据]
     */
    protected function _initialize() {
        // 公共数据初始化
        $this->CommonInit();
    }

    /**
     * [ajaxReturn 重写ajax返回方法]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-07T22:03:40+0800
     * @param    [string]       $msg  [提示信息]
     * @param    [int]          $code [状态码]
     * @param    [mixed]        $data [数据]
     * @return   [json]               [json数据]
     */
    protected function ajaxReturn($msg = '', $code = -1, $data = '') {
        // ajax的时候，success和error错误由当前方法接收
        if (IS_AJAX) {
            if (isset($msg['info'])) {
                // success模式下code=0, error模式下code参数-1
                $result = array('msg' => $msg['info'], 'code' => -1, 'data' => '');
            }
        }

        // 默认情况下，手动调用当前方法
        if (empty($result)) {
            $result = array('msg' => $msg, 'code' => $code, 'data' => $data);
        }

        // 错误情况下，防止提示信息为空
        if ($result['code'] != 0 && empty($result['msg'])) {
            $result['msg'] = L('common_operation_error');
        }
        exit(json_encode($result));
    }

    /**
     * [Is_Login 登录校验]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-03-09T11:43:48+0800
     */
    protected function Is_Login() {

        $app_client_user_id = I('request.app_client_user_id');
        if (empty($app_client_user_id)) {
            $this->ajaxReturn('openid为空');
        }
    }

    /**
     * [CommonInit 公共数据初始化]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-03-09T11:43:48+0800
     */
    private function CommonInit() {
        // 用户数据
        $this->user = M('user')->where(['alipay_openid' => I('request.app_client_user_id')])->find();
    }

    /**
     * [_empty 空方法操作]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-02-25T15:47:50+0800
     * @param    [string]      $name [方法名称]
     */
    protected function _empty($name) {
        $this->ajaxReturn(L('common_unauthorized_access'));
    }

}

?>