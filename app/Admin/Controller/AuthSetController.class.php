<?php
/**
 * Created by PhpStorm.
 * User: holy
 * Date: 2017/11/2
 * Time: 下午6:37
 */

namespace Admin\Controller;

class AuthSetController extends CommonController
{
    function __construct()
    {
        parent::__construct();
        //查出所有用户组
        $Auth_group = M('Auth_group');
        $this->groups = $Auth_group->select();
    }

    /***
     * 加载用户管理首页(即管理员列表)
     */
    public function user_manage()
    {
        $User = D('User');

        $user_manages = $User->relation(true)->select();
        //统计管理员数量
        $this->usersNum = $User->count();

        $this->assign('user_manages', $user_manages);
        $this->display();
    }

    /***
     * 加载新增用户页面(即添加管理员)
     */
    public function add_user()
    {
        $User = D('User');
        $AuthGroupAccess = M('AuthGroupAccess');
        /***
         * 注：添加用户往用户表(user)和用户明细表(auth_group_access)同时插入数据
         */
        if (IS_POST) {
            if (!$User->create()) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($User->getError());
            } else {
                // 验证通过 可以进行其他数据操作
                $User->create();
                $User->password = set_password(I('post.password'));
                $User->token = make_token_code() . time();
                $User->created_at = time();

                $uid = $User->add();
                $group_ids = I('post.group_id');

                foreach ($group_ids as $v) {
                    $data['uid'] = $uid;
                    $data['group_id'] = $v;
                    $AuthGroupAccess->add($data);
                }

                $this->success('新增成功~', 'http://qinsongc.net/shop.php'.U('user_manage'));
            }
        } else {
            $this->display();
        }

    }

    /***
     * 编辑用户(即管理员)
     * 注：由于一个用户可以属于多个组，每个组有很多用户，此关联为多对多
     */
    public function edit_user()
    {
        $User = D('User');
        $AuthGroupAccess = M('AuthGroupAccess');

        $id = I('get.id');
        if (IS_POST) {
            $User->create();
            $User->password = set_password(I('post.password'));
            $User->token = make_token_code();
            $User->created_at = time();
            $User->where("id = '$id'")->save();

            //编辑用户组需要先删除用户原来所在的组，重新添加新的组
            $group_ids = I('post.group_id');
            $AuthGroupAccess->where("uid = '$id'")->delete();
            foreach ($group_ids as $v) {
                $data['uid'] = $id;
                $data['group_id'] = $v;
                $AuthGroupAccess->add($data);
            }
            $this->success('编辑用户组成功', 'http://qinsongc.net/shop.php'.U('user_manage'));
        } else {
            //关联查出要编辑的用户所有信息
            $user = $User->relation(true)->find($id);
            //取出用户所在的组的id
            $group_id = pluck($user['groups'], 'id');

            $this->assign(compact('user', 'group_id'));
            $this->display();
        }
    }

    /***
     * ajax删除用户(管理员)
     * 注：删除需要同时从两个表删除数据，用户表和用户明细表
     */
    public function delete_user()
    {
        $User = M('User');
        $Auth_group_access = M('Auth_group_access');
        $uid = I('post.uid');
        $Auth_group_access->where("uid = '$uid'")->delete();
        $result = $User->where("id = '$uid'")->delete();
        if ($result) {
            $info = array('status' => 1, 'msg' => '删除用户成功');
        } else {
            $info = array('status' => 0, 'msg' => '您没有权限进行此操作,请与管理员联系');
        }

        $this->ajaxReturn($info);
    }

    /***
     * 设置账号禁用启用
     */
    public function change_status()
    {
        $User = M('User');
        if (IS_AJAX) {
            $id = I('post.id');
            $status = !I('post.status');
            $User->where("id = '$id'")->setField("status", $status);
        }
    }

    /***
     * 用户组管理列表
     */
    public function group_manage()
    {
        //从用户组表中查出所有用户(auth_group)
        $Auth_group = M('Auth_group');
        $infoCount = $Auth_group->count();
        $this->assign('infoCount', $infoCount);
        $this->display();
    }


    /***
     * 添加用户组
     */
    public function add_group()
    {
        $Auth_group = M('Auth_group');
        if (IS_POST) {
            $data['rules'] = I('post.rules');
            if (empty($data['rules'])) {
                $this->error('权限不能为空');
            }
            $data['title'] = I('post.title');
            if ($data['title'] == "") {
                $this->error('请输入用户组名称');
            }

            //用户组名称不能重复
            $title = $Auth_group->where("title = '$data[title]'")->find();
            if (isset($title)) {
                $this->error('用户组名称已存在');
            }
            $data['rules'] = implode(',', $data['rules']);
            $data['create_time'] = time();
            $data['status'] = 1;
            $Auth_group->add($data);
            $this->success('添加用户组成功', 'http://qinsongc.net/shop.php'.U('AuthSet/group_manage'));
        } else {
            //调用模型的方法查出用户组对应的权限
            $AuthRule = D('AuthRule');
            $this->rules = $AuthRule->categoryTree();
            $this->display();
        }
    }

    /***
     * 编辑用户组和用户组的权限
     */
    public function edit_group()
    {
        $id = I('get.id');//用户组的ID
        $Auth_group = M('Auth_group');
        if (IS_POST) {
            $data['title'] = trim(I('post.title'));
            $data['rules'] = implode(',', I('post.rule_id'));
            $Auth_group->where("id = '$id'")->save($data);
            $this->success('编辑用户组成功', 'http://qinsongc.net/shop.php'.U('group_manage'));
        } else {
            $result = $Auth_group->find($id);

            //调用模型的方法查出用户组对应的权限
//            $AuthRule = D('AuthRule');
//            $rules = $AuthRule->categoryTree();
//            dump($rules);exit;
            $this->assign(compact('result'));
            $this->display();
        }

    }


    /***
     * 删除用户组
     */
    public function delete_group()
    {
        $Auth_group = M('Auth_group');
        if (IS_AJAX) {
            $id = I('post.id');
            $result = $Auth_group->delete($id);
            if ($result) {
                $info = array('status' => 1, 'msg' => '删除用户成功');
            } else {
                $info = array('status' => 0, 'msg' => '您没有权限进行此操作,请与管理员联系');
            }
            $this->ajaxReturn($info);
        }
    }


    /***
     * 权限菜单列表
     */
    public function rule_manage()
    {
        $this->display();
    }


    /***
     * 添加权限菜单
     */
    public function add_rule()
    {
        $AuthRule = M('AuthRule');
        if (IS_AJAX) {
            $AuthRule->create();
            $id = $AuthRule->add();
            if ($id) {
                $info = array('status' => 1, 'msg' => '添加菜单权限成功');
            } else {
                $info = array('status' => 0, 'msg' => '您没有权限进行此操作,请与管理员联系');
            }

            $this->ajaxReturn($info);
        }
    }

    public function edit_menu()
    {

    }


    /***
     * 删除菜单
     * 注：分别判断一、二级菜单是否有菜单，如果有菜单，不能删除。
     */
    public function delete_menu()
    {
        $AuthRule = M('AuthRule');
        if (IS_AJAX) {
            $id = I('post.id');
            $count = $AuthRule->where("parent_id = '$id'")->find();
            if ($count > 0) {
                $info = array('status' => 0, 'msg' => '此菜单下有菜单，不能删除');
            } else {
                $AuthRule->delete($id);
                $info = array('status' => 1, 'msg' => '删除菜单成功');
            }
            $this->ajaxReturn($info);
        }
    }


}