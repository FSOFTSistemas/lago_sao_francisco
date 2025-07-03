<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $empresas = Empresa::all();
        $users = User::with('roles')->get();
        $permissions = Permission::all();
        return view('usuario.index', compact('users', 'empresas', 'permissions', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        $empresas = Empresa::all();
        $permissions = Permission::all();
        return view('usuario.create', compact('empresas', 'permissions', 'roles'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required|exists:roles,name',
                'empresa_id' => 'required|exists:empresas,id',
                'permissions' => 'array', 
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'empresa_id' => $request->empresa_id,
            ]);

            $user->assignRole($request->role);

            if (!empty($request->permissions)) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $user->givePermissionTo($permissions);
            }

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
                'role' => 'required|exists:roles,name',
                'permissions' => 'array',
                'empresa_id' => 'required|exists:empresas,id',
            ]);

            $user->update([
                'name' => $request->name,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
                'empresa_id' => $request->empresa_id,
            ]);

            $user->syncRoles([$request->role]);

            $permissions = Permission::whereIn('id', $request->permissions ?? [])->get();
            $user->syncPermissions($permissions);

            return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Erro ao atualizar usuário!');
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $empresas = Empresa::all();
        $permissions = Permission::all();
        return view('usuario.create', compact('user', 'empresas', 'permissions', 'roles'));
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