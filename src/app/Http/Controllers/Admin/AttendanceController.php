<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EditRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakRecord;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * 勤怠一覧画面を表示。
     *
     * 一般ユーザーの指定日の出勤情報を収集し画面に一覧表示。
     * なお、日付指定がない場合は当日の出勤情報を表示。
     *
     * @param Request $request 表示対象となる日付情報(date)を含むリクエストオブジェクト
     * @return View 勤怠一覧画面のビュー
     */
    public function index(Request $request): View
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

    /**
     * 勤怠詳細画面を表示。
     *
     * 特定のユーザーの指定日の勤怠情報を取得し詳細画面に表示。
     * なお、詳細画面では勤怠情報の修正が可能(Admin/AttendanceController@update)。
     * また、一般ユーザーによる修正申請中は修正後の勤怠情報が表示される。
     *
     * @param int $id 表示対象となる出勤レコードのID。
     * @return View 勤怠詳細画面のビュー
     */
    public function show(int $id): View
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

    /**
     * 勤怠情報の修正処理。
     *
     * 既にAdmin/AttendanceController@showの処理で表示された画面に修正情報を入力。
     * 入力情報をもとに勤怠レコードを更新する。
     * 修正後は、勤怠詳細画面にリダイレクト。
     *
     * @param EditRequest $request 修正情報(バリデーション済み)を含むリクエストオブジェクト
     * @param int $id 修正対象となる出勤レコードのID
     * @return RedirectResponse 勤怠詳細画面へのリダイレクト
     */
    public function update(EditRequest $request, int $id): RedirectResponse
    {
        DB::transaction(function () use ($request, $id) {
            $attendanceRecord = AttendanceRecord::findOrFail($id);

            $attendanceRecord->update([
                'clock_in' => $attendanceRecord->date->format('Y-m-d') . ' ' . $request->clock_in . ':00',
                'clock_out' => $attendanceRecord->date->format('Y-m-d') . ' ' . $request->clock_out . ':00',
                'comment' => $request->comment,
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
