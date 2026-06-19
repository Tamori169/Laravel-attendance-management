<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function report()
    {
        return view('staff.attendances.report');
    }
}
