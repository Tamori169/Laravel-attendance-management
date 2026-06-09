<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakRecord;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
            return redirect()->route('staffAttendances.create');
        }

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now('Asia/Tokyo')->toDateString(),
            'clock_in' => now('Asia/Tokyo'),
        ]);

        return redirect()->route('staffAttendance.create');
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

        return redirect()->route('staffAttendance.create');
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

        return redirect()->route('staffAttendance.create');
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

        return redirect()->route('staffAttendance.create');
    }

    public function index(Request $request)
    {
        $month = $request->query('month', now('Asia/Tokyo')->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breakRecords')
            ->where('user_id', auth()->id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($attendanceRecord) => $attendanceRecord->date->format('Y-m-d'));

        $attendanceRecordList = collect(CarbonPeriod::create($startOfMonth, $endOfMonth))
            ->map(fn ($date) => [
                'date' => $date,
                'attendance_record' => $attendanceRecords->get($date->format('Y-m-d'))
            ]);

        return view('staff.attendances.index', compact('currentMonth', 'attendanceRecordList'));
    }

    public function show($id)
    {
        $user = auth()->user();

        $attendanceRecord = AttendanceRecord::where('id', $id)
            ->firstOrFail();
        
        $breakRecords = BreakRecord::where('attendance_record_id', $id)->get();

        $attendanceCorrectRequest = AttendanceCorrectRequest::with('breakCorrectRequests')
            ->where('attendance_record_id', $id)
            ->where('request_status_id', 1)
            ->first();

        return view('staff.attendances.show', compact('user', 'attendanceRecord', 'breakRecords', 'attendanceCorrectRequest'));
    }
}