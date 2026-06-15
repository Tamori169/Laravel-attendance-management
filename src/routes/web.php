<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CorrectionController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\CorrectionController as AdminCorrectionController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
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
});

// 管理者ログイン
Route::view('/admin/login', 'auth.admin.login')
    ->middleware('guest')
    ->name('admin.login');

// 管理者用ミドルウェア認証グループ
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('adminAttendance.index');
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'edit'])
        ->name('adminAttendance.edit');
    Route::patch('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('adminAttendance.update');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}',
    [AdminCorrectionController::class, 'edit'])
        ->name('adminCorrection.edit');
    Route::patch('/stamp_correction_request/approve/{attendance_correct_request_id}',
    [AdminCorrectionController::class, 'update'])
        ->name('adminCorrection.update');
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
        ->name('adminStaff.index');
    Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'show'])
        ->name('adminStaff.show');
    Route::get('/admin/attendance/staff/{id}/export', [AdminStaffController::class, 'export'])
        ->name('adminStaff.export');
});

// 申請一覧画面（パス共有のためミドルウェア認証で区別）
Route::get('/stamp_correction_request/list', [CorrectionController::class, 'index'])
    ->middleware(['auth', 'staff.verified'])
    ->name('correction.index');
