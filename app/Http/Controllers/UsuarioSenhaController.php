<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuarioSenhaController extends Controller
{
    public function form()
    {
        $usuario = Auth::user();
        return view('usuario.alterar-senha', compact('usuario'));
    }

    public function atualizar(Request $request)
    {
        $request->validate([
            'senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $usuario = Auth::user();
        $usuario->password = Hash::make($request->senha);
        $usuario->save();

        return redirect()->route('usuario.senha.form')->with('success', 'Senha atualizada com sucesso!');
    }
}
