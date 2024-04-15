<?php

declare(strict_types=1);

namespace plugins\form\controller;

use mon\util\Tool;
use mon\http\Request;
use mon\http\Response;
use plugins\form\dao\FormDao;
use plugins\form\dao\FormDataDao;
use plugins\admin\comm\Controller;
use plugins\form\contract\FormEmun;

/**
 * 问卷表单
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class FormDataController extends Controller
{
    /**
     * 表单模型
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $form_id = $request->get('form_id');
        if (!check('id', $form_id)) {
            return $this->error('Request faild!');
        }
        $info = FormDao::instance()->where('id', $form_id)->get();
        if (!$info) {
            return $this->error('记录不存在');
        }

        if ($request->get('isApi')) {
            $option = $request->get();
            $result = FormDataDao::instance()->getList($option);
            return $this->success('ok', $result['list'], ['count' => $result['count']]);
        };

        return $this->fetch('data/index', [
            'data' => $info,
            'uid' => $request->uid,
            'user' => $info['auth_login'] == FormEmun::LOGIN_AUTH['enable']
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
        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('参数错误');
        }
        $info = FormDataDao::instance()->where('id', $id)->json(['data'])->get();
        if (!$info) {
            return $this->error('记录不存在');
        }

        // 修改
        if ($request->isPost()) {
            $option = $request->post();
            $save = FormDataDao::instance()->editData($info['id'], $option, $request->uid);
            if (!$save) {
                return $this->error(FormDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $config = FormDao::instance()->where('id', $info['form_id'])->json(['config'])->value('config');
        if (!$config) {
            return $this->error('问卷表单不存在');
        }

        return $this->fetch('data/edit', [
            'data' => $info,
            'config' => $config,
        ]);
    }

    /**
     * 导出
     *
     * @param Request $request
     * @return mixed
     */
    public function export(Request $request)
    {
        $id = $request->get('form_id');
        if (!check('id', $id)) {
            return $this->error('参数错误');
        }
        $info = FormDao::instance()->where('id', $id)->json(['config'])->get();
        if (!$info) {
            return $this->error('问卷不存在');
        }
        $config = $info['config'];
        $list = FormDataDao::instance()->where('form_id', $id)->json(['data'])->order('id', 'DESC')->all();

        $header = [];
        foreach ($config as $item) {
            $header[$item['name']] = [
                'style' => 'text-align:center;height:36px',
                'text' => $item['label']
            ];
        }
        $header['create_time'] = [
            'style' => 'text-align:center;height:36px',
            'text' => '提交时间'
        ];
        $header['update_time'] = [
            'style' => 'text-align:center;height:36px',
            'text' => '更新时间'
        ];
        $header['ip'] = [
            'style' => 'text-align:center;height:36px',
            'text' => '提交IP'
        ];;

        $data = [];
        foreach ($list as $value) {
            $itemData = [];
            foreach ($value['data'] as $key => $val) {
                $itemData[$key] = [
                    'style' => 'text-align:left;height:36px',
                    'text' => $val . ' ',
                ];
            }

            $data[] = array_merge($itemData, [
                'create_time' => [
                    'style' => 'text-align:center;height:36px',
                    'text' => date('Y-m-d H:i:s', $value['create_time'])
                ],
                'update_time' => [
                    'style' => 'text-align:center;height:36px',
                    'text' => date('Y-m-d H:i:s', $value['update_time'])
                ],
                'ip' => [
                    'style' => 'text-align:center;height:36px',
                    'text' => $value['ip'],
                ]
            ]);
        }

        $xls = Tool::instance()->exportExcel($info['title'], $data, $header, true, 'sheet1', false);
        return new Response(200, $xls['header'], $xls['content']);
    }
}
