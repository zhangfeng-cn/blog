<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');

Route::group('', function () {

    Route::group('blog', function () {
        // 查询各页面数据
        Route::get('list', 'api/Home/lists')->middleware('Visitor');
        // 文章详细
        Route::get('article/:article_id', 'api/Home/article')->middleware('Read');
        // 验证是是否点赞
        Route::get('isThumb', 'api/Home/isThumb');
        // 更新点赞
        Route::put('updateThumb/:article_id', 'api/Home/updateThumb');
        // 关于博客
        Route::get('about/:id', 'api/Home/read');
    });

    // 验证登录、注册
    Route::post('login', 'api/Login/login');
    Route::post('getCaptcha', 'api/Login/getCaptcha');
    Route::get('nameExists', 'api/Login/nameExists');
    Route::post('register', 'api/Login/register');
    // 验证token
    Route::post('auth', 'api/Admin/auth');
    

    Route::group('admin', function () {
        // 退出登录
        Route::post('loginout', 'api/Admin/loginout');
        // 查询分类
        Route::get('publish', 'api/Admin/category');
        // 查询首页数据
        Route::get('admin', 'api/Admin/admin');
        //
        Route::resource('article', 'api/Admin');
        
        Route::get('getAuthors', 'api/Admin/getAuthors');
        
        Route::delete('article/deleteImg', 'api/Admin/deleteImg');
        
    })->middleware('Auth');
    // 上传
    Route::post('admin/article/upload', 'api/Admin/upload');
    
})->allowCrossDomain();

return [

];
