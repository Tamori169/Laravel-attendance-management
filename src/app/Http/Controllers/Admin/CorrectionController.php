<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CorrectionController extends Controller
{
    /**
     * 申請一覧画面を表示。
     *
     * 一般ユーザーによる修正申請レコードを取得し一覧画面に表示。
     * リクエスト内のクエリに応じて、「承認待ち」もしくは「承認済み」のいずれかの
     * 情報を取得し表示する。
     *
     * @param Request $request 表示対象のステータスを指定するクエリ(タブ)を含むリクエストオブジェクト
     * @return View 申請一覧画面のビュー
     */
    public function index(Request $request): View
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

    /**
     * 修正申請承認画面を表示。
     *
     * 一般ユーザーによる修正申請レコードのうち、$attendance_correct_request_idで
     * 指定された修正申請レコード(休憩の修正申請レコードを含む)を取得し承認画面に表示。
     *
     * @param int $attendance_correct_request_id 承認対象の修正申請レコードのID
     * @return View 申請一覧画面のビュー
     */
    public function edit(int $attendance_correct_request_id): View
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::with([
            'attendanceRecord.user',
            'breakCorrectRequests'
        ])
            ->findOrFail($attendance_correct_request_id);

        return view('admin.corrections.edit', compact('attendanceCorrectRequest'));
    }

    /**
     * 修正申請の承認処理。
     *
     * Staff/CorrectionController@storeにて新規作成された修正申請レコードの更新。
     * ステータスを「承認済み」に変更する。
     * また、入力情報に基づき勤怠レコードを更新。休憩レコードは既存分を削除後、新規作成する。
     * 処理後は修正申請承認画面にリダイレクト。
     *
     * @param int $attendance_correct_request_id 承認対象の修正申請レコードのID
     * @return RedirectResponse 修正申請承認画面へのリダイレクト
     */
    public function update(int $attendance_correct_request_id): RedirectResponse
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
