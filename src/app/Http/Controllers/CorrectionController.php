<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\CorrectionController as AdminCorrectionController;
use App\Http\Controllers\Staff\CorrectionController as StaffCorrectionController;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class CorrectionController extends Controller
{
    /**
     * ユーザーの権限に応じた申請一覧画面表示処理への振り分け。
     *
     * ログインユーザーのロールが「admin」の場合は管理者用、
     * それ以外の場合はスタッフ用のコントローラ処理を呼び出して処理を実行。
     *
     * @param Request $request 表示対象のステータスを指定するクエリ(タブ)を含むリクエストオブジェクト
     * @return View 各権限に応じた修正申請一覧画面のビュー
     */
    public function index(Request $request)
    {
        if ($request->user()?->role?->name === 'admin') {
            return app(AdminCorrectionController::class)->index($request);
        }

        return app(StaffCorrectionController::class)->index($request);
    }
}
