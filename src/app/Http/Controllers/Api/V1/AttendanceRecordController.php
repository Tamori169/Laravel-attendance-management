<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttendanceRecordController extends Controller
{
    /**
     * 一覧データを取得してResourceに渡す。
     *
     * ユーザーID、日付、月、１ページあたり表示件数の検索条件に指定がある場合は
     * 絞り込み後のデータを返す。
     *
     * @param IndexAttendanceRecordRequest $request 検索条件(バリデーション済み)を含むリクエストオブジェクト
     * @return AnonymousResourceCollection 取得した勤怠データのコレクション
     */
    public function index(IndexAttendanceRecordRequest $request): AnonymousResourceCollection
    {
        $userId = $request->user_id;
        $date = $request->date;
        $month = $request->month;
        $perPage = $request->per_page ?? 20;

        $startOfMonth = null;
        $endOfMonth   = null;
        if ($month) {
            $designatedMonth  = Carbon::parse($month . '-01');
            $startOfMonth = $designatedMonth->copy()->startOfMonth()->format('Y-m-d');
            $endOfMonth   = $designatedMonth->copy()->endOfMonth()->format('Y-m-d');
        }

        $attendanceRecords = AttendanceRecord::with([
            'user',
            'breakRecords',
        ])
            ->when($userId, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($date, function ($query, $date) {
                $query->where('date', $date);
            })
            ->when($month, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
            })
            ->latest('date')
            ->paginate($perPage);


        return AttendanceRecordResource::collection($attendanceRecords);
    }

    /**
     * 勤怠レコードを新規作成する。
     *
     * @param  StoreAttendanceRecordRequest  $request 登録する勤怠情報(バリデーション済み)を含むリクエストオブジェクト
     * @return JsonResponse 整形済みの勤怠データ及び紐づくユーザーデータ、休憩データ、201ステータスコードを含むJSONレスポンス
     */
    public function store(StoreAttendanceRecordRequest $request): JsonResponse
    {
        $validated =[
            'date' => $request->date,
            'clock_in' => $request->date->format('Y-m-d'). ' ' . $request->clock_in,
            'clock_out' => $request->clock_out ? $request->date->format('Y-m-d') . ' ' . $request->clock_out : null,
            'comment' => $request->comment,
        ];

        $attendanceRecord = $request->user()->attendanceRecords()->create($validated);

        $attendanceRecord->load(['user', 'breakRecords']);

        return (new AttendanceRecordResource($attendanceRecord))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 特定の勤怠レコードを取得してResourceに渡す。
     *
     * @param  AttendanceRecord  $attendanceRecord ルートバインディングされた勤怠レコード
     * @return AttendanceRecordResource 整形済みの勤怠データ及び紐づく休憩データ、修正申請データ
     */
    public function show(AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        $attendanceRecord->load([
            'user',
            'breakRecords',
            'attendanceCorrectRequests.breakCorrectRequests',
            'attendanceCorrectRequests.requestStatus',
        ]);

        return new AttendanceRecordResource($attendanceRecord);
    }

    /**
     * 特定の勤怠レコードを更新する。
     *
     * @param  UpdateAttendanceRecordRequest  $request 更新する勤怠情報(バリデーション済み)を含むリクエストオブジェクト
     * @param  AttendanceRecord  $attendanceRecord ルートバインディングされた勤怠レコード
     * @return JsonResponse 整形済みの勤怠データ及び紐づくユーザーデータ、休憩データ、200ステータスコードを含むJSONレスポンス
     */
    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord): JsonResponse
    {
        $this->authorize('update', $attendanceRecord);

        $validated = [
            'date' => $request->date,
            'clock_in' => $request->date->format('Y-m-d') . ' ' . $request->clock_in,
            'clock_out' => $request->clock_out ? $request->date->format('Y-m-d') . ' ' . $request->clock_out : null,
            'comment' => $request->comment,
        ];

        $attendanceRecord->update($validated);

        $attendanceRecord->load(['user', 'breakRecords']);

        return (new AttendanceRecordResource($attendanceRecord))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * 特定の勤怠レコードを削除する。
     *
     * @param  AttendanceRecord  $attendanceRecord ルートバインディングされた勤怠レコード
     * @return Response 204ステータスコードを含むレスポンス
     */
    public function destroy(AttendanceRecord $attendanceRecord): Response
    {
        $this->authorize('delete', $attendanceRecord);

        $attendanceRecord->delete();

        return response()->noContent();
    }
}
