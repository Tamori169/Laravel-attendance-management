<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Staff\AttendanceController as StaffAttendanceController;

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
});
