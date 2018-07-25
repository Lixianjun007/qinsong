<?php
// +----------------------------------------------------------------------
// | CoreThink [ Simple Efficient Excellent ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.corethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: jry <598821125@qq.com> <http://www.corethink.cn>
// +----------------------------------------------------------------------
namespace Admin\Controller;

use Think\Controller;
// 引入鉴权类
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;

class UploadfileController extends Controller
{
    /**
     * 上传
     */
    public function upload()
    {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->rootPath = './upload/image/'; // 设置附件上传根目录
        $upload->savePath = ''; // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();

        $info = $info['imgFile'];
        $filePath = getcwd() . '/upload/image/' . $info['savepath'] . $info['savename'];
//        dump($info);exit;
        //上传到七牛
        $this->qiniu_upload($filePath);
        //拼接图片地址返回给前端显示
        $info['real_path'] = 'http://oi6a6qhed.bkt.clouddn.com/' . $info['savename'];
        $this->ajaxReturn($info);
    }

    function qiniu_upload($filePath)
    {
        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = 'sDCa_uqbZSmgonwalGQMXJaCeU4Y5OBzYL-b09Ok';
        $secretKey = 'Js0hw0oq0B_rTlCMdSwffZ_BINFtBexjng8iCaOK';
        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        // 要上传的空间
        $bucket = 'hdjzq';
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        // 上传到七牛后保存的文件名
        $key = basename($filePath);
        // 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传
        $uploadMgr->putFile($token, $key, $filePath);

        //删除本地图片
        unlink($filePath);
    }

}
