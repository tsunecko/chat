<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Classes\Message;
use App\Events\NewMessageAdded;

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
        //$messages = Message::all();
        $username = Auth::user()->name;
        $token = Auth::user()->token;

        $users = DB::table('users')
            ->select('name')
            ->offset(1)
            ->limit(100000)
            ->get();
        $type = Auth::user()->type;

        return view('home', compact('username','token', 'users', 'type'));
    }

    public function postMessage(Request $request)
    {
        $message = Message::create($request->all());
        event(
            new NewMessageAdded($message)
        );
        return redirect()->back();
    }
}
