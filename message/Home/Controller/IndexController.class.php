<?php

namespace Home\Controller;

/**
 * 首页
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class IndexController extends CommonController {

    /**
     * [_initialize 前置操作-继承公共前置方法]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function _initialize() {
        // 调用父类前置方法
//		parent::_initialize();
    }

    public function index() {
        $param = $_GET['id'];
        $getMsg = I('addMsg', '');
        $base64_id = trim($param, addPerStr());
        $id = base64url_decode($base64_id);
//        if (!is_integer($id)) {
//            //错误页面
//            echo '这是错误页面';
//            return;
//        }
        $obj = new \Home\Model\IndexModel();
        $msg = $obj->getMsg($id);
        if ($msg == false) {
            if ($getMsg) {
                $msg = $obj->addMsg($id, $getMsg);
                if ($msg) {
                    goto display;
                } else {
                    goto add;
                }
            } else {
                add:
                    echo $id;
                echo'这是文本框';
                //显示添加的文本框
                $this->display('Index');
                return;
            }
        } else {
            display:
            //显示信息
            $this->assign('msg', $msg);
            $this->display('Show');
        }
        return;
    }

    /**
     * [Index 添加自增id]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-02-22T16:50:32+0800
     */
    public function addOrder() {
        $param = array_merge($_POST, $_GET);
        if ($param['sign'] == 'lxj') {
            // 参数
            for ($i = 0; $i < 5000; $i++) {
                $data[] = ['message' => ''];
            }
            $a = M('message')->addAll($data);

            $this->ajaxReturn($a);
        } else {
            $this->ajaxReturn('sign验证错误');
        }
    }

    public function makeUrl() {
        $start = 0;
        $end = 100;
        $data = [];
        while ($end <= 1000) {
            echo "id从[$start] 到 [$end]" . '<br/>';
            $list = M('message')->where(['message' => ''])->limit($start, 100)->select();
            foreach ($list as $k => $v) {
                $url = "http://qinsongc.net/message.php?id=" . addPerStr() . base64url_encode($v['id']) . addPerStr();
                echo $url . '<br/>';
            }
            $start = $end;
            $end += 100;
        }
        return;
    }

}

function addPerStr() {
    return '1q4i5N7s4O9n3G';
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
?>

