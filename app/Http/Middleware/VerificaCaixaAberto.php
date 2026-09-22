<?php

namespace App\Http\Middleware;

use App\Models\Caixa;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (! $caixaAberto) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Você precisa abrir o caixa do dia para continuar.'], 422);
            }

            return redirect()->route('fluxoCaixa.index')
                ->with('sweet_error', 'Você precisa abrir o caixa do dia para continuar.');
        }

        if (! Carbon::parse($caixaAberto->data_abertura)->isToday()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'O caixa aberto não é do dia atual. Feche-o para continuar.'], 422);
            }

            return redirect()->route('fluxoCaixa.index')
                ->with('sweet_error', 'O caixa aberto não é do dia atual. Feche-o para continuar.');
        }

        return $next($request);
    }
}
