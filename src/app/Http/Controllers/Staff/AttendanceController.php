<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakRecord;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    /**
     * 勤怠登録画面を表示。
     *
     * @return View 勤怠登録画面のビュー
     */
    public function create(): View
    {
        return view('staff.attendances.create');
    }

    /**
     * 出勤処理を実行。
     *
     * ログインユーザーの勤務ステータスが「勤務外」の時、新たに出勤レコードを新規作成。
     * 退勤時間(clock_out)は空で保存され、退勤時に更新される。
     * 作成後は勤怠登録画面にリダイレクト。
     *
     * @return RedirectResponse 勤怠登録画面へのリダイレクト
     */
    public function clockIn(): RedirectResponse
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

    /**
     * 休憩開始処理を実行。
     *
     * ログインユーザーの当日の出勤レコードに紐づいた休憩レコードを新規作成。
     * 休憩終了時間(break_out)は空で保存され、休憩終了時に更新される。
     * 作成後は勤怠登録画面にリダイレクト。
     *
     * @return RedirectResponse 勤怠登録画面へのリダイレクト
     */
    public function breakIn(): RedirectResponse
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

    /**
     * 休憩終了処理を実行。
     *
     * 既にStaff/AttendanceController@breakInで作成済みの休憩レコードに対して
     * 休憩終了時間(break_out)を追加で保存。
     * 作成後は勤怠登録画面にリダイレクト。
     *
     * @return RedirectResponse 勤怠登録画面へのリダイレクト
     */
    public function breakOut(): RedirectResponse
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

    /**
     * 退勤処理を実行。
     *
     * 既にStaff/AttendanceController@clockInで作成済みの出勤レコードに対して
     * 退勤時間(clock_out)を追加で保存。
     * 作成後は勤怠登録画面にリダイレクト。
     *
     * @return RedirectResponse 勤怠登録画面へのリダイレクト
     */
    public function clockOut(): RedirectResponse
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

    /**
     * 出勤情報一覧画面を表示。
     *
     * ログインユーザーの指定月の出勤情報を収集し、日毎にマッピング。
     * 出勤情報の月次一覧画面を表示する。
     * なお、指定がない場合は当月の出勤情報を表示。
     *
     * @param Request $request 表示対象となる年月情報(指定がない場合は当月)を含むリクエストオブジェクト
     * @return View 出勤一覧画面のビュー
     */
    public function index(Request $request): View
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

    /**
     * 勤怠詳細画面を表示。
     *
     * ログインユーザーの指定日の勤怠情報(休憩情報含む)を取得し詳細画面に表示。
     * なお、詳細画面では勤怠情報の修正申請が可能(Staff/CorrectionController@store)。
     * また、修正申請中は修正後の勤怠情報が表示される。
     *
     * @param int $id 表示対象となる出勤レコードのid。
     * @return View 勤怠詳細画面のビュー
     */
    public function show(int $id): View
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