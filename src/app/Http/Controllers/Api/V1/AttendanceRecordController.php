<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexRequest;
use App\Http\Requests\Api\V1\StoreRequest;
use App\Http\Requests\Api\V1\UpdateRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttendanceRecordController extends Controller
{
    /**
     * 一覧データを取得してResourceに渡す処理を行う。
     *
     * ユーザーID、日付、月、１ページあたり表示件数の検索条件に指定がある場合は
     * 絞り込み後のデータを返す。
     *
     * @param IndexRequest $request 検索条件(バリデーション済み)を含むリクエストオブジェクト
     * @return AnonymousResourceCollection 取得した勤怠データのコレクション
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
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
     * @param  StoreRequest  $request 登録する勤怠情報(バリデーション済み)を含むリクエストオブジェクト
     * @return JsonResponse 201ステータスコードを含むJSONレスポンス
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated =[
            'date' => $request->date,
            'clock_in' => $request->date->format('Y-m-d'). ''. $request->clock_in,
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
     * 特定の勤怠情報を取得してResourceに渡す処置を行う。
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
