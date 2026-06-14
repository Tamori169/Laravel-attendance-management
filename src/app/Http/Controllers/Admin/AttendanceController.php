<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', today('Asia/Tokyo')->format('Y-m-d'));
        $today = Carbon::parse($date);

        $attendanceRecords = AttendanceRecord::with('user')
            ->whereHas('user', function($query){
                $query->where('role_id', 1);
            })
            ->where('date', $date)
            ->get();

        return view('admin.attendances.index', compact('attendanceRecords', 'today'));
    }
}
