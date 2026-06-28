<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffController extends Controller
{
    /**
     * スタッフ一覧画面を表示。
     *
     * 全一般ユーザーの名前およびメールアドレスを取得し一覧画面に表示。
     *
     * @return View スタッフ一覧画面のビュー
     */
    public function index(): View
    {
        $users = User::where('role_id',1)
        ->get();

        return view('admin.staff.index',compact('users'));
    }

    /**
     * スタッフ別勤怠一覧画面を表示。
     *
     * 全一般ユーザーの名前およびメールアドレスを取得し一覧画面に表示。
     *
     * @param Request $request 表示対象となる年月情報(クエリパラメータ)を含むリクエストオブジェクト
     * @param int $id 表示対象となる一般ユーザーのID
     * @return View スタッフ別勤怠一覧画面のビュー
     */
    public function show(Request $request, int $id): View
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


    /**
     * 勤怠実績のCSVファイル出力処理
     *
     * idにて指定された一般ユーザーの月次勤怠レコードを取得し、その月の全日数の勤怠データをCSV形式に変換。
     * ブラウザに対しストリームダウンロードを実行する。
     * なお、年月指定がない場合は当月の勤怠実績が対象。
     *
     * @param Request $request 対象月（month）のクエリパラメータを含むリクエストオブジェクト
     * @param int $id 出力対象となるユーザーのID
     * @return StreamedResponse CSVダウンロード用のストリームレスポンス
     */
    public function export(Request $request, int $id): StreamedResponse
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
