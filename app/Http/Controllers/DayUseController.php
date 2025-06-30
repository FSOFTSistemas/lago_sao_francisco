<?php

namespace App\Http\Controllers;

use App\Models\DayUse;
use App\Http\Controllers\Controller;
use App\Models\Caixa;
use App\Models\FluxoCaixa;
use App\Models\Funcionario;
use App\Models\LogDayuse;
use App\Models\Movimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        try {
            $dayuse = DayUse::findOrFail($id);
            $dayuse->delete();
            return redirect()->route('dayuse.index')->with('success', 'Day Use deletado com sucesso!');
        } catch (\Exception $e) {
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

                $dayUse = DayUse::with('formaPag')->findOrFail($request->dayuse_id);

                // Log da exclusão
                LogDayuse::create([
                    'usuario' => Auth::user()->name,
                    'supervisor' => $func->nome,
                    'acao' => 'Exclusão de DayUse',
                    'data_hora' => now(),
                    'observacao' => 'DayUse #' . $dayUse->id . ' excluído.',
                ]);

                // Buscar caixa aberto da empresa
                $caixa = Caixa::where('empresa_id', Auth::user()->empresa_id)
                    ->where('status', 'aberto')
                    ->latest()
                    ->first();

                if ($caixa) {
                    foreach ($dayUse->formaPag as $pagamento) {
                        $descricao = strtolower($pagamento->formaPagamento->descricao); // Ex: "Cartão Crédito"
                        $slug = str_replace([' ', '_'], '-', $descricao); // "cartão-crédito"
                        $movimento = Movimento::where('descricao', 'cancelamento-' . $slug)->first();

                        if ($movimento) {
                            FluxoCaixa::create([
                                'descricao' => 'Cancelamento DayUse #' . $dayUse->id,
                                'valor' => -$pagamento->valor,
                                'valor_total' => -$pagamento->valor,
                                'tipo' => 'cancelamento',
                                'caixa_id' => $caixa->id,
                                'usuario_id' => Auth::id(),
                                'empresa_id' => Auth::user()->empresa_id,
                                'data' => now(),
                                'movimento_id' => $movimento->id,
                                'plano_de_conta_id' => 1,
                            ]);
                        }
                    }
                }

                // Exclui o DayUse
                $dayUse->delete();

                return response()->json(['message' => 'DayUse excluído com sucesso!']);
            }
        }

        return response()->json(['message' => 'Senha de supervisor inválida.'], 403);
    }
}
