<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Quarto;
use App\Models\Hospede;
use App\Models\Categoria;
use App\Models\FormaPagamento;
use App\Models\Funcionario;
use App\Models\LogReserva;
use App\Models\PreferenciasHotel;
use App\Models\Produto;
use App\Models\Transacao;
use App\Models\User;
use App\Models\Temporada;
use App\Models\Tarifa;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Mail\VoucherReservaEmail;
use App\Models\ReservaPet;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReservaController extends Controller
{
    private $canaisVenda = ['WhatsApp', 'Instagram', 'Telefone', 'Indicação', 'Balcão', 'Facebook', 'Email', 'Outros'];

    // ... (index, create, store, edit, etc... mantidos iguais) ...
    public function index(Request $request)
    {
        $situacao = $request->input('situacao', 'todos');
        $query = Reserva::with(['quarto', 'hospede'])->orderBy('id', 'desc');
        if ($situacao !== 'todos') $query->where('situacao', $situacao);
        $reservas = $query->get(); 
        return view('reserva.index', compact('reservas', 'situacao'));
    }

    public function create(Request $request)
    {
        $checkin = $request->data_checkin;
        $checkout = $request->data_checkout;
        $quartos = Quarto::query();

        if ($checkin && $checkout) {
            $ocupados = Reserva::where(function ($query) use ($checkin, $checkout) {
                $query->whereBetween('data_checkin', [$checkin, $checkout])
                    ->orWhereBetween('data_checkout', [$checkin, $checkout])
                    ->orWhere(function ($query) use ($checkin, $checkout) {
                        $query->where('data_checkin', '<=', $checkin)
                            ->where('data_checkout', '>=', $checkout);
                    });
            })->pluck('quarto_id');
            $quartos = $quartos->whereNotIn('id', $ocupados);
        }

        $quartosAgrupados = $quartos->with('categoria')->get()->groupBy('categoria.titulo');
        $categorias = Categoria::where('status', 1)->orderBy('posicao')->get();
        $formasPagamento = FormaPagamento::whereNotIn('descricao', ['sympla', 'boleto-bancário', 'crediário'])->get();
        $produtos = Produto::where('ativo', true)->orderBy('descricao')->get();
        $hospedes = Hospede::all();
        $hospedeBloqueado = Hospede::where('nome', 'Bloqueado')->first();
        $canaisVenda = $this->canaisVenda;
        $vendedores = Funcionario::all();
        
        $preferencias = PreferenciasHotel::first();

        return view('reserva.create', compact('quartosAgrupados', 'categorias', 'hospedes', 'hospedeBloqueado', 'formasPagamento', 'produtos', 'checkin', 'checkout', 'canaisVenda', 'vendedores', 'preferencias'));
    }

    public function validarSupervisor(Request $request)
    {
        $senha = $request->input('senha');
        $funcionarios = Funcionario::whereNotNull('senha_supervisor')->get();
        
        foreach ($funcionarios as $f) {
            $hashBanco = trim((string) $f->senha_supervisor);
            if ($hashBanco === '') continue;

            $isValid = false;
            if (hash_equals($hashBanco, md5($senha))) {
                $isValid = true;
            } elseif (str_starts_with($hashBanco, '$2') || str_starts_with($hashBanco, '$argon')) {
                $hashNorm = preg_replace('/^\$2b\$/', '$2y$', $hashBanco);
                if (password_verify($senha, $hashNorm)) {
                    $isValid = true;
                }
            }

            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'supervisor_id' => $f->id,
                    'supervisor_nome' => $f->nome 
                ]);
            }
        }
        
        return response()->json(['success' => false]);
    }

   public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'quarto_id' => 'required|exists:quartos,id',
                'hospede_id' => 'required|exists:hospedes,id',
                'data_checkin' => 'required|date',
                'data_checkout' => 'required|date|after_or_equal:data_checkin',
                'valor_diaria' => 'nullable',
                'valor_total' => 'nullable',
                'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado',
                'n_adultos' => 'required|integer',
                'n_criancas' => 'required|integer',
                'n_criancas_nao_pagantes' => 'nullable|integer', // NOVO
                'observacoes' => 'nullable|string',
                'placa_veiculo' => 'nullable|string|max:10',
                'canal_venda' => 'nullable|string|in:' . implode(',', $this->canaisVenda),
                'vendedor_id' => 'nullable|exists:funcionarios,id',
                'nomes_hospedes_secundarios' => 'nullable|string',
                // Campos de pets (não salvos na tabela reservas diretamente)
                'qtd_pet_pequeno' => 'nullable|integer|min:0',
                'qtd_pet_medio'   => 'nullable|integer|min:0',
                'qtd_pet_grande'  => 'nullable|integer|min:0',
            ]);

            // Validação de Capacidade (Pessoas)
            $quarto = Quarto::with('categoria')->findOrFail($validatedData['quarto_id']);
            $maxOcupantes = $quarto->categoria->ocupantes;
            $totalPessoas = $validatedData['n_adultos'] + $validatedData['n_criancas'] + ($validatedData['n_criancas_nao_pagantes'] ?? 0); 
        

            if ($totalPessoas > ($maxOcupantes + 10)) {
                return redirect()->back()->withInput()->with('error', "Capacidade excedida.");
            }

            // Cálculo da Diária Base (Quarto)
            if (empty($validatedData['valor_diaria'])) {
                $calculo = $this->calcularTarifaAutomatica(
                    $validatedData['quarto_id'],
                    $validatedData['data_checkin'],
                    $validatedData['data_checkout'],
                    $validatedData['n_adultos'],
                    $validatedData['n_criancas']
                );
                if (!$calculo['sucesso']) return redirect()->back()->withInput()->with('error', $calculo['mensagem']);
                $validatedData['valor_diaria'] = $calculo['valor_diaria'];
            } else {
                $validatedData['valor_diaria'] = str_replace(['.', ','], ['', '.'], $validatedData['valor_diaria']);
            }

            // Cálculo dos Dias
            $dtCheckin = \Carbon\Carbon::parse($validatedData['data_checkin']);
            $dtCheckout = \Carbon\Carbon::parse($validatedData['data_checkout']);
            $dias = $dtCheckin->diffInDays($dtCheckout);
            if ($dias < 1) $dias = 1;

            // 1. Cria a reserva (sem o valor total final ainda, pois falta somar os pets)
            // A gente calcula o total do quarto primeiro
            $valorTotalQuarto = (float)$validatedData['valor_diaria'] * $dias;
            $validatedData['valor_total'] = $valorTotalQuarto;
            
            $reserva = Reserva::create($validatedData);

            // 2. Processa Pets e Atualiza Valor Total
            // Soma o valor dos pets ao valor total da reserva
            $valorTotalPets = $this->processarPets($reserva, $request, $dias);
            
            $reserva->valor_total = $valorTotalQuarto + $valorTotalPets;
            $reserva->save();

            LogReserva::registrarCriacao($reserva, Auth::id());

            return redirect()->route('reserva.edit', $reserva->id)->with('success', 'Reserva criada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function edit(Reserva $reserva)
    {
        $quartosAgrupados = Quarto::with('categoria')->get()->groupBy('categoria.titulo');
        $categorias = Categoria::where('status', 1)->orderBy('posicao')->get();
        $formasPagamento = FormaPagamento::whereNotIn('descricao', ['sympla','boleto-bancário','crediário'])->get();
        $produtos = Produto::where('ativo', true)->orderBy('descricao')->get();
        $hospedes = Hospede::all();
        $hospedeBloqueado = Hospede::where('nome', 'Bloqueado')->first();
        $vendedores = Funcionario::all();
        $logs = LogReserva::where('reserva_id', $reserva->id)->with('usuario')->orderBy('created_at', 'desc')->get();

        $hoje = Carbon::today();
        $dataCheckin = Carbon::parse($reserva->data_checkin);
        $podeHospedar = $hoje->equalTo($dataCheckin) && in_array($reserva->situacao, ['reserva']) && !in_array($reserva->situacao, ['finalizada', 'cancelado']);
        $canaisVenda = $this->canaisVenda;

        $reserva->load('pets');
        $preferencias = PreferenciasHotel::first();

        $petsPequeno = $reserva->pets->where('tamanho', 'pequeno')->first()->quantidade ?? 0;
        $petsMedio = $reserva->pets->where('tamanho', 'medio')->first()->quantidade ?? 0;
        $petsGrande = $reserva->pets->where('tamanho', 'grande')->first()->quantidade ?? 0;

        return view('reserva.create', compact(
            'reserva', 'quartosAgrupados', 'categorias', 'hospedes', 'hospedeBloqueado', 
            'formasPagamento', 'produtos', 'podeHospedar', 'logs', 'canaisVenda', 
            'vendedores', 'preferencias', 'petsPequeno', 'petsMedio', 'petsGrande'
        ));
    }

    public function update(Request $request, Reserva $reserva)
    {
        try {
            $validatedData = $request->validate([
                'quarto_id' => 'required|exists:quartos,id',
                'hospede_id' => 'nullable|exists:hospedes,id',
                'data_checkin' => 'required|date',
                'data_checkout' => 'required|date|after_or_equal:data_checkin',
                'valor_diaria' => 'nullable',
                'valor_total' => 'nullable',
                'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado,finalizada,cancelado',
                'n_adultos' => 'required|integer',
                'n_criancas' => 'required|integer',
                'n_criancas_nao_pagantes' => 'nullable|integer',
                'observacoes' => 'nullable|string',
                'placa_veiculo' => 'nullable|string|max:10',
                'canal_venda' => 'nullable|string|in:' . implode(',', $this->canaisVenda),
                'vendedor_id' => 'nullable|exists:funcionarios,id',
                'nomes_hospedes_secundarios' => 'nullable|string',
                'supervisor_id_autorizacao' => 'nullable', 
                'qtd_pet_pequeno' => 'nullable|integer|min:0',
                'qtd_pet_medio'   => 'nullable|integer|min:0',
                'qtd_pet_grande'  => 'nullable|integer|min:0',
            ]);

            $situacaoAtual = $reserva->situacao;
            $novaSituacao = $validatedData['situacao'];
            if ($situacaoAtual === 'hospedado' && $novaSituacao !== 'hospedado') return redirect()->back()->withInput()->with('error', 'Não é possível alterar reserva hospedada.');
            if (in_array($situacaoAtual, ['finalizada', 'cancelado']) && $novaSituacao !== $situacaoAtual) return redirect()->back()->withInput()->with('error', 'Reserva finalizada/cancelada não pode ser alterada.');

            $quarto = Quarto::with('categoria')->findOrFail($validatedData['quarto_id']);
            $maxOcupantes = $quarto->categoria->ocupantes;
            if (($validatedData['n_adultos'] + $validatedData['n_criancas']) > ($maxOcupantes + 10)) {
                return redirect()->back()->withInput()->with('error', "Capacidade excedida.");
            }

            if (empty($validatedData['valor_diaria'])) {
                $calculo = $this->calcularTarifaAutomatica(
                    $validatedData['quarto_id'],
                    $validatedData['data_checkin'],
                    $validatedData['data_checkout'],
                    $validatedData['n_adultos'],
                    $validatedData['n_criancas']
                );
                if (!$calculo['sucesso']) return redirect()->back()->withInput()->with('error', $calculo['mensagem']);
                $validatedData['valor_diaria'] = $calculo['valor_diaria'];
            } else {
                $validatedData['valor_diaria'] = str_replace(['.', ','], ['', '.'], $validatedData['valor_diaria']);
            }

            $dtCheckin = \Carbon\Carbon::parse($validatedData['data_checkin']);
            $dtCheckout = \Carbon\Carbon::parse($validatedData['data_checkout']);
            $dias = $dtCheckin->diffInDays($dtCheckout);
            if ($dias < 1) $dias = 1;

           $valorTotalQuarto = (float)$validatedData['valor_diaria'] * $dias;
            $validatedData['valor_total'] = $valorTotalQuarto; // Temporário, vai somar pets depois

            // Dados antigos para log
            $dadosAntigos = $reserva->toArray();
            $valorAntigo = $reserva->valor_diaria;
            $supervisorId = $request->input('supervisor_id_autorizacao');
            
            unset($validatedData['supervisor_id_autorizacao']);
            unset($validatedData['qtd_pet_pequeno']);
            unset($validatedData['qtd_pet_medio']);
            unset($validatedData['qtd_pet_grande']);

            $reserva->update($validatedData);
            
            $reserva->pets()->delete(); 
            $valorTotalPets = $this->processarPets($reserva, $request, $dias);

            // Atualiza total final
            $reserva->valor_total = $valorTotalQuarto + $valorTotalPets;
            $reserva->save();
            
            // --- LOGS ---
            $valorNovo = $reserva->valor_diaria;
            $descricaoLog = null;
            
            // Se houve mudança de valor e tem supervisor, cria mensagem detalhada
            if (abs((float)$valorAntigo - (float)$valorNovo) > 0.01 && $supervisorId) {
                $supervisor = Funcionario::find($supervisorId);
                $nomeSupervisor = $supervisor ? $supervisor->nome : 'N/D';
                $vAnt = number_format($valorAntigo, 2, ',', '.');
                $vNov = number_format($valorNovo, 2, ',', '.');
                
                $descricaoLog = "Valor da diária alterado de R$ {$vAnt} para R$ {$vNov}. Autorizado por: {$nomeSupervisor}.";
            }

            // Chama o método do Model, passando a descrição personalizada (ou null para usar o padrão)
            LogReserva::registrarEdicao($reserva, Auth::id(), $dadosAntigos, $descricaoLog);

            if ($dadosAntigos['situacao'] !== $reserva->situacao) {
                LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $dadosAntigos['situacao']);
            }

            return redirect()->back()->with('success', 'Reserva atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    private function processarPets(Reserva $reserva, Request $request, int $dias)
    {
        $preferencias = PreferenciasHotel::first();
        $totalPets = 0;

        $tipos = [
            'pequeno' => 'qtd_pet_pequeno',
            'medio'   => 'qtd_pet_medio',
            'grande'  => 'qtd_pet_grande'
        ];

        $valoresRef = [
            'pequeno' => $preferencias->valor_pet_pequeno ?? 0,
            'medio'   => $preferencias->valor_pet_medio ?? 0,
            'grande'  => $preferencias->valor_pet_grande ?? 0,
        ];

        foreach ($tipos as $tamanho => $campoInput) {
            $quantidade = (int) $request->input($campoInput, 0);
            
            if ($quantidade > 0) {
                $valorUnitario = $valoresRef[$tamanho];
                
                ReservaPet::create([
                    'reserva_id' => $reserva->id,
                    'tamanho' => $tamanho,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario
                ]);

                // Cálculo: Quantidade * Valor Diária Pet * Dias da Reserva
                $totalPets += ($quantidade * $valorUnitario * $dias);
            }
        }

        return $totalPets;
    }

    private function calcularTarifaAutomatica($quartoId, $checkinData, $checkoutData, $nAdultos, $nCriancas)
    {
        $quarto = Quarto::with('categoria')->find($quartoId);
        if (!$quarto || !$quarto->categoria) return ['sucesso' => false, 'mensagem' => 'Quarto/Categoria não encontrados.'];

        $checkin = \Carbon\Carbon::parse($checkinData);
        $temporada = Temporada::where('data_inicio', '<=', $checkin)->where('data_fim', '>=', $checkin)->first();
        $isAlta = $temporada ? true : false;

        $queryTarifa = Tarifa::where('categoria_id', $quarto->categoria_id)->where('alta_temporada', $isAlta);
        if ($isAlta) $queryTarifa->where('data_inicio', '<=', $checkin)->where('data_fim', '>=', $checkin);
        
        $tarifa = $queryTarifa->first();
        if (!$tarifa) {
            $tarifa = Tarifa::where('categoria_id', $quarto->categoria_id)->where('alta_temporada', false)->first();
            if (!$tarifa) return ['sucesso' => false, 'mensagem' => 'Nenhuma tarifa encontrada.'];
        }

        $checkout = \Carbon\Carbon::parse($checkoutData);
        $periodo = \Carbon\CarbonPeriod::create($checkin, $checkout->copy()->subDay());
        $totalBase = 0; $diasCount = 0;

        foreach ($periodo as $dia) {
            $campo = match ($dia->dayOfWeek) { 0 => 'dom', 1 => 'seg', 2 => 'ter', 3 => 'qua', 4 => 'qui', 5 => 'sex', 6 => 'sab' };
            $totalBase += (float)$tarifa->$campo;
            $diasCount++;
        }

        $mediaDiaria = $diasCount > 0 ? $totalBase / $diasCount : 0;
        $extrasAdultos = max(0, $nAdultos - ($tarifa->padrao_adultos ?? 0));
        $extrasCriancas = max(0, $nCriancas - ($tarifa->padrao_criancas ?? 0));
        $valorAdicional = ($extrasAdultos * ($tarifa->adicional_adulto ?? 0)) + ($extrasCriancas * ($tarifa->adicional_crianca ?? 0));

        return ['sucesso' => true, 'valor_diaria' => number_format($mediaDiaria + $valorAdicional, 2, '.', '')];
    }

    // ... (restante do código: destroy, quartosDisponiveis, etc... mantido) ...
    public function destroy(Reserva $reserva)
    {
        try {
            $reserva->delete();
            return redirect()->route('reserva.index')->with('success', 'Reserva removida com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao remover reserva!');
        }
    }

    public function quartosDisponiveis(Request $request)
    {
        $checkin = Carbon::createFromFormat('d/m/Y', $request->checkin);
        $checkout = Carbon::createFromFormat('d/m/Y', $request->checkout);
        $reservaId = $request->reserva_id; // pode ser null

        $quartosIndisponiveis = Reserva::where(function ($query) use ($checkin, $checkout) {
            $query->whereBetween('data_checkin', [$checkin, $checkout->copy()->subDay()])
                ->orWhereBetween('data_checkout', [$checkin->copy()->addDay(), $checkout])
                ->orWhere(function ($query) use ($checkin, $checkout) {
                    $query->where('data_checkin', '<', $checkin)
                        ->where('data_checkout', '>', $checkout);
                });
        })
            ->when($reservaId, function ($query, $reservaId) {
                $query->where('id', '!=', $reservaId);
            })
            ->pluck('quarto_id');

        $quartosDisponiveis = Quarto::with('categoria')
            ->whereNotIn('id', $quartosIndisponiveis)
            ->get()
            ->groupBy('categoria.titulo');

        $response = [];
        foreach ($quartosDisponiveis as $categoria => $quartos) {
            $response[] = [
                'categoria' => $categoria,
                'quartos' => $quartos->map(function ($quarto) {
                    return [
                        'id' => $quarto->id,
                        'nome' => $quarto->nome,
                        'categoria_id' => $quarto->categoria_id,
                        'ocupantes' => $quarto->categoria->ocupantes
                    ];
                })
            ];
        }

        return response()->json($response);
    }

    public function finalizar($id)
    {
        try {
            $reserva = Reserva::findOrFail($id);

            if (in_array($reserva->situacao, ['finalizada', 'cancelado'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta reserva já foi finalizada ou cancelada.'
                ], 400);
            }

            $transacoes = Transacao::where('reserva_id', $reserva->id)
                ->where('status', true)
                ->get();

            $totalRecebido = $transacoes->where('tipo', 'pagamento')->sum('valor');
            $totalDescontos = $transacoes->where('tipo', 'desconto')->sum('valor');

            $checkin = Carbon::parse($reserva->data_checkin);
            $checkout = Carbon::parse($reserva->data_checkout);
            $numDiarias = $checkout->diffInDays($checkin);
            $totalDiarias = $reserva->valor_diaria * $numDiarias;
            $totalProdutos = $transacoes->where('categoria', 'produtos')->sum('valor');
            $totalGeral = $totalDiarias + $totalProdutos;

            $faltaLancar = $totalGeral - $totalRecebido - $totalDescontos;

            if ($faltaLancar > 0.01) { 
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível finalizar a reserva. Ainda há valores pendentes de recebimento.'
                ], 400);
            }

            $statusAntigo = $reserva->situacao;

            $reserva->situacao = 'finalizada';
            $reserva->save();

            LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);

            return response()->json([
                'success' => true,
                'message' => 'Reserva finalizada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelar($id)
    {
        try {
            DB::beginTransaction();
            $reserva = Reserva::findOrFail($id);

            $statusAntigo = $reserva->situacao;

            if (in_array($reserva->situacao, ['finalizada', 'cancelado'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Esta reserva já foi finalizada ou cancelada.'
                ], 400);
            }

            $transacoes = Transacao::where('reserva_id', $reserva->id)->get();
            $transacaoController = app(TransacaoController::class);

            foreach ($transacoes as $transacao) {
                $dataTransacao = Carbon::parse($transacao->data_pagamento);
                $hoje = Carbon::today();

                if ($dataTransacao->isSameDay($hoje)) {
                    $transacaoController->cancelarMovimentacaoCaixa($transacao);
                } else {
                    $transacaoController->criarContasAPagar($transacao);
                }
                $transacao->delete();
            }

            $reserva->situacao = 'cancelado';
            $reserva->save();

            LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada com sucesso! Todas as transações foram processadas.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelarComSupervisor(Request $request, $id)
    {
        $request->validate([
            'senha_supervisor' => ['required', 'string', 'min:4'],
        ]);

        $reserva = Reserva::findOrFail($id);

        if (in_array($reserva->situacao, ['finalizada', 'cancelado'])) {
            return response()->json([
                'success' => false,
                'message' => 'Esta reserva já foi finalizada ou cancelada.'
            ], 400);
        }

        $funcionarios = Funcionario::whereNotNull('senha_supervisor')->get();
        $supervisor = null;
        
        foreach ($funcionarios as $f) {
            $senhaDigitada = (string) $request->input('senha_supervisor');
            $hashBanco = trim((string) $f->senha_supervisor);
            if ($hashBanco === '') continue;

            if (hash_equals($hashBanco, md5($senhaDigitada))) {
                $supervisor = $f;
                break;
            }

            if (str_starts_with($hashBanco, '$2') || str_starts_with($hashBanco, '$argon')) {
                $hashNorm = preg_replace('/^\$2b\$/', '$2y$', $hashBanco);
                if (password_verify($senhaDigitada, $hashNorm)) {
                    $supervisor = $f;
                    break;
                }
            }
        }

        if (!$supervisor) {
            return response()->json(['success' => false, 'message' => 'Senha de supervisor inválida.'], 403);
        }

        try {
            DB::beginTransaction();

            $statusAntigo = $reserva->situacao;
            $dadosAntigos = $reserva->toArray();

            $transacoes = Transacao::where('reserva_id', $reserva->id)->get();
            $transacaoController = app(TransacaoController::class);
            $hoje = Carbon::today();

            foreach ($transacoes as $transacao) {
                $data = $transacao->data_pagamento ? Carbon::parse($transacao->data_pagamento) : null;

                if ($data && $data->isSameDay($hoje)) {
                    $transacaoController->cancelarMovimentacaoCaixa($transacao);
                } else {
                    $transacaoController->criarContasAPagar($transacao);
                }

                $transacao->delete();
            }

            $reserva->situacao = 'cancelado';
            $reserva->save();

            LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);

            LogReserva::create([
                'reserva_id'   => $reserva->id,
                'usuario_id'   => Auth::id(),
                'tipo'         => 'exclusao',
                'descricao'    => "Exclusão da reserva #{$reserva->id}",
                'dados_antigos' => $dadosAntigos,
                'dados_novos'  => [
                    'acao'            => "Exclusão da reserva #{$reserva->id}",
                    'executante_id'   => Auth::id(),
                    'executante_nome' => Auth::user()->name ?? Auth::user()->nome,
                    'supervisor_id'   => $supervisor->id,
                    'supervisor_nome' => $supervisor->nome ?? $supervisor->name,
                    'data_hora'       => now()->toDateTimeString(),
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada com sucesso! Todas as transações foram processadas.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function hospedar($id)
    {
        try {
            $reserva = Reserva::findOrFail($id);
            $statusAntigo = $reserva->situacao;

            $hoje = \Carbon\Carbon::today(); 
            $dataCheckin = \Carbon\Carbon::parse($reserva->data_checkin)->startOfDay();

            if ($hoje->lt($dataCheckin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'O check-in só pode ser realizado a partir de ' . $dataCheckin->format('d/m/Y') . '.'
                ], 400);
            }

            if ($reserva->situacao !== 'reserva') {
                $msg = 'Esta reserva não está pronta para check-in.';
                if ($reserva->situacao === 'hospedado') {
                    $msg = 'O hóspede já realizou o check-in.';
                } elseif (in_array($reserva->situacao, ['finalizada', 'cancelado', 'noshow'])) {
                    $msg = 'Esta reserva já foi finalizada ou cancelada.';
                } elseif ($reserva->situacao === 'pre-reserva') {
                    $msg = 'Confirme a pré-reserva antes de realizar o check-in.';
                }
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            $reserva->situacao = 'hospedado';
            $reserva->hora_checkin = \Carbon\Carbon::now()->format('H:i:s'); 
            $reserva->save();

            if (class_exists('App\Models\LogReserva')) {
                \App\Models\LogReserva::registrarAlteracaoStatus($reserva, \Illuminate\Support\Facades\Auth::id(), $statusAntigo);
            }

            return response()->json([
                'success' => true,
                'message' => 'Check-in realizado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar check-in: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarMudancaSituacao($reservaId)
    {
        $reserva = Reserva::find($reservaId);

        if ($reserva && $reserva->situacao === 'pre-reserva') {
            $temPagamentos = Transacao::where('reserva_id', $reservaId)
                ->where('tipo', 'pagamento')
                ->where('status', true)
                ->exists();

            if ($temPagamentos) {
                $reserva->situacao = 'reserva';
                $reserva->save();
            }
        }
    }

    public function show() {}

    public function marcarNoShowComSupervisor(Request $request, $id)
    {
        $request->validate([
            'senha_supervisor' => ['required', 'string', 'min:1'],
        ]);

        $reserva = Reserva::findOrFail($id);

        if ($reserva->situacao !== 'reserva') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas reservas em situação "reserva" podem ser marcadas como No Show.'
            ], 400);
        }

        $senhaDigitada = (string) $request->input('senha_supervisor');
        $funcionarios = Funcionario::whereNotNull('senha_supervisor')->get();
        $supervisor = null;

        foreach ($funcionarios as $f) {
            $hashBanco = trim((string) $f->senha_supervisor);
            if ($hashBanco === '') continue;

            if (hash_equals($hashBanco, md5($senhaDigitada))) {
                $supervisor = $f;
                break;
            }

            if (preg_match('/^\$(2[aby]|argon)/', $hashBanco)) {
                $hashNorm = preg_replace('/^\$2b\$/', '$2y$', $hashBanco);
                if (password_verify($senhaDigitada, $hashNorm)) {
                    $supervisor = $f;
                    break;
                }
            }
        }

        if (!$supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'Senha de supervisor inválida.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $statusAntigo = $reserva->situacao;
            $dadosAntigos = $reserva->toArray();

            $reserva->situacao = 'noshow';
            $reserva->save();

            LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);

            LogReserva::create([
                'reserva_id'    => $reserva->id,
                'usuario_id'    => Auth::id(),
                'tipo'          => 'status_alterado',
                'descricao'     => "No Show da reserva #{$reserva->id}",
                'dados_antigos' => $dadosAntigos,
                'dados_novos'   => [
                    'acao'            => "No Show da reserva #{$reserva->id}",
                    'executante_id'   => Auth::id(),
                    'executante_nome' => Auth::user()->name ?? Auth::user()->nome,
                    'supervisor_id'   => $supervisor->id,
                    'supervisor_nome' => $supervisor->nome ?? $supervisor->name,
                    'data_hora'       => now()->toDateTimeString(),
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva marcada como No Show com sucesso!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar No Show: ' . $e->getMessage()
            ], 500);
        }
    }

    public function emitirFNRH(Reserva $reserva)
    {
        try {
            $reserva->load('hospede', 'quarto');
            $hospede = $reserva->hospede;
            if (!$hospede) {
                return redirect()->back()->with('error', 'Reserva sem hóspede principal definido.');
            }

            // CORREÇÃO AQUI: Somar adultos + crianças pagantes + crianças não pagantes
            $totalPessoas = $reserva->n_adultos + $reserva->n_criancas + ($reserva->n_criancas_nao_pagantes ?? 0);
            
            // Subtrai 1 porque o hóspede principal não é acompanhante
            $n_acompanhantes = max(0, $totalPessoas - 1);

            $data = [
                'reserva' => $reserva,
                'hospede' => $hospede,
                'quarto' => $reserva->quarto,
                'data_entrada' => Carbon::parse($reserva->data_checkin)->format('d/m/Y'),
                'data_saida' => Carbon::parse($reserva->data_checkout)->format('d/m/Y'),
                'hora_entrada' => $reserva->hora_checkin ? Carbon::parse($reserva->hora_checkin)->format('H:i') : '',
                'hora_saida' => '',
                'n_acompanhantes' => $n_acompanhantes,
            ];

            $pdf = Pdf::loadView('reserva.fnrh', $data);
            $fileName = 'FNRH_' . str_replace(' ', '_', $hospede->nome) . '_' . $reserva->id . '.pdf';

            return $pdf->stream($fileName);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao gerar FNRH: ' . $e->getMessage());
        }
    }

    public function relatorioPorCanal(Request $request)
    {
        $query = Reserva::query();
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $query->whereBetween('created_at', [$dataInicio, $dataFim]);

        $dadosRelatorio = $query
            ->select('canal_venda', DB::raw('count(*) as total'))
            ->groupBy('canal_venda')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                $item->canal_venda = $item->canal_venda ?: 'Não Preenchido';
                return $item;
            })
            ->groupBy('canal_venda')
            ->map(function ($group) {
                return $group->sum('total');
            });

        return view('reserva.relatorio_canal', [
            'dadosRelatorio' => $dadosRelatorio,
            'data_inicio'    => $dataInicio,
            'data_fim'       => $dataFim,
        ]);
    }

    public function enviarVoucherPorEmail(Reserva $reserva)
    {
        try {
            $reserva->load('hospede');

            if (!$reserva->hospede || !$reserva->hospede->email) {
                return redirect()->back()->with('error', 'Hóspede não possui e-mail cadastrado.');
            }
            Mail::to($reserva->hospede->email)->send(new VoucherReservaEmail($reserva));

            return redirect()->back()->with('success', 'Voucher enviado por e-mail com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao enviar voucher por e-mail: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao enviar e-mail. Verifique as configurações.');
        }
    }

    public function cafeDaManha(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::today()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::today()->format('Y-m-d'));

        $reservas = $this->buscarReservasParaCafe($dataInicio, $dataFim);

        return view('reserva.relatorio.cafe', compact('reservas', 'dataInicio', 'dataFim'));
    }

    public function cafeDaManhaPdf(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::today()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::today()->format('Y-m-d'));

        $reservas = $this->buscarReservasParaCafe($dataInicio, $dataFim);

        $pdf = Pdf::loadView('reserva.relatorio.cafe_pdf', [
            'reservas' => $reservas,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim
        ]);

        return $pdf->stream('lista_cafe_manha.pdf');
    }

    private function buscarReservasParaCafe($inicio, $fim)
    {
        $reservas = Reserva::with(['quarto', 'hospede'])
            ->whereIn('situacao', ['reserva', 'hospedado'])
            ->where(function ($query) use ($inicio, $fim) {
                $query->where('data_checkin', '<', $fim)
                      ->where('data_checkout', '>=', $inicio);
            })
            ->orderBy('data_checkin')
            ->get();

        $reservas = $reservas->unique('id');
        
        $todosIdsSecundarios = [];
        foreach ($reservas as $reserva) {
            $secundarios = $reserva->hospedes_secundarios;
            if (is_string($secundarios)) {
                $secundarios = json_decode($secundarios, true);
            }
            if (is_array($secundarios)) {
                $todosIdsSecundarios = array_merge($todosIdsSecundarios, $secundarios);
            }
        }
        
        $todosIdsSecundarios = array_unique($todosIdsSecundarios);
        $mapaHospedes = Hospede::whereIn('id', $todosIdsSecundarios)->get()->keyBy('id');

        foreach ($reservas as $reserva) {
            $listaObjetos = collect([]);
            $secundarios = $reserva->hospedes_secundarios;
            if (is_string($secundarios)) $secundarios = json_decode($secundarios, true);

            if (is_array($secundarios)) {
                foreach ($secundarios as $id) {
                    if (isset($mapaHospedes[$id])) {
                        $listaObjetos->push($mapaHospedes[$id]);
                    }
                }
            }
            $reserva->setRelation('lista_secundarios', $listaObjetos);
        }

        return $reservas;
    }

    private function calcularDadosVendas($dataInicio, $dataFim)
    {
        $vendedores = Funcionario::all();

        $relatorio = $vendedores->map(function ($vendedor) use ($dataInicio, $dataFim) {
            $totalReservas = Reserva::where('vendedor_id', $vendedor->id)
                ->whereBetween('created_at', [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59'])
                ->where('situacao', '!=', 'cancelado') 
                ->sum('valor_total');

            $totalDayUse = 0;
            if (class_exists('App\Models\DayUse')) {
                $totalDayUse = \App\Models\DayUse::where('vendedor_id', $vendedor->id)
                    ->whereBetween('created_at', [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59'])
                    ->sum('total');
            }

            return [
                'nome' => $vendedor->nome,
                'total_reserva' => $totalReservas,
                'total_dayuse' => $totalDayUse,
                'total_geral' => $totalReservas + $totalDayUse
            ];
        });

        return $relatorio->filter(function ($item) {
            return $item['total_geral'] > 0;
        })->sortByDesc('total_geral');
    }

    public function relatorioVendas(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $relatorio = $this->calcularDadosVendas($dataInicio, $dataFim);

        return view('relatorios.vendas', compact('relatorio', 'dataInicio', 'dataFim'));
    }

    public function relatorioVendasPdf(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $relatorio = $this->calcularDadosVendas($dataInicio, $dataFim);

        $pdf = Pdf::loadView('relatorios.vendas_pdf', [
            'relatorio' => $relatorio,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim
        ]);

        return $pdf->stream('relatorio_vendas.pdf');
    }

     public function excluirBloqueioComSupervisor(Request $request, $id)
    {
        $request->validate([
            'senha_supervisor' => ['required', 'string', 'min:1'],
        ]);

        $reserva = Reserva::findOrFail($id);

        if ($reserva->situacao !== 'bloqueado') {
            return response()->json([
                'success' => false,
                'message' => 'Esta função é exclusiva para remover bloqueios de data.'
            ], 400);
        }

        $senhaDigitada = (string) $request->input('senha_supervisor');
        $funcionarios = Funcionario::whereNotNull('senha_supervisor')->get();
        $supervisor = null;

        foreach ($funcionarios as $f) {
            $hashBanco = trim((string) $f->senha_supervisor);
            if ($hashBanco === '') continue;

            if (hash_equals($hashBanco, md5($senhaDigitada))) {
                $supervisor = $f;
                break;
            }

            if (str_starts_with($hashBanco, '$2') || str_starts_with($hashBanco, '$argon')) {
                $hashNorm = preg_replace('/^\$2b\$/', '$2y$', $hashBanco);
                if (password_verify($senhaDigitada, $hashNorm)) {
                    $supervisor = $f;
                    break;
                }
            }
        }

        if (!$supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'Senha de supervisor inválida.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $dadosAntigos = $reserva->toArray();

            LogReserva::create([
                'reserva_id'    => $reserva->id,
                'usuario_id'    => Auth::id(),
                'tipo'          => 'exclusao',
                'descricao'     => "Exclusão de bloqueio ID #{$reserva->id} via Supervisor",
                'dados_antigos' => $dadosAntigos,
                'dados_novos'   => [
                    'acao'            => "Exclusão de bloqueio",
                    'supervisor_id'   => $supervisor->id,
                    'supervisor_nome' => $supervisor->nome,
                    'motivo'          => 'Liberação de data bloqueada'
                ],
            ]);

            $reserva->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bloqueio removido com sucesso!'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir bloqueio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function moverReserva(Request $request)
    {
        try {
            $request->validate([
                'reserva_id' => 'required|exists:reservas,id',
                'novo_quarto_id' => 'required|exists:quartos,id',
                'nova_data_checkin' => 'required|date',
            ]);

            $reserva = Reserva::findOrFail($request->reserva_id);
            
            $inicioOriginal = \Carbon\Carbon::parse($reserva->data_checkin);
            $fimOriginal = \Carbon\Carbon::parse($reserva->data_checkout);
            $diasDuracao = $inicioOriginal->diffInDays($fimOriginal);
            
            if ($diasDuracao < 1) $diasDuracao = 1;

            $novoInicio = \Carbon\Carbon::parse($request->nova_data_checkin);
            $novoFim = $novoInicio->copy()->addDays($diasDuracao);

            $conflito = Reserva::where('quarto_id', $request->novo_quarto_id)
                ->where('id', '!=', $reserva->id) 
                ->where('situacao', '!=', 'cancelado') 
                ->where(function ($query) use ($novoInicio, $novoFim) {
                    $query->where(function ($q) use ($novoInicio, $novoFim) {
                        $q->where('data_checkin', '<=', $novoInicio)
                          ->where('data_checkout', '>', $novoInicio);
                    })
                    ->orWhere(function ($q) use ($novoInicio, $novoFim) {
                        $q->where('data_checkin', '<', $novoFim)
                          ->where('data_checkout', '>=', $novoFim);
                    })
                    ->orWhere(function ($q) use ($novoInicio, $novoFim) {
                        $q->where('data_checkin', '>=', $novoInicio)
                          ->where('data_checkout', '<=', $novoFim);
                    });
                })->exists();

            if ($conflito) {
                return response()->json([
                    'success' => false,
                    'message' => 'O quarto selecionado já está ocupado neste período.'
                ], 422);
            }

            $dadosAntigos = $reserva->toArray();
            $quartoAntigoNome = Quarto::find($reserva->quarto_id)->nome ?? 'N/D';
            $quartoNovoNome = Quarto::find($request->novo_quarto_id)->nome ?? 'N/D';

            $reserva->quarto_id = $request->novo_quarto_id;
            $reserva->data_checkin = $novoInicio->format('Y-m-d');
            $reserva->data_checkout = $novoFim->format('Y-m-d');
            $reserva->save();

            LogReserva::create([
                'reserva_id' => $reserva->id,
                'usuario_id' => Auth::id(),
                'tipo' => 'edicao', 
                'descricao' => "Reserva movida via Mapa (Arrastar e Soltar)",
                'dados_antigos' => $dadosAntigos,
                'dados_novos' => [
                    'acao' => 'Movimentação Mapa',
                    'de_quarto' => $quartoAntigoNome,
                    'para_quarto' => $quartoNovoNome,
                    'de_periodo' => $inicioOriginal->format('d/m/Y') . ' a ' . $fimOriginal->format('d/m/Y'),
                    'para_periodo' => $novoInicio->format('d/m/Y') . ' a ' . $novoFim->format('d/m/Y'),
                    'responsavel' => Auth::user()->name ?? 'Sistema'
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reserva movida com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover reserva: ' . $e->getMessage()
            ], 500);
        }
    }
}