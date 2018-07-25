<?php

/**
 * 模块语言包-订单
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
return array(
	// 添加/编辑
	'order_add_name'				=>	'订单添加',
	'order_edit_name'				=>	'订单编辑',
	'order_so_keyword_tips'			=>	'单号/姓名/手机',
	'order_view_status_title'		=>	'订单状态',
	'order_view_department_title'	=>	'部门',
	'order_status_list'				=>	array(
			0 => array('id' => 0, 'name' => '待处理', 'checked' => true),
			1 => array('id' => 1, 'name' => '待支付'),
			2 => array('id' => 2, 'name' => '已支付'),
			3 => array('id' => 3, 'name' => '已完成'),
			4 => array('id' => 4, 'name' => '已取消'),
			5 => array('id' => 5, 'name' => '已关闭'),
		),
	'order_department_list'			=> ['Deck', 'Food and beverage', 'Photography', 'Engine', 'Finance', 'Human Resources', 'Medical', 'Cruise program', 'Casino', 'Surveillance', 'Production / theater ', 'Guest entertainment', 'Hotel', 'Musician', 'Concession', 'Gallery', 'IT', 'Restaurant', 'Bar', 'House miscellaneous', 'Shore excursions'],
	'order_ship_list'				=> ['NCL'],

	'order_is_disposal_list'				=>	array(
			0 => array('id' => 0, 'name' => '不处理', 'checked' => true),
			1 => array('id' => 1, 'name' => '处理'),
		),

	'order_is_notice_list'				=>	array(
			0 => array('id' => 0, 'name' => '不通知', 'checked' => true),
			1 => array('id' => 1, 'name' => '通知'),
		),

	'order_is_disposal_title'			=> '是否处理',
	'order_is_notice_title'				=> '是否通知',
	'order_courier_title'				=> '单号信息',
);
?>