<?php

namespace App\Http\Controllers;

use App\Models\LogReserva;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogReservaController extends Controller
{
    /**
     * Retorna os logs de uma reserva específica
     */
    public function getLogsPorReserva($reservaId)
    {
        $logs = LogReserva::where('reserva_id', $reservaId)
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }
    
    /**
     * Exibe os logs de uma reserva na view
     */
    public function showLogs($reservaId)
    {
        $reserva = Reserva::findOrFail($reservaId);
        $logs = LogReserva::where('reserva_id', $reservaId)
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('reservas.logs', compact('reserva', 'logs'));
    }
    
    /**
     * Registra um log manualmente (para uso em testes ou via API)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'tipo' => 'required|in:criacao,edicao,exclusao,produto_adicionado,produto_removido,pagamento_adicionado,pagamento_removido,status_alterado',
            'descricao' => 'required|string',
            'dados_antigos' => 'nullable|array',
            'dados_novos' => 'nullable|array',
        ]);
        
        $validated['usuario_id'] = Auth::id();
        
        $log = LogReserva::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Log registrado com sucesso',
            'log' => $log
        ]);
    }
}