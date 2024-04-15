<?php

declare(strict_types=1);

namespace plugins\form\dao;

use Throwable;
use mon\log\Logger;
use mon\thinkOrm\Dao;
use mon\util\Instance;
use plugins\admin\dao\AdminLogDao;
use plugins\form\contract\FormEmun;
use plugins\form\validate\FormValidate;

/**
 * 表单Dao操作
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class FormDao extends Dao
{
    use Instance;

    /**
     * 操作表
     *
     * @var string
     */
    protected $table = 'form';

    /**
     * 自动写入时间戳
     *
     * @var boolean
     */
    protected $autoWriteTimestamp = true;

    /**
     * 验证器
     *
     * @var string
     */
    protected $validate = FormValidate::class;

    /**
     * 新增
     *
     * @param array $data
     * @param integer $adminID
     * @return integer
     */
    public function add(array $data, int $adminID): int
    {
        $check = $this->validate()->data($data)->scope('add')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return 0;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Add form questionnaire');
            $field = ['title', 'img', 'remark', 'align', 'config', 'sort', 'auth_login', 'auth_edit'];
            $form_id = $this->allowField($field)->save($data, true, true);
            if (!$form_id) {
                $this->rollback();
                $this->error = '新增失败';
                return 0;
            }

            // 记录操作日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'form',
                    'action' => '新增问卷表单',
                    'content' => '新增问卷表单：' . $data['title'],
                    'sid' => $form_id
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败,' . AdminLogDao::instance()->getError();;
                    return 0;
                }
            }

            $this->commit();
            return $form_id;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '新增问卷表单异常';
            Logger::instance()->channel()->error('Add form questionnaire exception, msg => ' . $e->getMessage(), ['trace' => true]);
            return 0;
        }
    }

    /**
     * 编辑
     *
     * @param array $data
     * @param integer $adminID
     * @return boolean
     */
    public function edit(array $data, int $adminID): bool
    {
        $check = $this->validate()->data($data)->scope('edit')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }

        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '问卷表单不存在';
            return false;
        }
        if ($info['status'] == FormEmun::FORM_STATUS['publish']) {
            $this->error = '问卷表单已发布，无法编辑';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit form questionnaire');
            // 修改后状态改为草稿
            $data['status'] = FormEmun::FORM_STATUS['draft'];
            $field = ['title', 'img', 'remark', 'align', 'config', 'sort', 'status', 'auth_login', 'auth_edit'];
            $save = $this->allowField($field)->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '修改问卷表单失败';
                return false;
            }

            // 记录操作日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'form',
                    'action' => '编辑问卷表单',
                    'content' => '编辑问卷表单：' . $data['title'] . ', ID：' . $data['idx'],
                    'sid' => $data['idx']
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败,' . AdminLogDao::instance()->getError();;
                    return false;
                }
            }

            $this->commit();
            return true;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '编辑问卷表单异常';
            Logger::instance()->channel()->error('Edit form questionnaire exception, msg => ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 修改状态
     *
     * @param array $data 参数
     * @param integer $adminID  管理员ID
     * @return boolean
     */
    public function status(array $data, int $adminID): bool
    {
        $check = $this->validate()->data($data)->scope('status')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }

        // 获取用户信息
        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '获取用户信息失败';
            return false;
        }

        if ($data['status'] == $info['status']) {
            $this->error = '修改的状态与原状态一致';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('modify form questionnaire status');
            $saveData = ['status' => $data['status']];
            // 发布，更新发布时间
            if ($data['status'] == FormEmun::FORM_STATUS['publish']) {
                $saveData['publish_time'] = time();
            }
            $save = $this->where('id', $info['id'])->save($saveData);
            if (!$save) {
                $this->rollback();
                $this->error = '修改问卷表单状态失败';
                return false;
            }

            // 记录操作日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'form',
                    'action' => '修改问卷表单状态',
                    'content' => '修改问卷表单状态为: ' . $data['status'] . ', 问卷表单ID: ' . $info['id'],
                    'sid' => $info['id']
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败,' . AdminLogDao::instance()->getError();
                    return false;
                }
            }

            $this->commit();
            return true;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '修改问卷表单状态异常';
            Logger::instance()->channel()->error('modify form questionnaire status exception, msg => ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 查询列表
     *
     * @param array $data
     * @return array
     */
    public function getList(array $data): array
    {
        $limit = isset($data['limit']) ? intval($data['limit']) : 10;
        $page = isset($data['page']) && is_numeric($data['page']) ? intval($data['page']) : 1;

        // 查询
        $list = $this->scope('list', $data)->page($page, $limit)->all();
        $total = $this->scope('list', $data)->count();

        return [
            'list'      => $list,
            'count'     => $total,
            'pageSize'  => $limit,
            'page'      => $page
        ];
    }

    /**
     * 查询场景
     *
     * @param \mon\orm\db\Query $query
     * @param array $option
     * @return mixed
     */
    public function scopeList($query, array $option)
    {
        // ID搜索
        if (isset($option['idx']) &&  $this->validate()->id($option['idx'])) {
            $query->where('id', intval($option['idx']));
        }
        // 按名称
        if (isset($option['title']) && is_string($option['title']) && !empty($option['title'])) {
            $query->whereLike('title', trim($option['title']) . '%');
        }
        // 按状态
        if (isset($option['status']) && $this->validate()->int($option['status'])) {
            $query->where('status', intval($option['status']));
        }
        // 时间搜索
        if (isset($option['start_time']) && $this->validate()->int($option['start_time'])) {
            $query->where('create_time', '>=', intval($option['start_time']));
        }
        if (isset($option['end_time']) && $this->validate()->int($option['end_time'])) {
            $query->where('create_time', '<=', intval($option['end_time']));
        }

        // 排序字段，默认id
        $order = 'sort';
        if (isset($option['order']) && in_array($option['order'], ['id', 'create_time', 'update_time', 'sort'])) {
            $order = $option['order'];
        }
        // 排序类型，默认 DESC
        $sort = 'DESC';
        if (isset($option['sort']) && in_array(strtoupper($option['sort']), ['ASC', 'DESC'])) {
            $sort = $option['sort'];
        }

        return $query->order($order, $sort);
    }
}
