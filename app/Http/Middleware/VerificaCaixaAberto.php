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
        $user = Auth::user();

        // 1) Se for Master, deixa passar e ignora as regras de caixa
        if ($user && $user->hasRole('Master')) {
            return $next($request);
        }

        // 2) Regras para não-Master
        $empresaId = $user->empresa_id ?? null;
        $usuarioId = $user->id ?? null;

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
            return redirect()->route('fluxoCaixa.index')
                ->with('sweet_error', 'O caixa aberto não é do dia atual. Feche-o para continuar.');
        }

        return $next($request);
    }
}
