<?php

/**
 * 模块方法
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */

/**
 * [PowerCacheDelete 后台管理员权限缓存数据清除]
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2017-02-26T23:45:26+0800
 */
function PowerCacheDelete()
{
	$admin = M('Admin')->getField('id', true);
	if(!empty($admin))
	{
		foreach($admin as $id)
		{
			S(C('cache_admin_power_key').$id, null);
			S(C('cache_admin_left_menu_key').$id, null);
		}
	}
}

    function logToFile($errMsg, $logfile="/tmp/lxjerror.log")
    {
        $items = debug_backtrace();
        $errfile = $items[0]["file"];
        $errline = $items[0]["line"];
        
        $time = date("[Y-m-d H:i:s]");
        
        $str = $time."[".basename($errfile).":".$errline."] ".$errMsg."\r\n";
 
        error_log($str, 3, $logfile);
    }
?>