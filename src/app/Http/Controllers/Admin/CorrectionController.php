<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectRequest;
use App\Models\User;
use Illuminate\Http\Request;

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
}
