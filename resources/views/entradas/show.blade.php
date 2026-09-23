@extends('adminlte::page')

@section('title', 'Detalhes da Nota de Entrada Nº ' . $entrada->numero_nota)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-file-invoice-dollar text-success mr-2"></i>Nota de Entrada Nº {{ $entrada->numero_nota }}
            </h1>
            <p class="text-muted mb-0">
                Registrada no sistema em {{ $entrada->data_entrada_formatada }} pelo usuário {{ $entrada->usuario->name ?? 'Sistema' }}.
            </p>
        </div>
        <div>
            <a href="{{ route('entradas.index') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Voltar à Lista
            </a>
            @if ($entrada->xml)
                <a href="{{ route('entradas.danfe', $entrada->id) }}" target="_blank" class="btn btn-outline-danger mr-2">
                    <i class="fas fa-file-pdf mr-1"></i> Imprimir DANFE
                </a>
                <a href="{{ route('entradas.download-xml', $entrada->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download mr-1"></i> Baixar XML
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- 1. Cabeçalho --}}
    <div class="card card-outline card-success shadow-sm mb-3">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold text-sm">
                <i class="fas fa-info-circle mr-1"></i> Informações Principais
            </h3>
            <div class="card-tools">
                <span class="badge badge-success">Entrada Confirmada</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="text-xs text-muted mb-0">Número / Série</label>
                    <div class="font-weight-bold text-lg text-primary">
                        Nº {{ $entrada->numero_nota }} @if($entrada->serie) (Série {{ $entrada->serie }}) @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-xs text-muted mb-0">Fornecedor</label>
                    <div class="font-weight-bold">{{ $entrada->fornecedor->razao_social ?? '-' }}</div>
                    <small class="text-muted">CNPJ: {{ $entrada->fornecedor->cnpj ?? '-' }}</small>
                </div>
                <div class="col-md-3">
                    <label class="text-xs text-muted mb-0">Datas</label>
                    <div>Emissão: {{ $entrada->data_emissao_formatada }}</div>
                    <small class="text-muted">Entrada: {{ $entrada->data_entrada_formatada }}</small>
                </div>
                <div class="col-md-3 text-right">
                    <label class="text-xs text-muted mb-0">Valor Total</label>
                    <div class="font-weight-bold text-xl text-success">{{ $entrada->valor_total_formatado }}</div>
                    <small class="text-muted">Produtos: R$ {{ number_format($entrada->valor_produtos, 2, ',', '.') }} | Frete: R$ {{ number_format($entrada->valor_frete, 2, ',', '.') }}</small>
                </div>
            </div>

            <hr class="my-2">

            <div class="row text-sm">
                <div class="col-md-8">
                    <label class="text-xs text-muted mb-0">Chave de Acesso</label>
                    <div class="text-monospace small">{{ $entrada->chave_formatada }}</div>
                </div>
                <div class="col-md-4">
                    <label class="text-xs text-muted mb-0">Natureza da Operação</label>
                    <div>{{ $entrada->natureza_operacao ?: 'Não informada' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Itens Processados --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold text-sm">
                <i class="fas fa-boxes mr-1"></i> Itens da Nota e Destino no Sistema ({{ $entrada->itens->count() }} itens)
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped mb-0 text-sm">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Descrição na Nota</th>
                        <th>Destino</th>
                        <th>Vínculo no Sistema</th>
                        <th style="width: 100px;" class="text-center">Qtd</th>
                        <th style="width: 120px;" class="text-right">Unitário</th>
                        <th style="width: 130px;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entrada->itens as $item)
                        <tr>
                            <td class="text-center font-weight-bold">{{ $item->numero_item }}</td>
                            <td>
                                <strong>{{ $item->descricao }}</strong>
                                <small class="d-block text-muted">
                                    Cód: {{ $item->codigo_fornecedor }} 
                                    @if($item->codigo_barras) | EAN: {{ $item->codigo_barras }} @endif
                                    | NCM: {{ $item->ncm }}
                                </small>
                            </td>
                            <td>
                                <span class="badge {{ $item->destino === 'almoxarifado' ? 'badge-info' : 'badge-primary' }}">
                                    {{ $item->destino_formatado }}
                                </span>
                            </td>
                            <td>
                                @if ($item->destino === 'almoxarifado')
                                    <strong>{{ $item->almoxarifadoItem->nome ?? 'Item de Almoxarifado' }}</strong>
                                @else
                                    <strong>{{ $item->produto->descricao ?? 'Produto de Venda' }}</strong>
                                @endif
                            </td>
                            <td class="text-center font-weight-bold">{{ $item->quantidade_formatada }} {{ $item->unidade }}</td>
                            <td class="text-right">{{ $item->valor_unitario_formatado }}</td>
                            <td class="text-right font-weight-bold text-dark">{{ $item->valor_total_formatado }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
