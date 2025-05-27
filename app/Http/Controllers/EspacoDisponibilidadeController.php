<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use App\Models\Espaco;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class EspacoDisponibilidadeController extends Controller
{
    /**
     * Retorna a disponibilidade dos espaços para um determinado período.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDisponibilidade(Request $request)
    {
        // 1. Validar as datas de entrada
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        // 2. Obter os espaços relevantes (o EmpresaScope será aplicado automaticamente)
        // Selecionar apenas colunas necessárias para otimização
        $espacos = Espaco::orderBy('nome')->get(['id', 'nome']);

        // 3. Obter os aluguéis que se sobrepõem ao intervalo de datas solicitado
        // A lógica busca aluguéis cuja data de início OU fim esteja dentro do intervalo,
        // OU que envolvam completamente o intervalo.
        $alugueis = Aluguel::where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('data_inicio', [$startDate, $endDate])
                      ->orWhereBetween('data_fim', [$startDate, $endDate])
                      ->orWhere(function ($query) use ($startDate, $endDate) {
                          $query->where('data_inicio', '<=', $startDate)
                                ->where('data_fim', '>=', $endDate);
                      });
             })
             // Filtrar pelos IDs dos espaços obtidos anteriormente
             ->whereIn('espaco_id', $espacos->pluck('id'))
             // Selecionar apenas os campos necessários
             ->select('id', 'espaco_id', 'data_inicio', 'data_fim')
             ->get();

        // 4. Estruturar a resposta JSON
        $response = [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'spaces' => [],
        ];

        foreach ($espacos as $espaco) {
            // Filtrar os aluguéis para o espaço atual e formatar os dados
            $bookingsData = $alugueis->where('espaco_id', $espaco->id)->map(function ($aluguel) {
                return [
                    // Formatar as datas para o padrão YYYY-MM-DD
                    'start' => Carbon::parse($aluguel->data_inicio)->toDateString(),
                    'end' => Carbon::parse($aluguel->data_fim)->toDateString(),
                    'aluguel_id' => $aluguel->id, // ID do aluguel, pode ser útil
                ];
            })->values(); // Usar values() para garantir um array JSON padrão

            $response['spaces'][] = [
                'id' => $espaco->id,
                'nome' => $espaco->nome,
                'bookings' => $bookingsData, // Array com os períodos reservados para este espaço
            ];
        }

        return response()->json($response);
    }
}

