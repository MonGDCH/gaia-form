CREATE TABLE IF NOT EXISTS `form` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(100) NOT NULL COMMENT '表单名称',
    `img` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图片',
    `remark` varchar(250) NOT NULL DEFAULT '' COMMENT '表单备注',
    `align` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '表单对齐方式:1上对象0左对齐',
    `config` text NOT NULL COMMENT '表单配置信息',
    `sort` tinyint(3) unsigned NOT NULL DEFAULT '50' COMMENT '排序权重',
    `auth_login` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要登录',
    `auth_edit` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '填写后是否允许修改',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态:0已下线 1草稿 2已发布',
    `publish_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
    `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
    `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '问卷表单表';

CREATE TABLE IF NOT EXISTS `form_data` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `form_id` int(10) unsigned NOT NULL COMMENT '表单ID',
    `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '填写人ID',
    `data` text NOT NULL COMMENT '提交的数据',
    `ip` varchar(50) NOT NULL COMMENT '创建IP',
    `update_time` int(10) unsigned NOT NULL COMMENT '数据更新时间',
    `create_time` int(10) unsigned NOT NULL COMMENT '数据创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `form_id` (`form_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '表单提交数据表';