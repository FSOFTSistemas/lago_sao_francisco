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
        $dataInicio = $request->get('data_inicio', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', Carbon::parse($dataInicio)->addDays(13)->format('Y-m-d'));
        $hospedes = Hospede::all();
        
        return view('mapa.index', compact('dataInicio', 'dataFim', 'hospedes'));
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

            // Buscar quartos agrupados por categoria
            $categorias = Categoria::with(['quartos' => function($query) {
                $query->where('status', 1)->orderBy('posicao');
            }])->where('status', 1)->orderBy('posicao')->get();

            // Buscar reservas no período
            $reservas = Reserva::with(['hospede', 'quarto'])
                ->where(function($query) use ($dataInicio, $dataFim) {
                    $query->where('data_checkin', '<=', $dataFim)
                          ->where('data_checkout', '>', $dataInicio);
                })
                ->whereNotIn('situacao', ['cancelado'])
                ->get();

            // Organizar dados do mapa
            $dadosMapa = [];
            
            foreach ($categorias as $categoria) {
                $tarifas = $this->obterTarifasCategoria($categoria->id, $datas);
                
                $dadosCategoria = [
                    'id' => $categoria->id,
                    'titulo' => $categoria->titulo,
                    'quartos' => [],
                    'tarifas' => $tarifas,
                    'total_quartos' => $categoria->quartos->count()
                ];

                foreach ($categoria->quartos as $quarto) {
                    $dadosQuarto = [
                        'id' => $quarto->id,
                        'nome' => $quarto->nome,
                        'categoria_id' => $categoria->id,
                        'reservas' => []
                    ];

                    // Buscar reservas deste quarto no período
                    $reservasQuarto = $reservas->where('quarto_id', $quarto->id);
                    
                    foreach ($reservasQuarto as $reserva) {
                        $dadosQuarto['reservas'][] = [
                            'id' => $reserva->id,
                            'hospede_nome' => $reserva->hospede ? $reserva->hospede->nome : 'Sem hóspede',
                            'data_checkin' => $reserva->data_checkin,
                            'data_checkout' => $reserva->data_checkout,
                            'situacao' => $reserva->situacao,
                            'valor_diaria' => $reserva->valor_diaria
                        ];
                    }

                    $dadosCategoria['quartos'][] = $dadosQuarto;
                }

                $dadosMapa[] = $dadosCategoria;
            }

            // Calcular ocupação por data
            $ocupacaoPorData = $this->calcularOcupacaoPorData($datas, $reservas, $categorias);

            return response()->json([
                'success' => true,
                'datas' => $datas,
                'categorias' => $dadosMapa,
                'ocupacao' => $ocupacaoPorData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados do mapa: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obterTarifasCategoria($categoriaId, $datas)
    {
        $tarifas = [];
        $tarifa = Tarifa::where('categoria_id', $categoriaId)
                       ->where('ativo', true)
                       ->first();

        if ($tarifa) {
            foreach ($datas as $data) {
                $diaSemana = Carbon::parse($data)->dayOfWeek;
                $valorDiaria = match($diaSemana) {
                    0 => $tarifa->dom ?? 0, // Domingo
                    1 => $tarifa->seg ?? 0, // Segunda
                    2 => $tarifa->ter ?? 0, // Terça
                    3 => $tarifa->qua ?? 0, // Quarta
                    4 => $tarifa->qui ?? 0, // Quinta
                    5 => $tarifa->sex ?? 0, // Sexta
                    6 => $tarifa->sab ?? 0, // Sábado
                };
                
                $tarifas[$data] = $valorDiaria;
            }
        } else {
            // Se não houver tarifa, preencher com 0
            foreach ($datas as $data) {
                $tarifas[$data] = 0;
            }
        }

        return $tarifas;
    }

    private function calcularOcupacaoPorData($datas, $reservas, $categorias)
    {
        $ocupacao = [];
        $totalQuartos = $categorias->sum(function($categoria) {
            return $categoria->quartos->count();
        });

        foreach ($datas as $data) {
            $quartosOcupados = $reservas->filter(function($reserva) use ($data) {
                return $reserva->data_checkin <= $data && $reserva->data_checkout > $data;
            })->count();

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
            'hospede_id' => 'required'
        ]);

        $valorFormatado = str_replace(['.', ','], ['', '.'], $request->valor_diaria); // Ex: "350,00" → "350.00"

        $dadosReserva = [
            'quarto_id' => $request->quarto_id,
            'data_checkin' => $request->data_checkin,
            'data_checkout' => $request->data_checkout,
            'valor_diaria' => $valorFormatado ?? 0,
            'n_adultos' => 1,
            'n_criancas' => 0,
            'hospede_id' => $request->hospede_id
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
            $dadosReserva['situacao'] = $request->situacao;
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

