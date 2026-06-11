<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::where('role_id',1)
        ->get();

        return view('admin.staff.index',compact('users'));
    }

    public function show($id)
    {
        return view('admin.staff.show');
    }
}
