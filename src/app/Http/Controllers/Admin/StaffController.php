<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::where('role_id',1)
        ->get();

        return view('admin.staff.index',compact('users'));
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->query('month', now('Asia/Tokyo')->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breakRecords')
            ->where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($attendanceRecord) => $attendanceRecord->date->format('Y-m-d'));

        $attendanceRecordList = collect(CarbonPeriod::create($startOfMonth, $endOfMonth))
            ->map(fn($date) => [
                'date' => $date,
                'attendance_record' => $attendanceRecords->get($date->format('Y-m-d'))
            ]);

        return view('admin.staff.show', compact('user', 'currentMonth', 'attendanceRecordList'));
    }

    public function export(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->query(
            'month',
            now('Asia/Tokyo')->format('Y-m')
        );

        $currentMonth = Carbon::parse($month);

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breakRecords')
            ->where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($record) => $record->date->format('Y-m-d'));

        $attendanceRecordList = collect(
            CarbonPeriod::create($startOfMonth, $endOfMonth)
        )->map(fn($date) => [
            'date' => $date,
            'attendance_record' => $attendanceRecords->get(
                $date->format('Y-m-d')
            ),
        ]);

        $callback = function () use ($attendanceRecordList) {
            $file = fopen('php://output', 'w');

            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, ['日付','出勤','退勤','休憩','合計']);

            foreach ($attendanceRecordList as $item) {

                fputcsv($file, [
                    $item['date']->format('m/d'),
                    $item['attendance_record']?->clock_in?->format('H:i'),
                    $item['attendance_record']?->clock_out?->format('H:i'),
                    $item['attendance_record']?->formatted_break_time,
                    $item['attendance_record']?->formatted_work_time,
                ]);
            }

            fclose($file);
        };

        $filename = $currentMonth->format('Y-m').'_'.$user->name. '.csv';

        return response()->streamDownload(
            $callback,
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}
