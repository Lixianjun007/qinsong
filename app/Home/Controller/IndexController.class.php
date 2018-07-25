<?php

namespace Home\Controller;

class IndexController extends CommonController
{
    /***
     * 商城前端首页
     */
    public function index()
    {
//        dump($_SESSION);exit;
        //首页广告
        $adverts = $this->advert->order('sort_order desc')->limit(4)->select();

        //新品
        $new_products = $this->product->where("is_new = 1 and is_onsale = 1 and status = 1")->limit(2)->select();
//        dump($new_products);exit;
        //热销
        $hot_products = $this->product->where("is_hot = 1 and is_onsale = 1 and status = 1")->limit(2)->select();

        //推荐
        $recommend_products = $this->product->where("is_recommend = 1 and is_onsale = 1 and status = 1")->limit(2)->select();

        $this->assign(compact('adverts', 'new_products', 'hot_products', 'recommend_products'));
        $this->display();
    }

    /***
     * 全部商品
     */
    public function all()
    {
        //查出符合条件的记录数
        $total = $this->product->where("status=1 and is_onsale=1")->count();
        //查出所有符合条件的商品
        $all_products = $this->product->where("status = 1 and is_onsale=1")->order('sort_order desc')->limit(2)->select();
        $this->assign(compact('total', 'all_products'));
        $this->display();
    }

    /***
     * 点击加载更多，每次加载显示两条
     */
    public function getMore()
    {
        if (IS_AJAX) {
            $page = I('post.page');
            $data = $this->product->where("status=1 and is_onsale=1")->limit($page, 2)
                ->order("sort_order desc")->select();
            $this->ajaxReturn($data);
        }
    }

    /***
     * 封装所有分类
     * @return mixed
     */
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
     * 所有分类
     */
    public function category()
    {
        $this->categories = $this->all_categories();
        $this->display();
    }

    /***
     * 商品详情
     */
    public function show()
    {
        $id = I('get.id');
        $product = $this->product->find($id);

        $galleries = $this->gallery->where("product_id = '$id'")->select();

        $this->assign(compact('product', 'galleries'));
        $this->display();
    }


}