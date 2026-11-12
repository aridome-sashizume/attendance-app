<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminLoginController;

use App\Http\Controllers\AttendanceController;

use App\Http\Controllers\AttendanceListController;

use App\Http\Controllers\AttendanceDetailController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm']);
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::post('/logout', [AdminLoginController::class, 'logout']);
});

Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance', [AttendanceController::class, 'store']);

    Route::get('/attendance/list', [AttendanceListController::class, 'index']);


    Route::get('/attendance/{attendanceRecord}',[AttendanceDetailController::class, 'show']);

    Route::post('/attendance/{attendanceRecord}',[AttendanceDetailController::class, 'store']);
});