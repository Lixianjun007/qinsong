<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/12
 * Time: 下午9:03
 */

namespace Admin\Controller;

use Think\Controller;

class UserController extends Controller
{
    /***
     * 后台管理员登录
     */
    public function login()
    {
        $User = M('User');
        $LoginFailed = D("LoginFailed");
        if (IS_POST) {
            $success = true;
            $map['username|email|mobile'] = trim(I('post.email'));
            $map['password'] = set_password(I('post.password'));

            //有记录，并且错误时间小于1小时，登录次数达到了5次
            if (!$LoginFailed->check_failed_times()) {
                $this->error('您已经登录错误超过5次，请一个小时后再登录');
            }

            $user = $User->where($map)->find();
            if (!$user) {
                $this->error('用户名或密码输入错误');
            }
            //禁用的账号不允许登录
            if ($user['status'] == 0) {
                $this->error('您的账号已被禁用，请联系管理员');
            }

            if ($user['password'] != $map['password']) {
                $success = false;
            }

            if (!$success) {
                $LoginFailed->update_failed_times();
                $this->error('您的账号已被禁用，请联系管理员');
            }

            if ($_POST['remember']) {
                //设置cookie
                setcookie('token', $user['token'], time() + 60 * 60 * 24 * 7, '/');
            } else {
                setcookie('token', $user['token'], null, '/');
            }

            $_SESSION['user'] = $user;
            $this->success('您已成功登录', 'http://qinsongc.net/shop.php'.U('Index/index'));
        } else {
            $this->display();
        }
    }

    /***
     * 后台管理员注册
     */
//    public function register()
//    {
//        $User = M('User');
//        if (IS_AJAX) {
//            $data['username'] = I('post.username');
//            $data['email'] = I('post.email');
//            $data['mobile'] = I('post.mobile');
//            $data['token'] = make_token_code();
//            $data['password'] = set_password(I('post.password'));
//            $check_password = I('post.check_password');
//
//            $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
//            $telephone = "#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#";
//
//            $username = $User->where("username = '$data[username]'")->find();
//            $email = $User->where("email = '$data[email]'")->find();
//            $mobile = $User->where("mobile = '$data[mobile]'")->find();
//
//            $info = array('status' => 1, 'msg' => '注册成功');
//
//            if ($username) {
//                $info = array('status' => 0, 'msg' => '此用户名已被注册');
//            } elseif ($email) {
//                $info = array('status' => 0, 'msg' => '此邮箱已被注册');
//            } elseif ($mobile) {
//                $info = array('status' => 0, 'msg' => '该手机号已被注册');
//            } elseif (strlen($data['username']) < 5) {
//                $info = array('status' => 0, 'msg' => '用户名长度不能小于5位');
//            } elseif (!preg_match($pattern, $data['email'])) {
//                $info = array('status' => 0, 'msg' => '请输入正确的邮箱');
//            } elseif (!preg_match($telephone, $data['mobile'])) {
//                $info = array('status' => 0, 'msg' => '请输入正确的手机号');
//            } elseif ($check_password != I('post.password')) {
//                $info = array('status' => 0, 'msg' => '两次密码输入不一致');
//            }
//
//            if ($info['status'] == 1) {
//                $User->add($data);
//            }
//
//            $this->ajaxReturn($info);
//        } else {
//            $this->display();
//        }
//    }


    /***
     * 验证码配置
     */
    public function verify()
    {
        $config = array(
            'fontSize' => 20,    // 验证码字体大小
            'length' => 4,     // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'codeSet' => '0123456789'
        );

        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    /***
     * ajax验证验证码
     */
    function check_verify()
    {
        $code = I('get.code');
        $verify = new \Think\Verify();
        $this->ajaxReturn($verify->check($code));
    }

    /**
     * 密码重置
     */
    public function password_reset()
    {
        $this->display();
    }

    /**
     * 找回密码
     */
    public function findpwd()
    {
        $map['email'] = $_POST['email'];
        $token = make_token_code();  //生成token
        M('User')->where($map)->setField('token', $token);
        $info = M('User')->where($map)->find();
        if ($info) {
            $url = $_SERVER ['HTTP_HOST'] . 'http://qinsongc.net/shop.php'.U('User/editpwd') . '?token=' . $info['token'];
            // 调用发送邮件函数
            $username = $info ['username'];
            $title = "找回密码";

            $content = "<h3>亲爱的：$username 用户</h3>
            <br><br>http://$url
			<br><br><br><h4>有效期30分钟</h4>
			<br><br>
			<img src='__PUBLIC__/images/qq.png'>";

            $from = "244500972@qq.com"; //修改为你的发送邮箱
            $to = $info ['email'];
            $status = send_mail($title, $content, $from, $to);
            if ($status == 1) {
                $this->success('发送邮件成功,请登录邮箱重设密码~', 'http://qinsongc.net/shop.php'.U('User/email'), 1);
            } else {
                $this->error('发送邮件失败...');
                exit ();
            }

        } else {
            $this->error('此邮箱未注册');
            exit ();
        }
    }

    /**
     * 修改密码
     */
    public function editpwd()
    {

        $_SESSION ['userToken'] = array(
            'token' => trim($_GET ['token'])
        );
        $this->display();
    }

    /**
     * 执行修改操作
     */
    public function doeditpwd()
    {
        if (IS_AJAX) {
            $password = I('post.password');
            $check_password = I('post.check_password');

            if (strlen($password) < 6) {
                $info = array('status' => 0, 'msg' => '密码不能小于6位');
            } elseif (strlen($check_password) < 6) {
                $info = array('status' => 0, 'msg' => '确认密码不能小于6位');
            } elseif ($password != $check_password) {
                $info = array('status' => 0, 'msg' => '两次密码输入不一致');
            } else {
                $token = implode("", $_SESSION['userToken']); //将数组转换成字符串
                $userInfo = M('User')->where("token = '$token'")->find(); //查出数据库中当前token对应的用户
                $str = set_password(I('post.password')); //对表单提交过来的密码做截取处理。
                M('User')->where("id = '$userInfo[id]'")->setField('password', $str);
                $info = array('status' => 1, 'msg' => '修改成功');
            }

            $this->ajaxReturn($info);
        }

    }


    /***
     * 管理员退出
     */
    public function logout()
    {
        //使用cookie之后清空当前用户的session
        $_SESSION = array();

        //删除session后, 再清除对应的cookie，session_name(), cookie中储存的session标识, 即key
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), "", time() - 3600, "/");
        }

        //当选中记住密码时，用户退出后需清除cookie
        if(isset($_COOKIE['token'])) {
            setcookie('token', "", time() - 3600, "/");
        }

        session_destroy();

        $this->success('您已成功退出', 'http://qinsongc.net/shop.php'.U('User/login'));
    }

    /***
     * 加载无权限页面
     */
    public function no_auth()
    {
        $this->display();
    }
}