<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EditRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function show($id)
    {
        $attendanceRecord = AttendanceRecord::with('user')
            ->where('id', $id)
            ->firstOrFail();

        $breakRecords = BreakRecord::where('attendance_record_id', $id)->get();

        $attendanceCorrectRequest = AttendanceCorrectRequest::with('breakCorrectRequests')
            ->where('attendance_record_id', $id)
            ->where('request_status_id', 1)
            ->first();

        return view('admin.attendances.show', compact('attendanceRecord', 'breakRecords', 'attendanceCorrectRequest'));
    }

    public function update(EditRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $attendanceRecord = AttendanceRecord::findOrFail($id);

            $attendanceRecord->update([
                'clock_in' => $attendanceRecord->date->format('Y-m-d') . ' ' . $request->clock_in . ':00',
                'clock_out' => $attendanceRecord->date->format('Y-m-d') . ' ' . $request->clock_out . ':00',
                ]);

            BreakRecord::where('attendance_record_id', $id)
                ->delete();

            foreach ($request->input('breaks', []) as $break) {
                $breakIn = $break['break_in'] ?? null;
                $breakOut = $break['break_out'] ?? null;

                if (empty($breakIn) && empty($breakOut)) {
                    continue;
                }  
                
                BreakRecord::create([
                    'attendance_record_id' => $id,
                    'break_in' => $attendanceRecord->date->format('Y-m-d') . ' ' . $breakIn . ':00',
                    'break_out' => $attendanceRecord->date->format('Y-m-d') . ' ' . $breakOut . ':00',
                ]);
            }
        });

        return redirect()->route('adminAttendance.show', ['id' => $id]);
    }
}
