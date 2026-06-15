<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakCorrectRequest;
use Illuminate\Support\Facades\DB;

class CorrectionController extends Controller
{
    public function create($id)
    {
        $user = auth()->user();

        $attendanceRecord = AttendanceRecord::where('id', $id)
            ->firstOrFail();

        $breakRecords = BreakRecord::where('attendance_record_id', $id)->get();

        $attendanceCorrectRequest = AttendanceCorrectRequest::with('breakCorrectRequests')
            ->where('attendance_record_id', $id)
            ->where('request_status_id', 1)
            ->first();

        return view('staff.corrections.create', compact('user', 'attendanceRecord', 'breakRecords', 'attendanceCorrectRequest'));
    }

    public function store(CorrectionRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $attendanceRecord = AttendanceRecord::findOrFail($id);

            $attendanceCorrectRequest = AttendanceCorrectRequest::create([
                'attendance_record_id' => $id,
                'request_status_id' => 1,
                'requested_clock_in' => $attendanceRecord->date->format('Y-m-d') . ' ' . $request->requested_clock_in . ':00',
                'requested_clock_out' => $attendanceRecord->date->format('Y-m-d') . ' ' . $request->requested_clock_out . ':00',
                'comment' => $request->comment,
            ]);

            foreach ($request->input('requested_breaks', []) as $requestedBreak) {
                $breakIn = $requestedBreak['break_in'] ?? null;
                $breakOut = $requestedBreak['break_out'] ?? null;

                if (empty($breakIn) && empty($breakOut)) {
                    continue;
                }

                BreakCorrectRequest::create([
                    'attendance_correct_request_id' => $attendanceCorrectRequest->id,
                    'requested_break_in' => $attendanceRecord->date->format('Y-m-d') . ' ' . $breakIn . ':00',
                    'requested_break_out' => $attendanceRecord->date->format('Y-m-d') . ' ' . $breakOut . ':00',
                ]);
            }
        });

        return redirect()->route('staffCorrection.create', ['id' => $id]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab');

        if ($tab === 'approved') {
            $statusId = 2;
        } else {
            $statusId = 1;
        }

        $attendanceCorrectRequests = AttendanceCorrectRequest::with([
            'attendanceRecord',
            'requestStatus',
            ])
            ->whereHas('attendanceRecord', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('request_status_id', $statusId)
            ->latest()
            ->get();

        return view('staff.corrections.index', compact(
            'user',
            'attendanceCorrectRequests'
        ));
    }
}
