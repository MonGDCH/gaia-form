<?php

declare(strict_types=1);

namespace plugins\form\controller;

use mon\http\Request;
use plugins\form\dao\FormDao;
use plugins\admin\dao\RegionDao;
use plugins\admin\comm\Controller;
use plugins\form\contract\FormEmun;

/**
 * 问卷表单
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class FormController extends Controller
{
    /**
     * 表单模型
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->get('isApi')) {
            $option = $request->get();
            $result = FormDao::instance()->getList($option);
            return $this->success('ok', $result['list'], ['count' => $result['count']]);
        };

        return $this->fetch('form/index', [
            'uid' => $request->uid,
            'status' => FormEmun::FORM_STATUS_TITLE,
            'auth_edit' => json_encode(FormEmun::EDIT_AUTH_TITLE, JSON_UNESCAPED_UNICODE),
            'auth_login' => json_encode(FormEmun::LOGIN_AUTH_TITLE, JSON_UNESCAPED_UNICODE),
            'statusList' => json_encode(FormEmun::FORM_STATUS_TITLE, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * 新增
     *
     * @param Request $request
     * @return mixed
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $config = $request->post('config', '', false);
            $option['config'] = $config;
            $option['img'] = '';
            // 固定头像
            $save = FormDao::instance()->add($option, $request->uid);
            if (!$save) {
                return $this->error(FormDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $region = RegionDao::instance()->getTreeData(0);
        return $this->fetch('form/add', [
            'align' => 1,
            'region' => json_encode($region, JSON_UNESCAPED_UNICODE),
            'align' => FormEmun::FORM_ALIGN_TITLE,
            'auth_edit' => FormEmun::EDIT_AUTH_TITLE,
            'auth_login' => FormEmun::LOGIN_AUTH_TITLE
        ]);
    }

    /**
     * 编辑
     *
     * @param Request $request
     * @return mixed
     */
    public function edit(Request $request)
    {
        // 修改
        if ($request->isPost()) {
            $option = $request->post();
            $config = $request->post('config', '', false);
            $option['config'] = $config;
            $option['img'] = '';
            $save = FormDao::instance()->edit($option, $request->uid);
            if (!$save) {
                return $this->error(FormDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('参数错误');
        }

        $info = FormDao::instance()->where('id', $id)->get();
        if (!$info) {
            return $this->error('表单模型不存在');
        }

        return $this->fetch('form/edit', [
            'data' => $info,
            'align' => FormEmun::FORM_ALIGN_TITLE,
            'auth_edit' => FormEmun::EDIT_AUTH_TITLE,
            'auth_login' => FormEmun::LOGIN_AUTH_TITLE
        ]);
    }

    /**
     * 修改状态
     *
     * @param Request $request
     * @return mixed
     */
    public function status(Request $request)
    {
        $id = $request->post('idx');
        if (!check('id', $id)) {
            return $this->error('参数错误');
        }

        $option = $request->post();
        $save = FormDao::instance()->status($option, $request->uid);
        if (!$save) {
            return $this->error(FormDao::instance()->getError());
        }
        return $this->success('操作成功');
    }

    /**
     * 预览
     *
     * @return mixed
     */
    public function preview()
    {
        $region = RegionDao::instance()->getTreeData(0);
        return $this->fetch('form/preview', [
            'region' => json_encode($region, JSON_UNESCAPED_UNICODE)
        ]);
    }
}
