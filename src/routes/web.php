<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Staff\AttendanceController as StaffAttendanceController;
use App\Http\Controllers\Staff\CorrectionController as StaffCorrectionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 一般ユーザー用ミドルウェア認証グループ
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/attendance', [StaffAttendanceController::class, 'create'])
    ->name('staffAttendance.create');
    Route::post('/attendance/clock_in', [StaffAttendanceController::class, 'clockIn'])
        ->name('staffAttendance.clockIn');
    Route::post('/attendance/break_in', [StaffAttendanceController::class, 'breakIn'])
        ->name('staffAttendance.breakIn');
    Route::patch('/attendance/break_out', [StaffAttendanceController::class, 'breakOut'])
        ->name('staffAttendance.breakOut');
    Route::patch('/attendance/clock_out', [StaffAttendanceController::class, 'clockOut'])
        ->name('staffAttendance.clockOut');
    Route::get('/attendance/list', [StaffAttendanceController::class, 'index'])
        ->name('staffAttendance.index');
    Route::get('/attendance/detail/{id}', [StaffAttendanceController::class, 'show'])
        ->name('staffAttendance.show');
    Route::post('/attendance/detail/{id}', [StaffCorrectionController::class, 'store'])
        ->name('staffCorrection.store');
    Route::get('/stamp_correction_request/list', [StaffCorrectionController::class, 'index'])
        ->name('staffCorrection.index');
});

// 管理者処理
Route::view('/admin/login', 'auth.admin.login')
    ->middleware('guest')
    ->name('admin.login');

// 管理者用ミドルウェア認証グループ
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('adminAttendance.index');
});
