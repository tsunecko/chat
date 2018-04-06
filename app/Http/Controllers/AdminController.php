<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Http\Controllers\HomeController;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function admin()
    {
        return view('admin');
    }
}
