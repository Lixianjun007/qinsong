<?php

namespace Home\Controller;

/**
 * 用户
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2017-03-02T22:48:35+0800
 */
class UserController extends CommonController
{
	/**
	 * [_initialize 前置操作-继承公共前置方法]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-02T22:48:35+0800
	 */
	public function _initialize()
	{
		// 调用父类前置方法
		parent::_initialize();
	}

	/**
	 * [Index 用户初始化]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-02T22:48:35+0800
	 */
	public function Index()
	{
		
	}

	/**
     * [GetAlipayUserInfo 获取支付宝用户信息]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2017-09-23T21:52:49+0800
     */
    public function GetAlipayUserInfo()
    {
        $result = (new \My\Alipay())->GetAlipayUserInfo(I('authcode'), C('alipay_mini_appid'));
        if($result === false)
        {
            $this->ajaxReturn('获取授权信息失败 Get Authorization failure');
        } else {
        	$data = [
            	'alipay_openid'		=> $result['user_id'],
            	'nick_name'			=> empty($result['nick_name']) ? '' : $result['nick_name'],
            	'avatar'			=> empty($result['avatar']) ? '' : $result['avatar'],
            	'gender'			=> empty($result['gender']) ? '' : $result['gender'],
            	'province'			=> empty($result['province']) ? '' : $result['province'],
            	'city'				=> empty($result['city']) ? '' : $result['city'],
            ];
            $m = M('user');
            $where = ['alipay_openid' => $result['user_id']];
        	$temp = $m->where($where)->find();
        	if(empty($temp))
        	{
        		$data['add_time'] = time();
        		if($m->add($data) <= 0)
        		{
        			$this->ajaxReturn('用户添加失败 user add failure');
        		}
        	} else {
        		$data['upd_time'] = time();
        		if($m->where($where)->save($data) === false)
        		{
        			$this->ajaxReturn('用户更新失败 user update failure');
        		}
        	}
        	$this->ajaxReturn('请求成功 success', 0, $m->where($where)->find());
        }
    }
}
?>