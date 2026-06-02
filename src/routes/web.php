<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

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

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'create'])
    ->name('staff.attendances.create');
    Route::post('/attendance/clock_in', [AttendanceController::class, 'clockIn'])
        ->name('staff.attendances.clockIn');
    Route::post('/attendance/break_in', [AttendanceController::class, 'breakIn'])
        ->name('staff.attendances.breakIn');
    Route::patch('/attendance/break_out', [AttendanceController::class, 'breakOut'])
        ->name('staff.attendances.breakOut');
    Route::patch('/attendance/clock_out', [AttendanceController::class, 'clockOut'])
        ->name('staff.attendances.clockOut');
});
