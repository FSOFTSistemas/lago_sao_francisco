<?php

namespace App\Http\Controllers;

use App\Models\Quarto;
use App\Models\Categoria;
use App\Models\Reserva;
use App\Models\Tarifa;
use App\Models\Hospede;
use App\Models\PreferenciasHotel;
use App\Models\Funcionario;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MapaController extends Controller
{
    // ... (métodos index e getDadosMapa permanecem iguais) ...
    public function index(Request $request)
    {
        $dataInicio = $request->get('data_inicio', Carbon::now()->subDays(5)->format('Y-m-d'));
        $dataFim = $request->get('data_fim', Carbon::now()->addDays(15)->format('Y-m-d'));
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

            // Mapeamento de Nomes (Funcionários e Users)
            $funcionariosMap = Funcionario::pluck('nome', 'id')->toArray();
            $usersMap = User::pluck('name', 'id')->toArray();

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

            $dadosQuartos = [];

            foreach ($quartos as $quarto) {
                $reservasFormatadas = [];
                foreach ($quarto->reservas as $reserva) {
                    
                    // Lógica para encontrar o nome (Só vai aparecer se tiver vendedor_id salvo)
                    $nomeVendedor = null;
                    if ($reserva->vendedor_id) {
                        if (isset($funcionariosMap[$reserva->vendedor_id])) {
                            $nomeVendedor = $funcionariosMap[$reserva->vendedor_id];
                        } elseif (isset($usersMap[$reserva->vendedor_id])) {
                            $nomeVendedor = $usersMap[$reserva->vendedor_id];
                        }
                    }

                    $reservasFormatadas[] = [
                        'id' => $reserva->id,
                        'hospede_nome' => $reserva->hospede ? $reserva->hospede->nome : 'Sem hóspede',
                        'hospede_telefone' => $reserva->hospede ? $reserva->hospede->telefone : null,
                        'vendedor_nome' => $nomeVendedor,
                        'data_checkin' => $reserva->data_checkin,
                        'data_checkout' => $reserva->data_checkout,
                        'situacao' => $reserva->situacao,
                        'valor_diaria' => $reserva->valor_diaria,
                        'n_adultos' => $reserva->n_adultos,
                        'n_criancas' => $reserva->n_criancas,
                        'observacoes' => $reserva->observacoes,
                        'nomes_hospedes_secundarios' => $reserva->nomes_hospedes_secundarios
                    ];
                }

                $dadosQuartos[] = [
                    'id' => $quarto->id,
                    'nome' => $quarto->nome,
                    'posicao' => $quarto->posicao,
                    'ocupantes' => $quarto->categoria->ocupantes ?? 999, 
                    'categoria' => $quarto->categoria,
                    'categoria_nome' => $quarto->categoria->titulo ?? '',
                    'reservas' => $reservasFormatadas
                ];
            }

            $ocupacaoPorData = $this->calcularOcupacaoPorData($datas, $quartos);

            return response()->json([
                'success' => true,
                'datas' => $datas,
                'quartos' => $dadosQuartos,
                'ocupacao' => $ocupacaoPorData
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    private function calcularOcupacaoPorData($datas, $quartos)
    {
        $ocupacao = [];
        $totalQuartos = $quartos->count();
        foreach ($datas as $data) {
            $quartosOcupados = 0;
            foreach ($quartos as $quarto) {
                if ($quarto->reservas->contains(fn($r) => $r->data_checkin <= $data && $r->data_checkout > $data)) {
                    $quartosOcupados++;
                }
            }
            $percentual = $totalQuartos > 0 ? round(($quartosOcupados / $totalQuartos) * 100) : 0;
            $ocupacao[$data] = ['ocupados' => $quartosOcupados, 'total' => $totalQuartos, 'percentual' => $percentual];
        }
        return $ocupacao;
    }

    public function criarReservaRapida(Request $request)
    {
        try {
            $request->validate([
                'quarto_id'     => 'required|exists:quartos,id',
                'data_checkin'  => 'required|date',
                'data_checkout' => 'required|date|after:data_checkin',
                'tipo'          => 'required|in:reserva,bloqueio',
                'n_adultos'     => 'nullable|integer|min:1', 
                'n_criancas'    => 'nullable|integer|min:0',
                'nomes_hospedes_secundarios' => 'nullable|string',
            ]);

            if ($request->tipo === 'reserva') {
                $quarto = \App\Models\Quarto::with('categoria')->find($request->quarto_id);
                $capacidadeMaxima = $quarto->categoria ? $quarto->categoria->ocupantes : 999;
                
                $nAdultos = $request->input('n_adultos', 1);
                $nCriancas = $request->input('n_criancas', 0);
                $totalPessoas = $nAdultos + $nCriancas;

                if ($totalPessoas > ($capacidadeMaxima + 10)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Capacidade excedida! Máximo de {$capacidadeMaxima} pessoas."
                    ], 422);
                }
            }

            // Cálculo Automático de Preço
            $valorDiariaFinal = 0;
            $valorTotalFinal = 0;

            if ($request->tipo === 'reserva') {
                $quarto = \App\Models\Quarto::with('categoria.tarifa')->find($request->quarto_id);
                $categoria = $quarto->categoria;
                $tarifa = $categoria->tarifa;

                if ($tarifa) {
                    $checkin = \Carbon\Carbon::parse($request->data_checkin);
                    $checkout = \Carbon\Carbon::parse($request->data_checkout);
                    $periodo = \Carbon\CarbonPeriod::create($checkin, $checkout->copy()->subDay());

                    $totalTarifa = 0;
                    $quantidadeDias = 0;

                    foreach ($periodo as $dia) {
                        $campo = match ($dia->dayOfWeek) {
                            0 => 'dom', 1 => 'seg', 2 => 'ter', 3 => 'qua', 4 => 'qui', 5 => 'sex', 6 => 'sab',
                        };
                        $valorDia = (float) ($tarifa->$campo ?? 0);
                        $totalTarifa += $valorDia;
                        $quantidadeDias++;
                    }

                    $mediaTarifa = $quantidadeDias > 0 ? $totalTarifa / $quantidadeDias : 0;
                    
                    $padraoAdultos = $tarifa->padrao_adultos ?? 0;
                    $padraoCriancas = $tarifa->padrao_criancas ?? 0;
                    $adicionalAdulto = (float) ($tarifa->adicional_adulto ?? 0);
                    $adicionalCrianca = (float) ($tarifa->adicional_crianca ?? 0);

                    $nAdultosInput = $request->input('n_adultos', 1);
                    $nCriancasInput = $request->input('n_criancas', 0);

                    $extrasAdultos = max(0, $nAdultosInput - $padraoAdultos);
                    $extrasCriancas = max(0, $nCriancasInput - $padraoCriancas);

                    $valorDiariaFinal = $mediaTarifa + ($extrasAdultos * $adicionalAdulto) + ($extrasCriancas * $adicionalCrianca);
                    
                    $diasTotal = $checkin->diffInDays($checkout);
                    if ($diasTotal < 1) $diasTotal = 1;
                    
                    $valorTotalFinal = $valorDiariaFinal * $diasTotal;
                }
            }

            $dadosReserva = [
                'quarto_id'     => $request->quarto_id,
                'data_checkin'  => $request->data_checkin,
                'data_checkout' => $request->data_checkout,
                'valor_diaria'  => $valorDiariaFinal,
                'valor_total'   => $valorTotalFinal,
                'n_adultos'     => $request->input('n_adultos', 1), 
                'n_criancas'    => $request->input('n_criancas', 0),
                'nomes_hospedes_secundarios' => $request->nomes_hospedes_secundarios,
                'observacoes'   => $request->observacoes,
                // 'vendedor_id' removido daqui para não salvar em reservas comuns
            ];

            if ($request->tipo === 'bloqueio') {
                $hospedeBloqueado = \App\Models\Hospede::where('nome', 'Bloqueado')->first();
                if (!$hospedeBloqueado) {
                    return response()->json(['success' => false, 'message' => 'Hóspede "Bloqueado" não encontrado.'], 400);
                }
                $dadosReserva['hospede_id'] = $hospedeBloqueado->id;
                $dadosReserva['situacao'] = 'bloqueado';
                $dadosReserva['valor_diaria'] = 0;
                $dadosReserva['valor_total'] = 0;
                
                // ADICIONADO AQUI: Salva o vendedor_id APENAS se for bloqueio
                $dadosReserva['vendedor_id'] = Auth::id(); 
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