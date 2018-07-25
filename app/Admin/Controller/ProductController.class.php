<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/7
 * Time: 上午10:47
 */

namespace Admin\Controller;

//use Qiniu\Auth; //使用js直接上传到七牛，不通过PHP

class ProductController extends CommonController
{
    private $product;
    private $gallery;

    function __construct()
    {
        parent::__construct();
        $this->product = M('Product');
        $this->gallery = M('Gallery');
        $this->categories = $this->all_categories();
    }

    /***
     * 查出所有分类
     * @return mixed
     */
    private function all_categories()
    {
        $Category = M('Category');
        $categories = $Category->where("parent_id = 0")->order('sort_order asc')->select();
        foreach ($categories as $key => $value) {
            $children = $Category->where("parent_id = $value[id]")->order('sort_order asc')->select();
            $categories[$key]['children'] = $children;
        }

        return $categories;
    }


    /***
     * 商品管理首页
     */
    public function index()
    {
        $Product = D('Product');
        $name = I('get.name');
        $category_id = I('get.category_id');
        $map = [];
        if (isset($name)) {
            $map['name'] = array('like', "%" . $name . "%");  //按商品名
        }
        if (isset($category_id)) {
            $map['category_id'] = array('like', "%" . $category_id . "%");//按商品分类
        }

        $infoCount = $Product->where("status = 1")->count();
        $pageSize = 10;
        $Page = new \Common\Util\Page($infoCount, $pageSize);
        $pages = $Page->show();// 分页显示输出
        $products = $Product->where($map)->where("status = 1")->limit($Page->firstRow . ',' . $Page->listRows)->all_products();

        $datas = array('is_top', 'is_new', 'is_hot', 'is_recommend', 'is_onsale');
//        dump($datas);exit;
        $this->assign(compact('products', 'pages', 'datas'));
        $this->display();
    }


    /***
     * 新增商品
     */
    public function add()
    {
        $Product = D('Product');
        if (IS_POST) {
//            dump($_POST);exit;
            if (!$Product->create()) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($Product->getError());
            } else {
                // 验证通过 可以进行其他数据操作
                $Product->create();
                $id = $Product->add();
                if ($id) {
                    $this->success('新增商品成功', 'http://qinsongc.net/shop.php'.U('index'));
                }

                //把商品相册插入相册表
                $galleries = I('post.gallery');
                foreach ($galleries as $v) {
                    $gallery['img'] = $v;
                    $gallery['product_id'] = $id;
                    $this->gallery->where("product_id = '$id'")->add($gallery);
                }
            }
        } else {
            //使用js直接上传到七牛，不通过PHP
//            $bucket = 'hdjzq';
//            $accessKey = 'sDCa_uqbZSmgonwalGQMXJaCeU4Y5OBzYL-b09Ok';
//            $secretKey = 'Js0hw0oq0B_rTlCMdSwffZ_BINFtBexjng8iCaOK';
//            $auth = new Auth($accessKey, $secretKey);
//            $this->upToken = $auth->uploadToken($bucket);
            $this->display();
        }
    }

    /***
     * 编辑商品
     */
    public function edit()
    {
        $Product = D('Product');
        $id = I('get.id');
        if (IS_POST) {
            if (!$Product->create()) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($Product->getError());
            } else {
                // 验证通过 可以进行其他数据操作
                $Product->create();

                //把商品相册插入相册表,先判断相册表是否有该商品的相册，没有就插入新的图片
                $galleries = I('post.gallery');
                if ($galleries) {
                    //如果图片表已经有对应的图片，先把图片删掉，再插入新的图片
                    foreach ($galleries as $v) {
                        $gallery['img'] = $v;
                        $gallery['product_id'] = $id;
                        $this->gallery->add($gallery);
                    }
                }

                $Product->where("id = '$id'")->save();
                $this->success('编辑商品成功', 'http://qinsongc.net/shop.php'.U('index'));
            }
        } else {
            //查出要编辑的商品
            $product = $this->product->find($id);
            //查出要编辑的商品的相册
            $galleries = $this->gallery->where("product_id = '$id'")->select();
            $this->assign(compact('product', 'galleries'));
            $this->display();
        }
    }

    /***
     * ajax删除相册
     */
    public function remove_gallery()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $result = $this->gallery->where("id = '$id'")->delete();
            if ($result) {
                $info = array('status' => 1, 'msg' => '此相册已被删除');
            } else {
                $info = array('status' => 0, 'msg' => '删除失败');
            }

            $this->ajaxReturn($info);
        }
    }

    /***
     * ajax移至回收站(单条)
     * 注：删除商品时，只需改变商品的状态(即移至回收站)，对应的商品相册不动。
     */
    public function delete_product()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $result = $this->product->where("id = '$id' and is_onsale = '0'")->setField('status', 0);
            if ($result) {
                $info = array('status' => 1, 'msg' => '此商品已被移至回收站');
            } else {
                $info = array('status' => 0, 'msg' => '此商品为上架商品不能删除');
            }

            $this->ajaxReturn($info);
        }
    }

    /***
     * ajax移至回收站(多条)
     */
    public function delete_all()
    {
        if (IS_AJAX) {
            $ids = I('post.checked_id');
            $map['id'] = array('in', $ids);
            $result = $this->product->where($map)->setField('status', 0);
            if ($result) {
                $info = array('status' => 1, 'msg' => '此商品已被移至回收站');
                $this->ajaxReturn($info);
            }
        }
    }

    /***
     * ajax排序
     */
    public function sort_order()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $sort_order = I('post.sort_order');
            $this->product->where("id = '$id'")->setField('sort_order', $sort_order);
        }
    }

    /***
     * 改变库存
     */
    public function stock()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $stock = I('post.stock');
            $this->product->where("id = '$id'")->setField('stock', $stock);
        }
    }

    /***
     * 商品回收站首页
     */
    public function trash()
    {
        $Product = D('Product');
        $infoCount = $Product->where("status = 0")->count();
        $pageSize = 5;
        $Page = new \Common\Util\Page($infoCount, $pageSize);
        $pages = $Page->show();// 分页显示输出
        $products = $Product->where("status = 0")->limit($Page->firstRow . ',' . $Page->listRows)->all_products();
        $this->assign(compact('products', 'pages'));
        $this->display();
    }


    /***
     * 还原单条
     */
    public function back_one()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $result = $this->product->where("id = '$id'")->setField('status', 1);
            if ($result) {
                $info = array('status' => 1, 'msg' => '此商品已还原');
            } else {
                $info = array('status' => 0, 'msg' => '还原失败');
            }

            $this->ajaxReturn($info);
        }
    }

    /***
     * 还原多条
     */
    public function back_all()
    {
        if (IS_AJAX) {
            $ids = I('post.checked_id');
            $map['id'] = array('in', $ids);
            $result = $this->product->where($map)->setField('status', 1);
            if ($result) {
                $info = array('status' => 1, 'msg' => '此商品已还原');
                $this->ajaxReturn($info);
            }
        }
    }


    /***
     * 彻底删除单条
     */
    public function delete_one()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $result = $this->product->where("id = '$id'")->delete();
            if ($result) {
                $this->gallery->where("product_id = '$id'")->delete();
                $info = array('status' => 1, 'msg' => '此商品已彻底删除');
            } else {
                $info = array('status' => 0, 'msg' => '删除失败');
            }

            $this->ajaxReturn($info);
        }
    }


    /***
     * 彻底删除多条
     */
    public function delete_more()
    {
        if (IS_AJAX) {
            $ids = I('post.checked_id');
            $map['id'] = array('in', $ids);
            $result = $this->product->where($map)->delete();
            if ($result) {
                $this->gallery->where("product_id = $map[id]")->delete();
                $info = array('status' => 1, 'msg' => '此商品已彻底删除');
                $this->ajaxReturn($info);
            }
        }
    }

    /***
     * ajax改变属性
     */
    public function change_attr()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $attr = I('post.attr');
            $is_something = $this->product->find($id);
            $this->product->where("id = '$id'")->setField($attr, !$is_something[$attr]);
        }
    }


}

