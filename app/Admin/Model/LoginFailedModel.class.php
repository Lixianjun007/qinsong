<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/1
 * Time: 下午2:22
 */
namespace Admin\Model;

use Think\Model;

class LoginFailedModel extends Model
{
    /***
     * 检查用户登录失败的次数是否等于5次
     * @return bool
     */
    function check_failed_times()
    {
        $ip = get_client_ip();
        $email = I("post.email");
        $error = $this->where("ip='$ip' and email='$email'")->find();
        if ($error['login_at'] + 60 * 60 > time() && $error['error_times'] == 5) {
            return false;
        }

        return true;
    }

    /***
     * 一小时内用户登录失败次数
     */
    function update_failed_times()
    {
        $ip = get_client_ip();
        $email = I("post.email");
        $error = $this->where("ip='$ip' and email='$email'")->find();
//        dump($error);exit;
        //如果之前没有记录
        if (!$error) {
            $data['ip'] = $ip;
            $data['email'] = $email;
            $data['login_at'] = time();
            $data['error_times'] = 1;
            $this->data($data)->add();
        }

        //如果已经有数据了，超过1小时了
        if ($error['login_at'] + 60 * 60 < time()) {
            $data2['id'] = $error['id'];
            $data2['login_at'] = time();
            $data2['error_times'] = 1;
            $this->data($data2)->save();
        }

        //没有超过1小时，不到5次，增加错误记录
        if ($error['login_at'] + 60 * 60 > time()) {
            $this->where("id='$error[id]'")->setInc('error_times');
        }
    }
}
