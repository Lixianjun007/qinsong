<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/7
 * Time: 上午10:47
 */

namespace Admin\Controller;

class CategoryController extends CommonController
{
    private $category;

    function __construct()
    {
        parent::__construct();
        $this->category = M('Category');
        $this->categories = $this->category->where("parent_id = 0")->order('sort_order asc')->select();
    }

    private function all_categories()
    {
        $categories = $this->category->where("parent_id = 0")->order('sort_order asc')->select();
        foreach ($categories as $key => $value) {
            $children = $this->category->where("parent_id = $value[id]")->order('sort_order asc')->select();
            $categories[$key]['children'] = $children;
        }

        return $categories;
    }

    /***
     * 分类管理首页
     */
    public function index()
    {
        $this->categories = $this->all_categories();
        $this->display();
    }


    /***
     * 新增分类
     */
    public function add()
    {
        if (IS_POST) {
            $this->category->create();
            $this->category->add();
            $this->success('新增分类成功', 'http://qinsongc.net/shop.php'.U('index'));
        } else {
            $this->display();
        }
    }

    /***
     * 编辑分类
     */
    public function edit()
    {
        $id = I('get.id');
        if (IS_POST) {
            $this->category->create();
            $this->category->where("id = '$id'")->save();
            $this->success('编辑分类成功', 'http://qinsongc.net/shop.php'.U('index'));
        } else {
            $cate = $this->category->find($id);
            $this->assign('cate', $cate);
            $this->display();
        }
    }

    /***
     * ajax删除
     */
    public function delete()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $count = $this->category->where("parent_id = '$id'")->count();
            if ($count > 0) {
                $info = array('status' => 0, 'msg' => '此分类下有二级分类，不能删除');
            } else {
                $this->category->delete($id);
                $info = array('status' => 1, 'msg' => '删除分类成功');
            }

            $this->ajaxReturn($info);
        }
    }


    /***
     * ajax改变属性---是否显示
     */
    public function change_attr()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $is_show = !I('post.is_show');
            $this->category->where("id = '$id'")->setField("is_show", $is_show);
        }
    }

    /***
     * ajax排序
     */
    public function sort_order()
    {
        if (IS_POST) {
            $id = I('post.id');
            $sort_order = I('post.sort_order');
            $this->category->where("id = '$id'")->setField('sort_order', $sort_order);
        }
    }


}

