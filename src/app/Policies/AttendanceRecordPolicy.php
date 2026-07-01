<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AttendanceRecordPolicy
{
    use HandlesAuthorization;

    /**
     * 管理者に対する許可の事前実施。
     *
     * @param User $user ログイン中のユーザーレコード
     * @param string $ability 実行しようとしている操作名
     * @return bool
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role_id === 2) {
            return true;
        }

        return null;
    }

    /**
     * 更新の認可判定処理。
     *
     * @param User $user ログイン中のユーザーレコード
     * @param AttendanceRecord $attendanceRecord 勤怠レコード
     * @return Response 判定結果のレスポンス
     */
    public function update(User $user, AttendanceRecord $attendanceRecord): Response
    {
        return $user->id === $attendanceRecord->user_id
            ? Response::allow()
            : Response::deny();
    }

    /**
     * 削除の認可判定処理。
     *
     * @param User $user ログイン中のユーザーレコード
     * @param AttendanceRecord $attendanceRecord 勤怠レコード
     * @return Response 判定結果のレスポンス
     */
    public function delete(User $user, AttendanceRecord $attendanceRecord): Response
    {
        return $user->id === $attendanceRecord->user_id
            ? Response::allow()
            : Response::deny();
    }
}
