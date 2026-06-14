<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\CorrectionController as AdminCorrectionController;
use App\Http\Controllers\Staff\CorrectionController as StaffCorrectionController;
use Illuminate\Http\Request;

class CorrectionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()?->role?->name === 'admin') {
            return app(AdminCorrectionController::class)->index($request);
        }

        return app(StaffCorrectionController::class)->index($request);
    }
}
