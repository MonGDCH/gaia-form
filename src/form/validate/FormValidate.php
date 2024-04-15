<?php

declare(strict_types=1);

namespace plugins\form\validate;

use mon\util\Validate;
use plugins\form\contract\FormEmun;

/**
 * 表单验证器
 *
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class FormValidate extends Validate
{
    /**
     * 验证规则
     *
     * @var array
     */
    public $rule = [
        'idx'       => ['required', 'id'],
        'title'     => ['required', 'str'],
        'img'       => ['isset', 'str'],
        'remark'    => ['isset', 'str', 'maxLength:250'],
        'align'     => ['required', 'align'],
        'config'    => ['required', 'json'],
        'sort'      => ['required', 'int', 'min:0'],
        'auth_login' => ['required', 'auth_login'],
        'auth_edit' => ['required', 'auth_edit'],
        'status'    => ['required', 'status'],
    ];

    /**
     * 错误提示信息
     *
     * @var array
     */
    public $message = [
        'idx'       => '参数异常',
        'title'     => '请输入表单名称',
        'img'       => '请上传封面图片',
        'remark'    => '请输入合法的备注',
        'align'     => '请选择表单对齐方式',
        'config'    => '表单设计参数异常',
        'sort'      => '请输入排序权重',
        'auth_login' => '请选择是否需要登录权限',
        'auth_edit' => '请选择是否允许编辑修改',
        'status'    => '请选择正确的状态',
    ];

    /**
     * 验证场景
     *
     * @var array
     */
    public $scope = [
        // 新增
        'add'       => ['title', 'img', 'remark', 'align', 'config', 'sort', 'auth_login', 'auth_edit'],
        // 编辑
        'edit'      => ['idx', 'title', 'img', 'remark', 'align', 'config', 'sort', 'auth_login', 'auth_edit'],
        // 修改状态
        'status'    => ['idx', 'status'],
    ];

    /**
     * 是否需要登录合法值
     *
     * @param string $value
     * @return boolean
     */
    public function auth_login($value): bool
    {
        return isset(FormEmun::LOGIN_AUTH_TITLE[$value]);
    }

    /**
     * 是否允许编辑修改合法值
     *
     * @param string $value
     * @return boolean
     */
    public function auth_edit($value): bool
    {
        return isset(FormEmun::EDIT_AUTH_TITLE[$value]);
    }

    /**
     * 对齐合法值
     *
     * @param string $value
     * @return boolean
     */
    public function align($value): bool
    {
        return isset(FormEmun::FORM_ALIGN_TITLE[$value]);
    }

    /**
     * 状态合法值
     *
     * @param string $value
     * @return boolean
     */
    public function status($value): bool
    {
        return isset(FormEmun::FORM_STATUS_TITLE[$value]);
    }
}
