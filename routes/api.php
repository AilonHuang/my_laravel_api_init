<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api')->prefix('v1')->group(function () {
    Route::middleware('api.guard')->group(function() {
        //用户注册
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        //用户登录
        Route::post('/login', [UserController::class, 'login'])->name('users.login');
        Route::middleware('api.refresh')->group(function () {
            //当前用户信息
            Route::get('/users/info', [UserController::class, 'info'])->name('users.info');
            //用户列表
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
            //用户信息
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            //用户退出
            Route::post('/logout', [UserController::class, 'logout'])->name('users.logout');
        });
    });

    Route::middleware('admin.guard')->group(function() {
        //管理员注册
        Route::post('/admins', [AdminController::class, 'store'])->name('admins.store');
        //管理员登录
        Route::post('/admin/login', [AdminController::class, 'login'])->name('admins.login');
        Route::middleware('admin.refresh')->group(function () {
            //当前管理员信息
            Route::get('/admins/info', [AdminController::class, 'info'])->name('admins.info');
            //管理员列表
            Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
            //管理员信息
            Route::get('/admins/{user}', [AdminController::class, 'show'])->name('admins.show');
            //管理员退出
            Route::get('/admins/logout', [AdminController::class, 'logout'])->name('admins.logout');
        });
    });
});

