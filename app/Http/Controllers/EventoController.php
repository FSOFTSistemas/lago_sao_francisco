<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    private const CORES_STATUS = [
        'pendente' => '#ffc107',
        'pago' => '#28a745',
        'cancelado' => '#dc3545',
    ];

    public function home()
    {
        $proximosEventos = Aluguel::with(['cliente', 'espaco'])
            ->where('status', '!=', 'cancelado')
            ->whereDate('data_fim', '>=', Carbon::today())
            ->orderBy('data_inicio')
            ->take(8)
            ->get();

        return view('eventos.home', compact('proximosEventos'));
    }

    public function planner()
    {
        return view('eventos.planner');
    }

    public function plannerEventos(Request $request)
    {
        $inicio = $request->filled('start')
            ? Carbon::parse($request->input('start'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $request->filled('end')
            ? Carbon::parse($request->input('end'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $eventos = Aluguel::with(['cliente', 'espaco'])
            ->where('data_inicio', '<=', $fim)
            ->where('data_fim', '>=', $inicio)
            ->get();

        return response()->json($eventos->map(function ($aluguel) {
            return [
                'id' => $aluguel->id,
                'title' => ($aluguel->espaco->nome ?? 'Espaço').' - '.ucfirst(str_replace('_', ' ', $aluguel->tipo ?? 'evento')),
                'start' => Carbon::parse($aluguel->data_inicio)->format('Y-m-d'),
                'end' => Carbon::parse($aluguel->data_fim)->addDay()->format('Y-m-d'),
                'color' => self::CORES_STATUS[$aluguel->status] ?? '#6c757d',
                'extendedProps' => [
                    'aluguel_id' => $aluguel->id,
                    'espaco' => $aluguel->espaco->nome ?? '-',
                    'tipo' => ucfirst(str_replace('_', ' ', $aluguel->tipo ?? '-')),
                    'cliente' => $aluguel->cliente->nome_razao_social ?? '-',
                    'status' => $aluguel->status,
                    'total_formatado' => 'R$ '.number_format($aluguel->total ?? 0, 2, ',', '.'),
                    'data_inicio' => Carbon::parse($aluguel->data_inicio)->format('d/m/Y'),
                    'data_fim' => Carbon::parse($aluguel->data_fim)->format('d/m/Y'),
                ],
            ];
        }));
    }
}
