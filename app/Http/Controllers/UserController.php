<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;


class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('role:Master');
    // }

    public function index()
    {
        $empresas = Empresa::all();
        $users = User::all();
        return view('usuario.index', compact('users', 'empresas'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required|exists:roles,name',
                'empresa_id' => 'required|exists:empresas,id'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'empresa_id' =>$request->empresa_id
            ]);

            $user->assignRole($request->role);

            return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Erro ao cadastrar usuário!');
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $request->validate([
                'name' => 'required|string|max:255',
                'role' => 'required|exists:roles,name'
            ]);

            $user->update([
                'name' => $request->name,
                'password' => Hash::make($request->password),
            ]);
            $user->syncRoles([$request->role]);

            return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Erro ao atualizar usuário!');
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('usuarios.index')->with('success', 'Usuário excluído com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Erro ao excluir usuário!');
        }
    }
}
