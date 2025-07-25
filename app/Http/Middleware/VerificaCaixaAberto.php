<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Caixa;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VerificaCaixaAberto
{
    public function handle(Request $request, Closure $next)
    {
        $empresaId = Auth::user()->empresa_id ?? null;
        $usuarioId = Auth::id();


        $caixaAberto = Caixa::where('empresa_id', $empresaId)
        ->where('usuario_id', $usuarioId)
            ->where('status', 'aberto')
            ->latest('data_abertura')
            ->first();

        if (!$caixaAberto) {
            return redirect()->route('fluxoCaixa.index')
                ->with('sweet_error', 'Você precisa abrir o caixa do dia para continuar.');
        }

        if (!Carbon::parse($caixaAberto->data_abertura)->isToday()) {
            return redirect()->route('fluxoCaixa.index')->with('sweet_error', 'O caixa aberto não é do dia atual. Feche-o para continuar.');
        }

        return $next($request);
    }
}
