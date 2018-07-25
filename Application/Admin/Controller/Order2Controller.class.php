<?php

namespace Admin\Controller;

/**
 * 订单管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Order2Controller extends CommonController
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
	public function Index()
	{
		// 参数
		$param = array_merge($_POST, $_GET);

		// 模型对象
		$m = M('Order');

		// 条件
		$where = $this->GetIndexWhere();

		// 分页
		$number = 10;
		$page_param = array(
				'number'	=>	$number,
				'total'		=>	$m->where($where)->count(),
				'where'		=>	$param,
				'url'		=>	U('Admin/Order/Index'),
			);
		$page = new \My\Page($page_param);

		// 获取列表
		$list = $this->SetDataHandle($m->where($where)->limit($page->GetPageStarNumber(), $number)->order('id desc')->select());

		// 状态
		$this->assign('order_status_list', L('order_status_list'));

		// 部门
		$this->assign('order_department_list', L('order_department_list'));

		// 参数
		$this->assign('param', $param);

		// Excel地址
		$this->assign('excel_url', U('Admin/Order/ExcelExport', $param));

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
	public function ExcelExport()
	{
		// 条件
		$where = $this->GetIndexWhere();

		// 读取数据
		$data = $this->SetDataHandle(M('Order')->where($where)->select());
		$result = array();
		if(!empty($data))
		{
			foreach($data as $v)
			{
				// 单号信息
				if(!empty($v['courier']))
				{
					foreach($v['courier'] as $vs)
					{
						$v['courier_text'] = $vs['number'].'/'.$vs['goods'].'/'.$vs['kg']."\n";
						$result[] = $v;
					}
				} else {
					$v['courier_text'] = '';
					$result[] = $v;
				}
			}
		}

		// Excel驱动导出数据
		$excel = new \My\Excel(array('filename'=>'order', 'title'=>L('excel_order_title_list'), 'data'=>array_reverse($result), 'msg'=>L('common_not_data_tips')));
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
	private function SetDataHandle($data)
	{
		if(!empty($data))
		{
			$ac = M('OrderClass');
			foreach($data as $k=>$v)
			{
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
				if(!empty($data[$k]['courier']))
				{
					foreach($data[$k]['courier'] as $vs)
					{
						$courier_text .= $vs['number'].'/'.$vs['goods'].'/'.$vs['kg']."\n";
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
	private function GetIndexWhere()
	{
		$where = array();

		// 模糊
		if(!empty($_REQUEST['keyword']))
		{
			$like_keyword = array('like', '%'.I('keyword').'%');
			$where[] = array(
					'courier'		=>	$like_keyword,
					'user_name'		=>	$like_keyword,
					'mobile_phone'	=>	$like_keyword,
					'_logic'		=>	'or',
				);
		}

		// 是否更多条件
		if(I('is_more', 0) == 1)
		{
			// 状态
			if(I('status', -1) != -1)
			{
				$where['status'] = intval(I('status'));
			}
			// 部门
			if(I('department') != -1)
			{
				$where['department'] = intval(I('department'));
			}

			// 表达式
			if(!empty($_REQUEST['time_start']))
			{
				$where['add_time'][] = array('gt', strtotime(I('time_start')));
			}
			if(!empty($_REQUEST['time_end']))
			{
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
	public function SaveInfo()
	{
		// 订单信息
		if(empty($_REQUEST['id']))
		{
			$data = array();
		} else {
			$data = M('Order')->find(I('id'));
			if(!empty($data['courier']))
			{
				// 静态资源地址处理
				$data['courier'] = unserialize($data['courier']);
			}
		}
		$this->assign('data', $data);

		// 状态列表
		$this->assign('order_is_disposal_list', L('order_is_disposal_list'));
		$this->assign('order_is_notice_list', L('order_is_notice_list'));

		$this->display('SaveInfo');
	}

	/**
	 * [Save 订单添加/编辑]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-14T21:37:02+0800
	 */
	public function Save()
	{
		// 是否ajax请求
		if(!IS_AJAX)
		{
			$this->error(L('common_unauthorized_access'), -1000);
		}

		// 请求参数
        $params = [
            [
                'checked_type'      => 'isset',
                'key_name'          => 'is_notice',
                'error_msg'         => '通知状态有误',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'status',
                'error_msg'         => '处理状态有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'courier',
                'error_msg'         => '单号信息有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '订单id有误',
            ]
        ];
        $ret = params_checked($_POST, $params);
        if($ret !== true)
        {
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
	private function Edit()
	{
		// 数据处理
		$courier = [];
		$kg = 0;
		foreach($_POST['courier']['number'] as $k=>$v)
		{
			$courier[] = [
				'number'	=> $v,
				'goods'		=> $_POST['courier']['goods'][$k],
				'kg'		=> $_POST['courier']['kg'][$k],
			];
			$kg += $_POST['courier']['kg'][$k];
		}
		$price = sprintf("%.2f", ($kg/2.5)*5);

		// 更新数据
		$data = [
//			'price'		=> ($price < 5) ? 5 : $price,
                    'price'             => 0,           //lxj
			'courier'	=> serialize($courier),
			'status'	=> I('status'),
			'upd_time'	=> time(),
		];
		if(M('Order')->where(['id' => I('id')])->save($data))
		{
			// 成功是否发起通知
			if(I('is_notice') == 1)
			{
				$this->OrderNotice(I('id'));
			}
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
	private function OrderNotice($order_id)
	{
		//
	}

	/**
	 * [Close 订单关闭]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-15T11:03:30+0800
	 */
	public function Close()
	{
		// 是否ajax请求
		if(!IS_AJAX)
		{
			$this->error(L('common_unauthorized_access'), -1000);
		}

		// 删除数据
		if(!empty($_POST['id']))
		{
			// 更新
			$data = ['status' => 5, 'upd_time' => time()];
			if(M('Order')->where(['id' => I('id')])->save($data))
			{
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
	public function Success()
	{
		// 是否ajax请求
		if(!IS_AJAX)
		{
			$this->error(L('common_unauthorized_access'), -1000);
		}

		// 删除数据
		if(!empty($_POST['id']))
		{
			// 更新
			$data = ['status' => 3, 'upd_time' => time()];
			if(M('Order')->where(['id' => I('id')])->save($data))
			{
				$this->ajaxReturn(L('common_operation_success'), 0);
			} else {
				$this->ajaxReturn(L('common_operation_error'), -100);
			}
		} else {
			$this->ajaxReturn(L('common_param_error'), -1);
		}
	}
}
?>