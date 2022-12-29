<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DepositController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

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

Route::post('/login', [UserController::class, 'login']);

Route::middleware(['auth:sanctum', 'role:superadmin|admin'])->group(function(){
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // User
    Route::get('/user/change-status/{user}', [UserController::class, 'changeStatus']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/profile/{id}', [UserController::class, 'getUserById']);
    // Roles
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::patch('/edit-role/{id}', [RoleController::class, 'update']);

    // permission
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::patch('/edit-permission/{id}', [PermissionController::class, 'update']);
    Route::get('/add-permission/{permissionId}/role/{roleId}', [PermissionController::class, 'givePermission']);
});

