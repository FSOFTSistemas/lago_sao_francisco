<?php

namespace App\Http\Controllers;

use App\Models\DayUse;
use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\LogDayuse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DayUseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dayuses = DayUse::all();
        return view('dayuse.index', compact('dayuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('dayuse._form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
public function show($id)
{
    return view('dayuse.show', [
        'id' => $id,    
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DayUse $dayUse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DayUse $dayUse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $dayuse = DayUse::findOrFail($id);
            $dayuse->delete();
            return redirect()->route('dayuse.index')->with('success', 'Day Use deletado com sucesso!');
        } catch(\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao deletar Day Use!', $e);
        }
    }

   public function verificaSupervisor(Request $request)
{
    $request->validate([
        'dayuse_id' => 'required|exists:day_uses,id',
        'senha' => 'required|string',
    ]);

    $funcionarios = Funcionario::whereNotNull('senha_supervisor')->get();
    foreach ($funcionarios as $func) {
        if (Hash::check($request->senha, $func->senha_supervisor)) {
            $dayUse = DayUse::findOrFail($request->dayuse_id);

            LogDayuse::create([
                'usuario' => Auth::user()->name,
                'supervisor' => $func->nome,
                'acao' => 'Exclusão de DayUse',
                'data_hora' => now(),
                'observacao' => 'DayUse #' . $dayUse->id . ' excluído.',
            ]);

            $dayUse->delete();

            return response()->json(['message' => 'DayUse excluído com sucesso!']);
        }
    }

    return response()->json(['message' => 'Senha de supervisor inválida.'], 403);
}

}
