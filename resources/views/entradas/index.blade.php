@extends('adminlte::page')

@section('title', 'Notas Fiscais de Entrada')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-boxes text-success mr-2"></i>Notas Fiscais de Entrada
            </h1>
            <p class="text-muted mb-0">
                Histórico de compras e entradas de mercadorias no estoque e almoxarifado.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dfe.index') }}" class="btn btn-outline-primary mr-2">
                <i class="fas fa-cloud-download-alt mr-1"></i> Caixa DF-e (SEFAZ)
            </a>
            <a href="{{ route('entradas.create') }}" class="btn btn-success font-weight-bold">
                <i class="fas fa-file-upload mr-1"></i> Nova Entrada (Upload XML)
            </a>
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
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Cards Informativos --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Entradas</span>
                    <span class="info-box-number">{{ number_format($totalNotas, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Volume Total Comprado</span>
                    <span class="info-box-number">R$ {{ number_format($valorTotalNotas, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Entradas no Mês</span>
                    <span class="info-box-number">{{ number_format($notasMesAtual, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total no Mês</span>
                    <span class="info-box-number">R$ {{ number_format($valorMesAtual, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card card-outline card-secondary shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('entradas.index') }}">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2">
                        <input type="text" name="busca" class="form-control form-control-sm" placeholder="Buscar por Número da Nota, Chave ou Fornecedor" value="{{ request('busca') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="d-flex">
                            <input type="date" name="data_inicio" class="form-control form-control-sm mr-1" value="{{ request('data_inicio') }}" title="Data de Entrada Início">
                            <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ request('data_fim') }}" title="Data de Entrada Fim">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-secondary btn-sm mr-1"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="{{ route('entradas.index') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela de Entradas --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0 text-sm">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 100px;">Nº Nota</th>
                        <th style="width: 60px;">Série</th>
                        <th>Fornecedor</th>
                        <th style="width: 130px;">Emissão</th>
                        <th style="width: 130px;">Data Entrada</th>
                        <th style="width: 120px;">Itens</th>
                        <th style="width: 150px;">Valor Total</th>
                        <th style="width: 130px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entradas as $entrada)
                        <tr>
                            <td><strong class="text-primary">{{ $entrada->numero_nota }}</strong></td>
                            <td>{{ $entrada->serie ?: '-' }}</td>
                            <td>
                                <strong>{{ $entrada->fornecedor->razao_social ?? 'Fornecedor não identificado' }}</strong>
                                <small class="d-block text-muted">CNPJ: {{ $entrada->fornecedor->cnpj ?? '-' }}</small>
                            </td>
                            <td>{{ $entrada->data_emissao_formatada }}</td>
                            <td>{{ $entrada->data_entrada_formatada }}</td>
                            <td><span class="badge badge-info">{{ $entrada->itens->count() }} item(ns)</span></td>
                            <td class="font-weight-bold text-dark">{{ $entrada->valor_total_formatado }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('entradas.show', $entrada->id) }}" class="btn btn-info" title="Visualizar Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if ($entrada->xml)
                                        <a href="{{ route('entradas.danfe', $entrada->id) }}" target="_blank" class="btn btn-outline-danger" title="Visualizar / Imprimir DANFE (PDF)">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="{{ route('entradas.download-xml', $entrada->id) }}" class="btn btn-outline-secondary" title="Baixar XML">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-boxes fa-3x mb-2 d-block text-secondary"></i>
                                Nenhuma nota de entrada registrada até o momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($entradas->hasPages())
            <div class="card-footer py-2 d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">
                    Exibindo {{ $entradas->firstItem() }} a {{ $entradas->lastItem() }} de {{ $entradas->total() }} entradas
                </small>
                <div class="mt-1 mt-md-0">
                    {{ $entradas->links() }}
                </div>
            </div>
        @endif
    </div>

    <style>
        .pagination {
            margin-bottom: 0 !important;
        }
        .pagination svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 16px !important;
            max-height: 16px !important;
        }
    </style>
@stop
