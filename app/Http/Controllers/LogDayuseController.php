<?php

namespace App\Http\Controllers;

use App\Models\LogDayuse;
use Illuminate\Http\Request;

class LogDayuseController extends Controller
{
    public function index(Request $request)
    {
        $logs = LogDayuse::orderBy('data_hora', 'desc')->paginate(20);

        return view('logs.dayuse.index', compact('logs'));
    }
}