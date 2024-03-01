<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\PostController;
use App\Http\Controllers\Api\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\Public\PostController as PublicPostController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/login', [LoginController::class, 'index']);

Route::group(['middleware' => 'auth:api'], function() {
    Route::post('/logout', [LoginController::class, 'logout']);
});


Route::prefix('admin')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('/dashboard', DashboardController::class);


        Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.index');
        Route::get('/permissions/all', [PermissionController::class, 'all'])->middleware('permission:permissions.index');

        Route::get('/roles/all', [RoleController::class, 'all'])->middleware('permission:roles.index');
        Route::apiResource('/roles', RoleController::class)->middleware('permission:roles.index|roles.store|roles.update|roles.delete');

        Route::apiResource('/users', UserController::class)->middleware('permission:users.index|users.store|users.update|users.delete');

        Route::get('/categories/all', [CategoryController::class, 'all'])->middleware('permission:categories.index');
        Route::apiResource('/categories', CategoryController::class)->middleware('permission:categories.index|categories.store|categories.update|categories.delete');

        Route::post('/posts/storeImagePost', [PostController::class, 'storeImagePost']);
        Route::apiResource('/posts', PostController::class)->middleware('permission:posts.index|posts.store|posts.update|posts.delete');
    });
});

Route::prefix('public')->group(function () {

    Route::get('/categories', [PublicCategoryController::class, 'index']);
    Route::get('/categories/{slug}', [PublicCategoryController::class, 'show']);

    Route::get('/posts', [PublicPostController::class, 'index']);
    Route::get('/posts/{slug}', [PublicPostController::class, 'show']);

});