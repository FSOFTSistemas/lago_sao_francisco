<?php

namespace App\Http\Controllers;

use App\Models\Quarto;
use App\Models\Categoria;
use App\Models\Reserva;
use App\Models\Tarifa;
use App\Models\Hospede;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MapaController extends Controller
{
    public function index(Request $request)
    {
        $dataInicio = $request->get('data_inicio', Carbon::now()->subDays(5)->format('Y-m-d'));
        $dataFim = $request->get('data_fim', Carbon::now()->addDays(15)->format('Y-m-d'));

        // Busca os hóspedes ordenados por nome (Melhora a UX do select)
        $hospedes = Hospede::orderBy('nome')->get();

        return view('mapa.index_react', compact('dataInicio', 'dataFim', 'hospedes'));
    }

    public function getDadosMapa(Request $request)
    {
        try {
            $dataInicio = Carbon::parse($request->get('data_inicio', Carbon::now()->startOfWeek()));
            $dataFim = Carbon::parse($request->get('data_fim', $dataInicio->copy()->addDays(13)));

            // Gerar array de datas
            $datas = [];
            $dataAtual = $dataInicio->copy();
            while ($dataAtual <= $dataFim) {
                $datas[] = $dataAtual->format('Y-m-d');
                $dataAtual->addDay();
            }

            // --- ALTERAÇÃO PRINCIPAL ---
            // Busca quartos diretamente, ordenados pela posição globalmente, ignorando agrupamento visual de categoria
            $quartos = Quarto::where('status', 1)
                ->orderBy('posicao', 'asc') // Ordenação Global
                ->with(['categoria', 'reservas' => function ($query) use ($dataInicio, $dataFim) {
                    $query->with('hospede')
                        ->where(function ($q) use ($dataInicio, $dataFim) {
                            $q->where('data_checkin', '<=', $dataFim)
                              ->where('data_checkout', '>', $dataInicio);
                        })
                        ->whereNotIn('situacao', ['cancelado']);
                }])
                ->get();

            // Organizar dados do mapa (Lista plana de quartos)
            $dadosQuartos = [];

            foreach ($quartos as $quarto) {
                $reservasFormatadas = [];
                foreach ($quarto->reservas as $reserva) {
                    $reservasFormatadas[] = [
                        'id' => $reserva->id,
                        'hospede_nome' => $reserva->hospede ? $reserva->hospede->nome : 'Sem hóspede',
                        'data_checkin' => $reserva->data_checkin,
                        'data_checkout' => $reserva->data_checkout,
                        'situacao' => $reserva->situacao,
                        'valor_diaria' => $reserva->valor_diaria,
                        'n_adultos' => $reserva->n_adultos,
                        'n_criancas' => $reserva->n_criancas,
                    ];
                }

                $dadosQuartos[] = [
                    'id' => $quarto->id,
                    'nome' => $quarto->nome,
                    'posicao' => $quarto->posicao,
                    'categoria_nome' => $quarto->categoria->titulo ?? '',
                    'reservas' => $reservasFormatadas
                ];
            }

            // Calcular ocupação por data
            $ocupacaoPorData = $this->calcularOcupacaoPorData($datas, $quartos);

            return response()->json([
                'success' => true,
                'datas' => $datas,
                'quartos' => $dadosQuartos, // Agora retornamos 'quartos' direto, não 'categorias'
                'ocupacao' => $ocupacaoPorData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados do mapa: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calcularOcupacaoPorData($datas, $quartos)
    {
        $ocupacao = [];
        $totalQuartos = $quartos->count();

        foreach ($datas as $data) {
            $quartosOcupados = 0;
            
            // Loop otimizado
            foreach ($quartos as $quarto) {
                $temReserva = $quarto->reservas->contains(function ($reserva) use ($data) {
                    return $reserva->data_checkin <= $data && $reserva->data_checkout > $data;
                });
                
                if ($temReserva) {
                    $quartosOcupados++;
                }
            }

            $percentual = $totalQuartos > 0 ? round(($quartosOcupados / $totalQuartos) * 100) : 0;

            $ocupacao[$data] = [
                'ocupados' => $quartosOcupados,
                'total' => $totalQuartos,
                'percentual' => $percentual
            ];
        }

        return $ocupacao;
    }

    public function criarReservaRapida(Request $request)
    {
        try {
            Log::debug('Dados recebidos:', $request->all());
            $request->validate([
                'quarto_id' => 'required|exists:quartos,id',
                'data_checkin' => 'required|date',
                'data_checkout' => 'required|date|after:data_checkin',
                'tipo' => 'required|in:reserva,bloqueio',
                'valor_diaria' => 'nullable|numeric',
            ]);

            $dadosReserva = [
                'quarto_id' => $request->quarto_id,
                'data_checkin' => $request->data_checkin,
                'data_checkout' => $request->data_checkout,
                'valor_diaria' => $request->valor_diaria ?? 0,
                'n_adultos' => 1,
                'n_criancas' => 0,
            ];

            if ($request->tipo === 'bloqueio') {
                $hospedeBloqueado = Hospede::where('nome', 'Bloqueado')->first();

                if (!$hospedeBloqueado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hóspede "Bloqueado" não encontrado. Crie um hóspede com este nome.'
                    ], 400);
                }

                $dadosReserva['hospede_id'] = $hospedeBloqueado->id;
                $dadosReserva['situacao'] = 'bloqueado';
            } else {
                $request->validate([
                    'hospede_id' => 'required|exists:hospedes,id'
                ]);
                $dadosReserva['situacao'] = $request->situacao;
                $dadosReserva['hospede_id'] = $request->hospede_id;
            }

            $reserva = Reserva::create($dadosReserva);

            return response()->json([
                'success' => true,
                'message' => $request->tipo === 'bloqueio'
                    ? 'Bloqueio criado com sucesso!'
                    : 'Reserva criada com sucesso!',
                'reserva_id' => $reserva->id
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar reserva rápida', ['erro' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Erro inesperado ao criar reserva.'
            ], 500);
        }
    }
}