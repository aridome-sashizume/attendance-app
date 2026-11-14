<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminLoginController;

use App\Http\Controllers\AttendanceController;

use App\Http\Controllers\AttendanceListController;

use App\Http\Controllers\AttendanceDetailController;

USE App\Http\Controllers\ApplicationListController;

use App\Http\Controllers\ApplicationDetailController;

use App\Http\Controllers\AdminAttendanceListController;

use App\Http\Controllers\AdminAttendanceDetailController;

use App\Http\Controllers\StaffListController;

use App\Http\Controllers\StaffAttendanceController;

use App\Http\Controllers\AdminApplicationDetailController;
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

/**
*  一般ユーザーのログイン後のルート
*/
Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance', [AttendanceController::class, 'store']);

    Route::get('/attendance/list', [AttendanceListController::class, 'index']);

    Route::get('/attendance/{attendanceRecord}',[AttendanceDetailController::class, 'show']
    );

    Route::post('/attendance/{attendanceRecord}', [AttendanceDetailController::class, 'store']
    );

    Route::get('/stamp_correction_request/list',[ApplicationListController::class, 'index']
    );


    Route::get('/application/{application}',[ApplicationDetailController::class, 'show']
    );
});

/**
*  管理者のログイン後のルート
*/
Route::middleware('auth')->group(function () {

    Route::get('/admin/attendance/list',[AdminAttendanceListController::class, 'index']);

    Route::post('/admin/attendance/{attendanceRecord}',[AdminAttendanceDetailController::class, 'update']);

    Route::get('/admin/staff/list', [StaffListController::class, 'index'])->name('admin.staff.list');

    Route::get('/admin/attendance/staff/{user}', [StaffAttendanceController::class, 'index'])->name('admin.staff.attendance.index');

    Route::get('/stamp_correction_request/approve/{application}',[AdminApplicationDetailController::class, 'show']);

    Route::post('/stamp_correction_request/approve/{application}',[AdminApplicationDetailController::class, 'update']
);
});