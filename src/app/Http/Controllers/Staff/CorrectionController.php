<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakCorrectRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CorrectionController extends Controller
{
    /**
     * 勤怠情報の修正申請処理。
     *
     * 指定された勤怠情報に紐づく修正申請レコードを新規作成。
     * 作成時は「承認待ち」ステータスで作成され、管理者ユーザーによる承認処理で「承認済み」に更新される。
     * 作成後は勤怠詳細画面にリダイレクトする。
     *
     * @param CorrectionRequest $request バリデーション済みのリクエストオブジェクト
     * @param int $id 対象となる出勤レコードのid
     * @return RedirectResponse 勤怠詳細画面へのリダイレクト
     */
    public function store(CorrectionRequest $request, int $id): RedirectResponse
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

        return redirect()->route('staffAttendance.show', ['id' => $id]);
    }

    /**
     * 申請一覧画面を表示。
     *
     * ログインユーザーの修正申請レコードを取得し一覧画面に表示。
     * リクエスト内のクエリに応じて、「承認待ち」もしくは「承認済み」のいずれかの
     * 情報を取得し表示する。
     *
     * @param Request $request 表示対象のステータスを指定するクエリ(タブ)を含むリクエストオブジェクト
     * @return View 申請一覧画面のビュー
     */
    public function index(Request $request): View
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

    /**
     * 勤怠詳細画面(修正申請承認済み)を表示。
     *
     * 指定された修正申請レコードを取得し勤怠詳細画面に表示。
     * なお、本画面は申請一覧(承認済み)の「詳細」から遷移した場合のみに表示される。
     *
     * @param int $id 表示対象となる修正申請レコードのid
     * @return View 勤怠詳細画面(修正申請承認済み)のビュー
     */
    public function show(int $id): View
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::with([
            'attendanceRecord.user',
            'breakCorrectRequests',
        ])
        ->findOrFail($id);

        return view('staff.corrections.show', compact('attendanceCorrectRequest'));
    }
}
