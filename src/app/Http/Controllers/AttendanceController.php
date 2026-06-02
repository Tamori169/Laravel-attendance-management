<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function create()
    {
        return view('staff.attendances.create');
    }

    public function clockIn()
    {
        $user = auth()->user();

        if($user->attendanceStatus !== '勤務外'){
            return redirect()->route('staff.attendances.create');
        }

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now('Asia/Tokyo')->toDateString(),
            'clock_in' => now('Asia/Tokyo'),
        ]);

        return redirect()->route('staff.attendances.create');
    }

    public function breakIn()
    {
        $attendanceRecord = AttendanceRecord::where('user_id', auth()->id())
            ->where('date', now('Asia/Tokyo')->toDateString())
            ->first();

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => now('Asia/Tokyo'),
        ]);

        return redirect()->route('staff.attendances.create');
    }

    public function breakOut()
    {
        $attendanceRecord = AttendanceRecord::where('user_id', auth()->id())
            ->where('date', now('Asia/Tokyo')->toDateString())
            ->first();

        $breakRecord = BreakRecord::where('attendance_record_id', $attendanceRecord->id)
            ->whereNull('break_out')
            ->first();

        BreakRecord::where('id', $breakRecord->id)
            ->update([
                'break_out' => now('Asia/Tokyo'),
            ]);

        return redirect()->route('staff.attendances.create');
    }

    public function clockOut()
    {
        $attendanceRecord = AttendanceRecord::where('user_id', auth()->id())
            ->where('date', now('Asia/Tokyo')->toDateString())
            ->first();

        AttendanceRecord::where('id', $attendanceRecord->id)
            ->update([
                'clock_out' => now('Asia/Tokyo'),
            ]);

        return redirect()->route('staff.attendances.create');
    }
}
