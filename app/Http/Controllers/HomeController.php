<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $username = Auth::user()->name;

        DB::table('users')
            ->where('name',$username)
            ->update(['islogin' => 'true']);
        $onlineUsers = DB::table('users')
            ->select('name')
            ->where('islogin','true')
            ->get();

        return view('home', compact('username','onlineUsers'));
    }
}
