<?php

declare(strict_types=1);

namespace plugins\form\contract;

/**
 * 表单相关枚举属性
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
interface FormEmun
{
    /**
     * 表单状态
     * 
     * @var array
     */
    const FORM_STATUS = [
        // 已下线
        'disable'   => 0,
        // 草稿
        'draft'     => 1,
        // 已发布
        'publish'   => 2
    ];

    /**
     * 表单状态名称
     * 
     * @var array
     */
    const FORM_STATUS_TITLE = [
        // 已下线
        self::FORM_STATUS['disable'] => '已下线',
        // 草稿
        self::FORM_STATUS['draft']   => '草稿',
        // 已发布
        self::FORM_STATUS['publish'] => '已发布'
    ];

    /**
     * 表单对象方式
     * 
     * @var array
     */
    const FORM_ALIGN = [
        // 左对齐
        'left'  => 0,
        // 上对齐
        'top'   => 1,
    ];

    /**
     * 表单对象方式名称
     * 
     * @var array
     */
    const FORM_ALIGN_TITLE = [
        // 上对齐
        self::FORM_ALIGN['top']     => '上对齐',
        // 左对齐
        self::FORM_ALIGN['left']    => '左对齐'
    ];

    /**
     * 登录权限
     * 
     * @var array
     */
    const LOGIN_AUTH = [
        // 禁用
        'disable'   => 0,
        // 正常
        'enable'    => 1,
    ];

    /**
     * 登录权限名称
     * 
     * @var array
     */
    const LOGIN_AUTH_TITLE = [
        // 正常
        self::LOGIN_AUTH['enable']    => '需要登录',
        // 禁用
        self::LOGIN_AUTH['disable']   => '无需登录',
    ];

    /**
     * 编辑权限
     * 
     * @var array
     */
    const EDIT_AUTH = [
        // 禁用
        'disable'   => 0,
        // 正常
        'enable'    => 1,
    ];

    /**
     * 编辑权限名称
     * 
     * @var array
     */
    const EDIT_AUTH_TITLE = [
        // 禁用
        self::EDIT_AUTH['disable']   => '无法修改',
        // 正常
        self::EDIT_AUTH['enable']    => '允许修改',
    ];
}
