<?php
/*
|--------------------------------------------------------------------------
| 定义应用请求路由
|--------------------------------------------------------------------------
| 通过Route类进行注册
|
*/

use mon\env\Config;
use mon\http\Route;
use plugins\form\controller\FormController;
use plugins\admin\middleware\AuthMiddleware;
use plugins\admin\middleware\LoginMiddleware;
use plugins\form\controller\FormDataController;

/** @var Route $route */

Route::instance()->group(Config::instance()->get('admin.app.root_path', ''), function (Route $route) {

    $route->group(['path' => '/form', 'middleware' => [LoginMiddleware::class, AuthMiddleware::class]], function (Route $route) {
        // 问卷表单
        $route->group('', function (Route $route) {
            // 列表页
            $route->get('', [FormController::class, 'index']);
            // 预览
            $route->get('/preview', [FormController::class, 'preview']);
            // 新增
            $route->map(['get', 'post'], '/add', [FormController::class, 'add']);
            // 修改
            $route->map(['get', 'post'], '/edit', [FormController::class, 'edit']);
            // 修改状态
            $route->post('/status', [FormController::class, 'status']);
        });

        // 问卷表单数据
        $route->group('/data', function (Route $route) {
            // 列表页
            $route->get('', [FormDataController::class, 'index']);
            // 编辑
            $route->map(['get', 'post'], '/edit', [FormDataController::class, 'edit']);
            // 导出
            $route->get('/export', [FormDataController::class, 'export']);
        });
    });
});
