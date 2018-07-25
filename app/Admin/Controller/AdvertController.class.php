<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/10
 * Time: 上午1:41
 */

namespace Admin\Controller;

class AdvertController extends CommonController
{
    private $advert;

    function __construct()
    {
        parent::__construct();
        $this->advert = M('Advert');
    }

    /***
     * 广告首页
     */
    public function index()
    {
        $adverts = $this->advert->order('sort_order desc')->select();
        $this->assign('adverts', $adverts);
        $this->display();
    }

    /***
     * 新增广告
     */
    public function add()
    {
        if (IS_POST) {
            $this->advert->create();
            $this->advert->add();
            $this->success('新增广告成功', 'http://qinsongc.net/shop.php'.U('index'));
        } else {
            $this->display();
        }
    }


    /***
     * 编辑广告
     */
    public function edit()
    {
        $id = I('get.id');
        if (IS_POST) {
            $this->advert->create();
            $this->advert->where("id = '$id'")->save();
            $this->success('编辑广告成功', 'http://qinsongc.net/shop.php'.U('index'));
        } else {
            $advert = $this->advert->find($id);
            $this->assign('advert', $advert);
            $this->display();
        }
    }

    /***
     * 删除单条
     */
    public function delete_one()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $this->advert->delete($id);
        }
    }

    /***
     * 删除多条
     */
    public function delete_all()
    {
        if (IS_AJAX) {
            $checked_id = I('post.checked_id');
            //第一种
            $id = implode(',', $checked_id);
            $this->advert->delete($id);
            //第二种
//            foreach ($checked_id as $item) {
//                $this->advert->delete($item);
//            }
        }
    }

    /***
     * 排序
     */
    public function sort_order()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $sort_order = I('post.sort_order');
            $this->advert->where("id = '$id'")->setField('sort_order', $sort_order);
        }
    }

}