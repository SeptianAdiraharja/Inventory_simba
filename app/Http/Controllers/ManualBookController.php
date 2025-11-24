<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class ManualBookController extends Controller
{
    public function index()
    {
        return view('role.super_admin.manual_book.index');
    }
}
