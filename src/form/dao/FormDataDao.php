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
 * 表单数据Dao操作
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class FormDataDao extends Dao
{
    use Instance;

    /**
     * 操作表
     *
     * @var string
     */
    protected $table = 'form_data';

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
     * @param integer $form_id  表单ID
     * @param array $data       请求数据
     * @param integer $uid      用户ID
     * @param string $ip        用户IP
     * @return integer
     */
    public function add(int $form_id, array $data, int $uid = 0, string $ip = '0.0.0.0'): int
    {
        $formInfo = FormDao::instance()->where('id', $form_id)->get();
        if (!$formInfo) {
            $this->error = '表单不存在';
            return 0;
        }
        if ($formInfo['status'] != FormEmun::FORM_STATUS['publish']) {
            $this->error = '表单未发布';
            return 0;
        }
        if ($formInfo['auth_login'] == FormEmun::LOGIN_AUTH['enable'] && $uid <= 0) {
            $this->error = '请先登录';
            return 0;
        }

        // 验证请求参数
        $config = json_decode($formInfo['config'], true);
        $rules = [];
        foreach ($config as $item) {
            $rule = [];
            if (isset($item['required']) && $item['required']) {
                $rule[] = 'required';
            }
            // 最大值
            if (isset($item['max']) && check('num', $item['max']) && $item['max'] > 0 && !in_array($item['type'], ['slider'])) {
                $rule[] = 'max:' . $item['max'];
            }
            // 最小值
            if (isset($item['min']) && check('num', $item['min'])  && $item['min'] > 0 && !in_array($item['type'], ['slider'])) {
                $rule[] = 'min:' . $item['min'];
            }
            // 最大长度
            if (isset($item['maxLength']) && check('num', $item['maxLength']) && $item['maxLength'] >= '0') {
                $rule[] = 'maxLength:' . $item['maxLength'];
            }
            $rules[$item['name']] = $rule;
        }

        $check = $this->validate()->rule($rules)->data($data)->check();
        if (!$check) {
            $this->error = '表单参数错误';
            return 0;
        }

        // 保存数据
        $data_id = $this->save([
            'form_id' => $formInfo['id'],
            'uid' => $uid,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'ip' => $ip
        ], true, true);
        if (!$data_id) {
            $this->rollback();
            $this->error = '新增失败';
            return 0;
        }

        return $data_id;
    }

    /**
     * 修改表单数据
     *
     * @param integer $id
     * @param array $data
     * @param integer $adminID
     * @return boolean
     */
    public function editData(int $id, array $data, int $adminID = 0)
    {
        $info = $this->where('id', $id)->get();
        if (!$info) {
            $this->error = '记录不存在';
            return false;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $this->error = '数据格式错误';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('modify form questionnaire data');
            $save = $this->where('id', $info['id'])->save(['data' => $json]);
            if (!$save) {
                $this->rollback();
                $this->error = '修改问卷表单内容失败';
                return false;
            }

            // 记录操作日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'form',
                    'action' => '修改问卷表单数据',
                    'content' => '修改问卷表单数据ID: ' . $info['id'],
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
            $this->error = '修改问卷表单数据异常';
            Logger::instance()->channel()->error('modify form questionnaire data exception, msg => ' . $e->getMessage());
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
        $query->json(['data']);
        // ID搜索
        if (isset($option['idx']) &&  $this->validate()->id($option['idx'])) {
            $query->where('id', intval($option['idx']));
        }
        // 表单ID搜索
        if (isset($option['form_id']) &&  $this->validate()->id($option['form_id'])) {
            $query->where('form_id', intval($option['form_id']));
        }
        // 用户ID
        if (isset($option['uid']) &&  $this->validate()->id($option['uid'])) {
            $query->where('uid', intval($option['uid']));
        }
        // 时间搜索
        if (isset($option['start_time']) && $this->validate()->int($option['start_time'])) {
            $query->where('update_time', '>=', intval($option['start_time']));
        }
        if (isset($option['end_time']) && $this->validate()->int($option['end_time'])) {
            $query->where('update_time', '<=', intval($option['end_time']));
        }
        // 数据字段查询
        if (isset($option['data_field']) && !empty($option['data_field']) && isset($option['data_value']) && !empty($option['data_value'])) {
            $query->whereLike('data->' . $option['data_field'], '%' . $option['data_value'] . '%');
        }

        // 排序字段，默认id
        $order = 'id';
        if (isset($option['order']) && in_array($option['order'], ['id', 'create_time', 'update_time'])) {
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
