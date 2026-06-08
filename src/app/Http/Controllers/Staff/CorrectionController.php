<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CorrectionController extends Controller
{
    public function store(Request $request){
        return view('staff.attendances.show');
    }
}
