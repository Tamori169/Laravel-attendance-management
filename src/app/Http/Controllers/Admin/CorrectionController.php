<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakCorrectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CorrectionController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab');

        if ($tab === 'approved') {
            $statusId = 2;
        } else {
            $statusId = 1;
        }

        $attendanceCorrectRequests = AttendanceCorrectRequest::with([
            'attendanceRecord.user',
            'requestStatus',
        ])
            ->whereHas('attendanceRecord.user', function ($query) {
                $query->where('role_id', 1);
                    })
            ->where('request_status_id', $statusId)
            ->latest()
            ->get();

        return view('admin.corrections.index', compact('attendanceCorrectRequests'));
    }

    public function edit($attendance_correct_request_id)
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::with([
            'attendanceRecord.user',
            'breakCorrectRequests'
        ])
            ->findOrFail($attendance_correct_request_id);

        return view('admin.corrections.edit', compact('attendanceCorrectRequest'));
    }

    public function update($attendance_correct_request_id)
    {
        DB::transaction(function () use ($attendance_correct_request_id) {
            $attendanceCorrectRequest = AttendanceCorrectRequest::with([
                'attendanceRecord',
                'breakCorrectRequests',
            ])->findOrFail($attendance_correct_request_id);

            $attendanceRecord = $attendanceCorrectRequest->attendanceRecord;

            $attendanceRecord->update([
                'clock_in'  => $attendanceCorrectRequest->requested_clock_in,
                'clock_out' => $attendanceCorrectRequest->requested_clock_out,
            ]);

            $attendanceRecord->breakRecords()->delete();

            foreach ($attendanceCorrectRequest->breakCorrectRequests as $breakCorrectRequest) {
                $attendanceRecord->breakRecords()->create([
                    'break_in'  => $breakCorrectRequest->requested_break_in,
                    'break_out' => $breakCorrectRequest->requested_break_out,
                ]);
            }

            $attendanceCorrectRequest->update([
                'request_status_id' => 2,
            ]);
        });

        return redirect()->route('adminCorrection.edit', [
            'attendance_correct_request_id' => $attendance_correct_request_id,
        ]);
    }
}
