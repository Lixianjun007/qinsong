<?php

namespace Admin\Controller;

/**
 * 订单管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class WaterOrderController extends CommonController {

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

        // 权限校验
        $this->Is_Power();
    }

    /**
     * [Index 订单列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     */
    public function Index() {
        // 参数
        $param = array_merge($_POST, $_GET);

        // 模型对象
        $m = M('water_order');

        // 条件
        $where = $this->GetIndexWhere();

        // 分页
        $number = 10;
        $page_param = array(
            'number' => $number,
            'total' => $m->where($where)->where('status <> 5')->count(),
            'where' => $param,
            'url' => U('Admin/WaterOrder/Index'),
        );
        $page = new \My\Page($page_param);

        $select = $m->where($where)->where('status <> 5')->limit($page->GetPageStarNumber(), $number);
        if (I('status', -1) != -1) {
            $select = $select->order('upd_time desc')->select();
        } else {
            $select = $select->order('id desc')->select();
        }
        // 获取列表
        $list = $this->SetDataHandle($select);
        // 状态
        $this->assign('order_status_list', L('order_status_list'));

        // 部门
        $this->assign('order_department_list', L('order_department_list'));

        // 参数
        $this->assign('param', $param);

        // Excel地址
        $this->assign('excel_url', U('Admin/WaterOrder/ExcelExport', $param));

        // 分页
        $this->assign('page_html', $page->GetPageHtml());

        // 数据列表
        $this->assign('list', $list);
        $this->display('Index');
    }

    /**
     * [ExcelExport excel文件导出]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-01-10T15:46:00+0800
     */
    public function ExcelExport() {
        // 条件
        $where = $this->GetIndexWhere();

        // 读取数据
        $data = $this->SetDataHandle(M('water_order')->where($where)->where('status <> 5')->select());
        $result = array();
        if (!empty($data)) {
            $number = count($data);
            foreach ($data as $v) {
                $v['number'] = $number;
                $v['order_id'] = $v['id'];
                // 单号信息
                if (!empty($v['courier'])) {
                    foreach ($v['courier'] as $vs) {
                        $v['courier_text'] = $vs['number'] . '/' . $vs['goods'] . '/' . $vs['kg'] . "\n";
                        $v['express'] = $vs['number'];
                        $v['description'] = $vs['goods'];
                        $result[] = $v;
                    }
                } else {
                    $v['courier_text'] = '';
                    $result[] = $v;
                }
                $number--;
            }
        }

        // Excel驱动导出数据
        $excel = new \My\Excel(array('filename' => 'order', 'title' => L('lxj'), 'data' => array_reverse($result), 'msg' => L('common_not_data_tips')));
        $excel->Export();
    }

    /**
     * [SetDataHandle 数据处理]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-29T21:27:15+0800
     * @param    [array]      $data [订单数据]
     * @return   [array]            [处理好的数据]
     */
    private function SetDataHandle($data) {
        if (!empty($data)) {
            $ac = M('OrderClass');
            foreach ($data as $k => $v) {
                // 时间
                $data[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $data[$k]['upd_time'] = date('Y-m-d H:i:s', $v['upd_time']);

                // 状态
                $data[$k]['status_text'] = L('order_status_list')[$v['status']]['name'];

                // 部门
                $data[$k]['department_text'] = L('order_department_list')[$v['department']];

                // 船号
                $data[$k]['ship_text'] = L('order_ship_list')[$v['ship']];

                // 单号信息
                $data[$k]['courier'] = unserialize($v['courier']);
                $courier_text = "";
                if (!empty($data[$k]['courier'])) {
                    foreach ($data[$k]['courier'] as $vs) {
                        $courier_text .= $vs['number'] . '/' . $vs['goods'] . '/' . $vs['kg'] . "\n";
                    }
                }
                $data[$k]['courier_text'] = $courier_text;
            }
        }
        return $data;
    }

    /**
     * [GetIndexWhere 订单列表条件]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     */
    private function GetIndexWhere() {
        $where = array();

        if ($_REQUEST['chose_key']) {
            $like_keyword = array('like', '%' . I('keyword') . '%');
            switch ($_REQUEST['chose_key']) {
                case 1:
                    $where[] = array(
                        'courier' => $like_keyword,
                        '_logic' => 'or',
                    );
                    break;
                case 2:
                    $where[] = array(
                        'user_name' => $like_keyword,
                        '_logic' => 'or',
                    );
                    break;
                case 3:
                    $where[] = array(
                        'mobile_phone' => $like_keyword,
                        '_logic' => 'or',
                    );
                    break;
                case 4:
                    $where[] = array(
                        'order_number' => $like_keyword,
                        '_logic' => 'or',
                    );
                    break;
            }
        } else {
            if (!empty($_REQUEST['keyword'])) {
                $like_keyword = array('like', '%' . I('keyword') . '%');
                $where[] = array(
                    'courier' => $like_keyword,
                    'user_name' => $like_keyword,
                    'mobile_phone' => $like_keyword,
                    'order_number' => $like_keyword,
                    '_logic' => 'or',
                );
            }
        }

        // 模糊
        // 是否更多条件
        if (I('is_more', 0) == 1) {
            // 状态
            if (I('status', -1) != -1) {
                $where['status'] = intval(I('status'));
            }
            // 部门
            if (I('department') != -1) {
                $where['department'] = intval(I('department'));
            }

            // 表达式
            if (!empty($_REQUEST['time_start'])) {
                $where['add_time'][] = array('gt', strtotime(I('time_start')));
            }
            if (!empty($_REQUEST['time_end'])) {
                $where['add_time'][] = array('lt', strtotime(I('time_end')));
            }
        }
        return $where;
    }

    /**
     * [SaveInfo 订单添加/编辑页面]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-14T21:37:02+0800
     */
    public function SaveInfo() {
        // 订单信息
        if (empty($_REQUEST['id'])) {
            $data = array();
        } else {
            $data = M('water_order')->find(I('id'));
            if (!empty($data['courier'])) {
                // 静态资源地址处理
                $data['courier'] = unserialize($data['courier']);
            }
        }
        $history = M('water_order')->field('order_number')->where(['status' => 1])->order('upd_time desc')->limit(20)->select();
        $max = 0;
        foreach ($history as $k => $v) {
            list($pre, $num) = explode('-', $v['order_number']);
            if ($num > $max) {
                $max = $num;
                $return_max = $v['order_number'];
            }
        }
        // 部门
        $data['department_text'] = L('order_department_list')[$data['department']];
        list($pret, $numt) = explode('.', $data['department_text']);
        if (!(isset($data['order_number']) && $data['order_number'])) {
            $data['order_number'] = '七' . $pret . '-';
        }

        $this->assign('return_max', $return_max);
        $this->assign('data', $data);

        // 状态列表
        $this->assign('order_is_disposal_list', L('order_is_disposal_list'));
//        $this->assign('order_is_notice_list', L('order_is_notice_list'));

        $this->display('SaveInfo');
    }

    /**
     * [Save 订单添加/编辑]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-14T21:37:02+0800
     */
    public function Save() {
        var_dump($_POST);exit;
        // 是否ajax请求
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'), -1000);
        }

        // 请求参数
        $params = [
//            [
//                'checked_type' => 'isset',
//                'key_name' => 'is_notice',
//                'error_msg' => '通知状态有误',
//            ],
            [
                'checked_type' => 'isset',
                'key_name' => 'status',
                'error_msg' => '处理状态有误',
            ],
            [
                'checked_type' => 'empty',
                'key_name' => 'courier',
                'error_msg' => '单号信息有误',
            ],
            [
                'checked_type' => 'empty',
                'key_name' => 'id',
                'error_msg' => '订单id有误',
            ]
        ];
        $ret = params_checked($_POST, $params);
        if ($ret !== true) {
            $this->ajaxReturn($ret);
        }

        // 编辑
        $this->Edit();
    }

    /**
     * [Edit 订单编辑]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-17T22:13:40+0800
     */
    private function Edit() {
        // 数据处理
        $courier = [];
        $kg = 0;
        var_dump($_POST);exit;
        foreach ($_POST['courier']['number'] as $k => $v) {
            $courier[] = [
                'number' => $v,
                'goods' => $_POST['courier']['goods'][$k],
                'kg' => $_POST['courier']['kg'][$k],
            ];
            $kg += $_POST['courier']['kg'][$k];
        }
        list($kg1, $kg2) = explode('.', $kg);
        $price = sprintf("%.2f", 5 + ($kg1 - 1) * 1);

        // 更新数据
        $data = [
            'price' => ($price < 5) ? 5 : $price,
//            'price' => 0, //lxj
            'courier' => serialize($courier),
            'status' => I('status'),
            'order_number' => I('order_number'),
            'upd_time' => time(),
        ];
        if (M('water_order')->where(['id' => I('id')])->save($data)) {
            // 成功是否发起通知
//            if (I('is_notice') == 1) {
//                $this->OrderNotice(I('id'));
//            }
            $this->ajaxReturn(L('common_operation_edit_success'), 0);
        }
        $this->ajaxReturn(L('common_operation_edit_error'), -100);
    }

    /**
     * [OrderNotice 订单通知]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-03-06T18:10:31+0800
     * @param    [int]            $order_id [订单id]
     */
//    private function OrderNotice($order_id) {
//        //
//    }

    /**
     * [Close 订单关闭]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Close() {
        // 是否ajax请求
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'), -1000);
        }

        // 删除数据
        if (!empty($_POST['id'])) {
            // 更新
            $data = ['status' => 5, 'upd_time' => time()];
            if (M('water_order')->where(['id' => I('id')])->save($data)) {
                $this->ajaxReturn(L('common_operation_success'), 0);
            } else {
                $this->ajaxReturn(L('common_operation_error'), -100);
            }
        } else {
            $this->ajaxReturn(L('common_param_error'), -1);
        }
    }

    /**
     * [Success 订单完成]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Success() {
        // 是否ajax请求
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'), -1000);
        }

        // 删除数据
        if (!empty($_POST['id'])) {
            // 更新
            $data = ['status' => 3, 'upd_time' => time()];
            if (M('water_order')->where(['id' => I('id')])->save($data)) {
                $this->ajaxReturn(L('common_operation_success'), 0);
            } else {
                $this->ajaxReturn(L('common_operation_error'), -100);
            }
        } else {
            $this->ajaxReturn(L('common_param_error'), -1);
        }
    }

    /**
     * 批量修改订单
     */
    public function modify() {
        // 是否ajax请求
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'), -1000);
        }
        $ids_arr = I('id');
        $status = I('status');
        if ($ids_arr && $status) {
            $data = ['status' => $status, 'upd_time' => time()];
            M('order')->where(['id' => ['in', $ids_arr]])->save($data);
            $this->ajaxReturn('批量更新成功', 0);
        }

        $this->ajaxReturn('错误', -100);
    }

}

?>