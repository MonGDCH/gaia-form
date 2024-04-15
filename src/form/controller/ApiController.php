<?php

declare(strict_types=1);

namespace plugins\form\controller;

use mon\http\Request;
use plugins\form\dao\FormDao;
use plugins\admin\dao\RegionDao;
use plugins\form\dao\FormDataDao;
use plugins\admin\comm\Controller;
use plugins\form\contract\FormEmun;

/**
 * 问卷表单接口
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class ApiController extends Controller
{
    /**
     * 填写问卷表单
     *
     * @param Request $request
     * @param integer $id
     * @return mixed
     */
    public function fill(Request $request, int $id)
    {
        $info = FormDao::instance()->where('id', $id)->where('status', FormEmun::FORM_STATUS['publish'])->get();
        if (!$info) {
            return $this->fetch('api/form', ['box' => 'error',]);
        }
        $uid = property_exists($request, 'uid') ? $request->uid : 0;
        if ($info['auth_login'] == FormEmun::LOGIN_AUTH['enable'] && $uid <= 0) {
            // TODO 跳转登录页面
            return $this->fetch('api/form', ['box' => 'error', 'msg' => '请先登录']);
        }

        // post请求，提交表单数据
        if ($request->isPost()) {
            $data = $request->post();
            $save = FormDataDao::instance()->add($id, $data, $uid, $request->ip());
            if (!$save) {
                return $this->error(FormDataDao::instance()->getError());
            }

            return $this->success('您的表单已经提交，感谢您的参与！');
        }

        $region = RegionDao::instance()->getTreeData(0);
        return $this->fetch('api/form', [
            'box' => 'form',
            'data' => $info,
            'region' => json_encode($region, JSON_UNESCAPED_UNICODE)
        ]);
    }
}
