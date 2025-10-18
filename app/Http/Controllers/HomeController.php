<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminHome()
    {
        return view('admin.home',["msg"=>"Hello! I am admin"]);
    }
    public function guruHome() 
    {
        return view('guru.home',["msg"=>"Hello! I am guru"]); 
    }
    public function siswaHome()
    {
        return view('siswa.home',["msg"=>"Hello! I am siswa"]);
    }
}
