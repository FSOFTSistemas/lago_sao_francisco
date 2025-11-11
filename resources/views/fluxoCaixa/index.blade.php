@extends('adminlte::page')

@section('title', 'Fluxo de Caixa')

@section('content_header')
    <h5>Caixa</h5>
@stop

@section('content')
    <div class="caixas-list-wrapper">


        <div class="list-group shadow-sm">
            @forelse ($caixas as $caixa)
                <div class="list-group-item flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-1">
                            <span><strong>#{{ $caixa->id }}</strong> - {{ $caixa->descricao }}</span> |
                            <span>
                                <strong>Status:</strong>
                                @if ($caixa->status === 'aberto')
                                    <span class="badge bg-success text-white">Aberto</span>
                                @else
                                    <span class="badge bg-danger text-white">Fechado</span>
                                @endif
                            </span> |
                            <span><strong>Abertura:</strong>
                                {{ \Carbon\Carbon::parse($caixa->data_abertura)->format('d/m/Y') }}</span> |
                            <span><strong>Fechamento:</strong>
                                {{ \Carbon\Carbon::parse($caixa->data_fechamento)->format('d/m/Y') }}</span>
                        </div>
                        <div class="text-right d-flex flex-column justify-content-center">
                            @if ($caixa->status === 'fechado')
                                <button class="btn btn-success btn-sm mb-1" data-bs-toggle="modal"
                                    data-bs-target="#abrirCaixaModal{{ $caixa->id }}">
                                    🟢 Abrir Caixa
                                </button>
                            @else
                                <button class="btn btn-danger btn-sm mb-1" data-bs-toggle="modal"
                                    data-bs-target="#fecharCaixaModal{{ $caixa->id }}">
                                    🔴 Fechar Caixa
                                </button>
                            @endif
                        </div>
                    </div>


                    @include('caixa.modals._abrircaixa', ['caixa' => $caixa])
                    @include('caixa.modals._fecharcaixa', ['caixa' => $caixa])
                    @include('caixa.modals._show', ['caixa' => $caixa])
                    @include('caixa.modals._edit', ['caixa' => $caixa])
                    @include('caixa.modals._delete', ['caixa' => $caixa])
                </div>
            @empty
                <div class="list-group-item">Nenhum caixa encontrado.</div>
            @endforelse
        </div>

        @include('caixa.modals._create')
    </div>
    <hr>






    <div class="d-flex justify-content-end">
        <button class="btn btn-outline-danger mb-3" data-toggle="modal" data-target="#pdfResumoModal">
            <i class="fas fa-file-pdf"></i> Gerar PDF Resumo
        </button>

    </div>
    <form method="GET" action="{{ route('fluxoCaixa.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-2">
                <label for="data_inicio">Data Início</label>
                <input type="date" class="form-control" name="data_inicio" id="data_inicio"
                    value="{{ request('data_inicio', now()->toDateString()) }}">
            </div>
            <div class="col-md-2">
                <label for="data_fim">Data Fim</label>
                <input type="date" class="form-control" name="data_fim" id="data_fim"
                    value="{{ request('data_fim', now()->toDateString()) }}">
            </div>
            <div class="col-md-2">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="saida" {{ request('tipo') == 'saida' ? 'selected' : '' }}>Saída</option>
                    <option value="abertura" {{ request('tipo') == 'abertura' ? 'selected' : '' }}>Abertura</option>
                    <option value="fechamento" {{ request('tipo') == 'fechamento' ? 'selected' : '' }}>Fechamento</option>
                    <option value="fechamento" {{ request('tipo') == 'cancelamento' ? 'selected' : '' }}>Cancelamento
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="caixa_id">Caixa:</label>
                    <select name="caixa_id" id="caixa_id" class="form-control">
                        <option value="">Todos os Caixas</option>
                        @foreach ($caixas as $caixa)
                            <option value="{{ $caixa->id }}" {{ request('caixa_id') == $caixa->id ? 'selected' : '' }}>
                                {{ $caixa->descricao }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end pb-3">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-end">
            <button id="btnToggleTotais" class="btn btn-outline-secondary mb-3" type="button" data-toggle="collapse"
                data-target="#totaisMovimentosCollapse" aria-expanded="false" aria-controls="totaisMovimentosCollapse">
                <i class="fas fa-filter"></i> Mostrar Totais por Movimento
            </button>
        </div>
        <div class="collapse" id="totaisMovimentosCollapse">
            <br>
            <div class="card card-body">
                <div class="row mb-4">
                    @foreach ($totaisPorMovimento as $item)
                        @php
                            $descricao = $item->movimento->descricao ?? 'outros';

                            // Mapas de cor e ícone
                            $mapa = [
                                'venda-dinheiro' => [
                                    'cor' => 'success',
                                    'icone' => 'fas fa-money-bill-wave',
                                    'nome' => 'Dinheiro',
                                ],
                                'venda-cartão-crédito' => [
                                    'cor' => 'primary',
                                    'icone' => 'fas fa-credit-card',
                                    'nome' => 'Cartão Crédito',
                                ],
                                'venda-cartão-débito' => [
                                    'cor' => 'info',
                                    'icone' => 'fas fa-credit-card',
                                    'nome' => 'Cartão Débito',
                                ],
                                'venda-pix' => ['cor' => 'success', 'icone' => 'fas fa-qrcode', 'nome' => 'Pix'],
                                'venda-transferência-bancária' => [
                                    'cor' => 'warning',
                                    'icone' => 'fas fa-university',
                                    'nome' => 'Transferência',
                                ],
                                'venda-boleto-bancário' => [
                                    'cor' => 'secondary',
                                    'icone' => 'fas fa-barcode',
                                    'nome' => 'Boleto',
                                ],
                                'venda-carteira' => ['cor' => 'dark', 'icone' => 'fas fa-wallet', 'nome' => 'Carteira'],
                                'venda-cheque' => [
                                    'cor' => 'danger',
                                    'icone' => 'fas fa-file-invoice-dollar',
                                    'nome' => 'Cheque',
                                ],
                            ];

                            $dados = $mapa[$descricao] ?? [
                                'cor' => 'secondary',
                                'icone' => 'fas fa-cash-register',
                                'nome' => ucfirst(str_replace('-', ' ', $descricao)),
                            ];
                        @endphp

                        <div class="col-md-3 mb-3">
                            <div class="card border-left-{{ $dados['cor'] }} shadow h-100 py-2">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-{{ $dados['cor'] }} font-weight-bold text-uppercase mb-1">
                                            {{ $dados['nome'] }}
                                        </h6>
                                        <span class="text-dark">R$ {{ number_format($item->total, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="icon text-{{ $dados['cor'] }} ml-3">
                                        <i class="{{ $dados['icone'] }} fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="col-md-3 mb-3">
                        <div class="card border-left-dark shadow h-100 py-2">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-dark font-weight-bold text-uppercase mb-1">Total Geral</h6>
                                    <span class="text-dark">R$ {{ number_format($totalGeral, 2, ',', '.') }}</span>
                                </div>
                                <div class="icon text-dark ml-3">
                                    <i class="fas fa-calculator fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </form>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createFluxoCaixaModal">
            <i class="fas fa-plus"></i> Novo Fluxo de Caixa
        </button>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 3, 'targets' => 3],
            ['responsivePriority' => 5, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 2,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>Ordenação</th>
                <th>Origem</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Tipo</th>
                <th>Movimento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fluxoCaixas as $fluxoCaixa)
                <tr>
                    <td>{{ $fluxoCaixa->created_at }}</td>
                    <td>{{ $fluxoCaixa->caixa->descricao }}</td>
                    <td>{{ $fluxoCaixa->descricao }}</td>
                    <td>R${{ $fluxoCaixa->valor }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($fluxoCaixa->data)->format('d/m/Y') }}</td>
                    <td
                        style="color:
                        @switch($fluxoCaixa->tipo)
                            @case('entrada') green @break
                            @case('saida') red @break
                            @case('abertura') maroon @break
                            @case('fechamento') maroon @break
                            @case('cancelamento') grey @break
                            @default black
                        @endswitch;">
                        {{ ucfirst($fluxoCaixa->tipo) }}
                    </td>
                    <td>{{ $fluxoCaixa->movimento->descricao }}</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showFluxoCaixa{{ $fluxoCaixa->id }}">
                            👁️
                        </button>

                        {{-- <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                            data-target="#editFluxoCaixaModal{{ $fluxoCaixa->id }}">
                            ✏️
                        </button>

                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteFluxoCaixaModal{{ $fluxoCaixa->id }}">
                            🗑️
                        </button> --}}
                    </td>
                </tr>

                @include('fluxoCaixa.modals._show', ['fluxoCaixa' => $fluxoCaixa])
                {{-- @include('fluxoCaixa.modals._edit', ['fluxoCaixa' => $fluxoCaixa])
                @include('fluxoCaixa.modals._delete', ['fluxoCaixa' => $fluxoCaixa]) --}}
            @endforeach
        </tbody>
    @endcomponent

    @include('fluxoCaixa.modals._create')
    <div class="modal fade" id="pdfResumoModal" tabindex="-1" role="dialog" aria-labelledby="pdfResumoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('fluxoCaixa.pdf') }}" method="GET" target="_blank">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pdfResumoModalLabel">Gerar Relatório de Fluxo de Caixa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body row">
                        <div class="form-group col-md-6">
                            <label for="data_inicio">Data Início</label>
                            <input type="date" class="form-control" name="data_inicio" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="data_fim">Data Fim</label>
                            <input type="date" class="form-control" name="data_fim" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="movimento_id">Forma de Pagamento (Movimento)</label>
                            <select class="form-control" name="movimento_id">
                                <option value="">Todos</option>
                                @foreach ($movimento as $item)
                                    <option value="{{ $item->id }}">{{ $item->descricao }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="caixa_id">Caixa</label>
                            <select class="form-control" name="caixa_id">
                                <option value="">Todos</option>
                                @foreach ($caixas as $cx)
                                    <option value="{{ $cx->id }}">{{ $cx->descricao ?? 'Caixa #' . $cx->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if (Auth::user()->hasRole('Master'))
                            <div class="form-group col-md-12">
                                <label for="empresa_id">Empresa</label>
                                <select class="form-control" name="empresa_id">
                                    <option value="">Todas</option>
                                    @foreach ($empresas as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->nome_fantasia }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Gerar PDF
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if (session('sweet_error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Atenção!',
                    text: '{{ session('sweet_error') }}',
                    confirmButtonColor: '#d33'
                });
            });
        </script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btnToggleTotais').click(function() {
                let $btn = $(this);
                let $collapse = $('#totaisMovimentosCollapse');
                if ($collapse.hasClass('show')) {
                    $btn.html('<i class="fas fa-filter"></i> Mostrar Totais por Movimento');
                } else {
                    $btn.html('<i class="fas fa-filter"></i> Esconder Totais por Movimento');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('[id^="fecharCaixaModal"]').on('show.bs.modal', function() {
                const modalId = $(this).attr('id');
                const caixaId = modalId.replace('fecharCaixaModal', '');
                const url = `/caixas/${caixaId}/resumo`;

                $.get(url, function(resumo) {
                    const saldo = resumo.saldo ?? 0;
                    const formas = resumo.formas ?? {};

                    $(`#valor_final${caixaId}`).val(saldo.toFixed(2).replace('.', ','));

                    const lista = $(`#resumoFormas${caixaId}`);
                    lista.empty();

                    if (Object.keys(formas).length === 0) {
                        lista.append(
                            '<li class="list-group-item">Nenhuma movimentação encontrada.</li>');
                    } else {
                        for (const [forma, valor] of Object.entries(formas)) {
                            const nome = forma.charAt(0).toUpperCase() + forma.slice(1);
                            lista.append(
                                `<li class="list-group-item">${nome}: R$ ${valor.toFixed(2).replace('.', ',')}</li>`
                            );
                        }
                    }
                }).fail(function() {
                    $(`#resumoFormas${caixaId}`).html(
                        '<li class="list-group-item text-danger">Erro ao carregar resumo.</li>');
                });
            });
        });
    </script>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

@stop
