<?php

use App\Http\Controllers\Api\V1\AttendanceRecordController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('v1')->group(function () {
    Route::apiResource('attendance-records', AttendanceRecordController::class)
        ->parameters([
            'attendance-records' => 'attendanceRecord',
        ])
        ->only(['index', 'show']);

    Route::apiResource('attendance-records', AttendanceRecordController::class)
        ->parameters([
            'attendance-records' => 'attendanceRecord',
        ])
        ->only(['store', 'update', 'destroy'])
        ->middleware('auth:sanctum');
});
