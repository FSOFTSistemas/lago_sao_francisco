<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use App\Models\Espaco;
use App\Models\Excursao;
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
            ->where('tipo', '!=', 'bloqueio')
            ->where('status', '!=', 'cancelado')
            ->whereDate('data_fim', '>=', Carbon::today())
            ->orderBy('data_inicio')
            ->take(8)
            ->get();

        return view('eventos.home', compact('proximosEventos'));
    }

    public function planner()
    {
        $espacos = Espaco::orderBy('nome')->get();

        return view('eventos.planner', compact('espacos'));
    }

    public function plannerEventos(Request $request)
    {
        $inicio = $request->filled('start')
            ? Carbon::parse($request->input('start'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $request->filled('end')
            ? Carbon::parse($request->input('end'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $alugueis = Aluguel::with(['cliente', 'espaco'])
            ->where('data_inicio', '<=', $fim)
            ->where('data_fim', '>=', $inicio)
            ->where('status', '!=', 'cancelado')
            ->get();

        $eventos = $alugueis->map(function ($aluguel) {
            $isBloqueio = ($aluguel->tipo === 'bloqueio');

            return [
                'id' => 'aluguel-'.$aluguel->id,
                'title' => $isBloqueio
                    ? ($aluguel->espaco->nome ?? 'Espaço').' - [BLOQUEADO]'
                    : ($aluguel->espaco->nome ?? 'Espaço').' - '.ucfirst(str_replace('_', ' ', $aluguel->tipo ?? 'evento')),
                'start' => Carbon::parse($aluguel->data_inicio)->format('Y-m-d'),
                'end' => Carbon::parse($aluguel->data_fim)->addDay()->format('Y-m-d'),
                'color' => $isBloqueio ? '#343A40' : (self::CORES_STATUS[$aluguel->status] ?? '#6c757d'),
                'extendedProps' => [
                    'categoria' => 'aluguel',
                    'aluguel_id' => $aluguel->id,
                    'is_bloqueio' => $isBloqueio,
                    'espaco' => $aluguel->espaco->nome ?? '-',
                    'tipo' => $isBloqueio ? 'Bloqueio de Data' : ucfirst(str_replace('_', ' ', $aluguel->tipo ?? '-')),
                    'cliente' => $isBloqueio ? 'Data Bloqueada' : ($aluguel->cliente->nome_razao_social ?? '-'),
                    'status' => $isBloqueio ? 'Bloqueado' : $aluguel->status,
                    'total_formatado' => $isBloqueio ? 'R$ 0,00' : ('R$ '.number_format($aluguel->total ?? 0, 2, ',', '.')),
                    'observacoes' => $aluguel->observacoes ?? '',
                    'data_inicio' => Carbon::parse($aluguel->data_inicio)->format('d/m/Y'),
                    'data_fim' => Carbon::parse($aluguel->data_fim)->format('d/m/Y'),
                ],
            ];
        });

        $excursoes = Excursao::query()
            ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])
            ->where('status', '!=', Excursao::STATUS_CANCELADO)
            ->orderBy('data')
            ->get()
            ->map(function (Excursao $excursao) {
                return [
                    'id' => 'excursao-'.$excursao->id,
                    'title' => 'Excursão - '.$excursao->qtd_pessoas.' pessoas',
                    'start' => $excursao->data->format('Y-m-d'),
                    'color' => '#6f42c1',
                    'extendedProps' => [
                        'categoria' => 'excursao',
                        'tipo' => 'Excursão',
                        'qtd_pessoas' => $excursao->qtd_pessoas,
                        'status' => ucfirst(strtolower(str_replace('_', ' ', $excursao->status))),
                        'responsavel' => $excursao->responsavel,
                        'telefone_responsavel' => $excursao->telefone_responsavel,
                        'descricao' => $excursao->descricao,
                        'total_formatado' => 'R$ '.number_format($excursao->total, 2, ',', '.'),
                        'data_inicio' => $excursao->data->format('d/m/Y'),
                        'data_fim' => $excursao->data->format('d/m/Y'),
                    ],
                ];
            });

        return response()->json($eventos->concat($excursoes)->values());
    }
}
