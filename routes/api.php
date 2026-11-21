<?php

use App\Http\Controllers\Api\V1\AttendanceApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 公開APIのルーティング
Route::prefix('v1')->group(function (): void {
    Route::get('/attendance-records', [AttendanceApiController::class, 'index']);

    Route::get('/attendance-records/{attendanceRecord}', [AttendanceApiController::class, 'show']);

    // 　Sanctumでの認証
    Route::middleware('auth:sanctum')->group(function (): void {

        Route::post('/attendance-records', [AttendanceApiController::class, 'store']);
    
        Route::patch('/attendance-records/{attendanceRecord}', [AttendanceApiController::class, 'update']);

        Route::delete('/attendance-records/{attendanceRecord}', [AttendanceApiController::class, 'destroy']);
    });
});
