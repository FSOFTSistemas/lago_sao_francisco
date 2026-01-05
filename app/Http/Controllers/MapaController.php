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
            $dataInicio = \Carbon\Carbon::parse($request->get('data_inicio', \Carbon\Carbon::now()->startOfWeek()));
            $dataFim = \Carbon\Carbon::parse($request->get('data_fim', $dataInicio->copy()->addDays(13)));

            // Gerar array de datas
            $datas = [];
            $dataAtual = $dataInicio->copy();
            while ($dataAtual <= $dataFim) {
                $datas[] = $dataAtual->format('Y-m-d');
                $dataAtual->addDay();
            }

            // Busca quartos ordenados
            $quartos = \App\Models\Quarto::where('status', 1)
                ->orderBy('posicao', 'asc')
                ->with(['categoria', 'reservas' => function ($query) use ($dataInicio, $dataFim) {
                    $query->with('hospede')
                        ->where(function ($q) use ($dataInicio, $dataFim) {
                            $q->where('data_checkin', '<=', $dataFim)
                              ->where('data_checkout', '>', $dataInicio);
                        })
                        ->whereNotIn('situacao', ['cancelado']);
                }])
                ->get();

            // Organizar dados do mapa
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
                        'observacoes' => $reserva->observacoes,
                    ];
                }

                $dadosQuartos[] = [
                    'id' => $quarto->id,
                    'nome' => $quarto->nome,
                    'posicao' => $quarto->posicao,
                    
                    // --- CORREÇÃO AQUI ---
                    // Adicionamos a capacidade para o React conseguir ler
                    'ocupantes' => $quarto->categoria->ocupantes ?? 999, 
                    
                    // Enviamos também o objeto categoria completo por segurança
                    'categoria' => $quarto->categoria,
                    
                    'categoria_nome' => $quarto->categoria->titulo ?? '',
                    'reservas' => $reservasFormatadas
                ];
            }

            // Calcular ocupação
            $ocupacaoPorData = $this->calcularOcupacaoPorData($datas, $quartos);

            return response()->json([
                'success' => true,
                'datas' => $datas,
                'quartos' => $dadosQuartos,
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
            // 1. Validação dos inputs
            $request->validate([
                'quarto_id'     => 'required|exists:quartos,id',
                'data_checkin'  => 'required|date',
                'data_checkout' => 'required|date|after:data_checkin',
                'tipo'          => 'required|in:reserva,bloqueio',
                'n_adultos'     => 'nullable|integer|min:1', 
                'n_criancas'    => 'nullable|integer|min:0',
            ]);

            // --- NOVA VALIDAÇÃO DE CAPACIDADE (O SEGREDO ESTÁ AQUI) ---
            if ($request->tipo === 'reserva') {
                // Busca o quarto e a categoria dele
                $quarto = \App\Models\Quarto::with('categoria')->find($request->quarto_id);
                
                // Pega a capacidade (se não tiver categoria, assume um valor alto ou 0)
                $capacidadeMaxima = $quarto->categoria ? $quarto->categoria->ocupantes : 999;
                
                $nAdultos = $request->input('n_adultos', 1);
                $nCriancas = $request->input('n_criancas', 0);
                $totalPessoas = $nAdultos + $nCriancas;

                if ($totalPessoas > ($capacidadeMaxima + 10)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Capacidade excedida! O quarto comporta máximo de {$capacidadeMaxima} pessoas, mas você tentou inserir {$totalPessoas}."
                    ], 422); // 422 = Unprocessable Entity
                }
            }
            // -----------------------------------------------------------

            $dadosReserva = [
                'quarto_id'     => $request->quarto_id,
                'data_checkin'  => $request->data_checkin,
                'data_checkout' => $request->data_checkout,
                'valor_diaria'  => $request->valor_diaria ?? 0,
                'valor_total'   => $request->valor_total ?? 0,
                'n_adultos'     => $request->input('n_adultos', 1), 
                'n_criancas'    => $request->input('n_criancas', 0),
            ];

            if ($request->tipo === 'bloqueio') {
                $hospedeBloqueado = \App\Models\Hospede::where('nome', 'Bloqueado')->first();
                if (!$hospedeBloqueado) {
                    return response()->json(['success' => false, 'message' => 'Hóspede "Bloqueado" não encontrado.'], 400);
                }
                $dadosReserva['hospede_id'] = $hospedeBloqueado->id;
                $dadosReserva['situacao'] = 'bloqueado';
            } else {
                $request->validate(['hospede_id' => 'required|exists:hospedes,id']);
                $dadosReserva['situacao'] = $request->situacao;
                $dadosReserva['hospede_id'] = $request->hospede_id;
            }

            $reserva = \App\Models\Reserva::create($dadosReserva);

            return response()->json([
                'success' => true,
                'message' => 'Reserva criada com sucesso!',
                'reserva_id' => $reserva->id
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro criar reserva rápida', ['erro' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}