<?php

namespace App\Http\Controllers;

use App\Models\User;

class StaffListController extends Controller
{
    //
    public function index()
    {
        $users = User::all();

        return view('admin.staff-list', compact('users'));
    }
}
