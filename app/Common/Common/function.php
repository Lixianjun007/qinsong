<?php
import('Com.Email.PHPMailer');
import('Com.Email.SMTP');

//发邮件，重置密码
function send_mail($title, $content, $from, $to, $chart = 'utf-8', $attachment = '')
{
    $mail = new PHPMailer ();
    $mail->CharSet = $chart; // 设置采用gb2312中文编码
    $mail->IsSMTP('smtp'); // 设置采用SMTP方式发送邮件
    $mail->Host = "smtp.qq.com"; // 设置邮件服务器的地址
    $mail->Port = 25; // 设置邮件服务器的端口，默认为25
    $mail->From = "244500972@qq.com"; // 设置发件人的邮箱地址
    $mail->FromName = "Holy"; // 设置发件人的姓名
    $mail->SMTPAuth = true; // 设置SMTP是否需要密码验证，true表示需要
    $mail->Username = "244500972@qq.com"; // 设置发送邮件的邮箱
    $mail->Password = "huangdjzq201284"; // 设置邮箱的密码
    $mail->Subject = $title; // 设置邮件的标题
    $mail->AltBody = "text/html"; // optional, comment out and test
    $mail->Body = $content; // 设置邮件内容
    $mail->IsHTML(true); // 设置内容是否为html类型
    $mail->WordWrap = 50; // 设置每行的字符数
    $mail->AddReplyTo("地址", "名字"); // 设置回复的收件人的地址
    $mail->AddAddress($to, ""); // 设置收件的地址
    if ($attachment != '') {
        $mail->AddAttachment($attachment, $attachment);
    }
    if ($mail->Send()) {
        //$status1 = "$to" . '&nbsp;&nbsp;已投送成功<br />';
        $status = 1;

    } else {
        //$status2 = "$to" . '&nbsp;&nbsp;发送邮件失败<br />';
        $status = 0;
    }
    return $status;
}


/***
 * 密码截取并做加密处理
 * @param $password
 * @return bool|string
 */
function set_password($password)
{
    return substr(md5($password), 6, -6);
}

/***
 * 生成随机token
 * @return bool|string
 */
function make_token_code()
{
    return substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 3, 100), 2))), 0, 100);
}


/***
 * 递归删除目录
 * @param $directory
 */
function rm_dir($directory)
{
    //判断目录是否存在，如果不存在rmdir()函数会出错
    if (file_exists($directory)) {
        //打开目录返回目录资源，并判断是否成功
        if ($dir_handle = @opendir($directory)) {
            //遍历目录，读出目录中的文件或文件夹
            while ($filename = readdir($dir_handle)) {
                //一定要排除两个特殊的目录
                if ($filename != "." && $filename != "..") {
                    //将目录下的文件和当前目录相连
                    $sub_file = $directory . "/" . $filename;
                    //如果是目录条件则成立
                    if (is_dir($sub_file)) {
                        //递归调用自己删除子目录
                        rm_dir($sub_file);
                    }
                    //如果是文件条件则成立
                    if (is_file($sub_file)) {
                        //直接删除这个文件
                        unlink($sub_file);
                    }
                }
            }
            //关闭目录资源
            closedir($dir_handle);
            //删除空目录
            rmdir($directory);
        }
    }
}


/***
 * 改变属性
 * @param $model
 * @param $attr
 * @return string
 */
function is_something($model, $attr)
{
    if (isset($model[$attr]) && $model[$attr] == 1) {
        return '<span class="am-icon-check change_attr" data-attr="' . $attr . '"></span>';
    }

    return '<span class="am-icon-close change_attr" data-attr="' . $attr . '"></span>';
}


/***
 * 权限管理--用户管理
 * 判断数组中的元素是否存在于该数组中，存在则取出
 * @param $array
 * @param $key
 * @return array|void
 */
function pluck($array, $key)
{
    $result = [];
    foreach ($array as $k => $v) {
        if (!array_key_exists($key, $v)) {
            return;
        }
        $result[] = $v[$key];
    }
    return $result;
}



