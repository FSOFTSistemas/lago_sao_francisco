<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Master')) {
            return view('home.master');
        }

        if ($user->hasRole('financeiro')) {
            return view('home.financeiro');
        }

        if ($user->hasRole('funcionario')) {
            return view('home.funcionario');
        }

        // Role não reconhecida
        abort(403, 'Acesso não autorizado.');
    }
}
