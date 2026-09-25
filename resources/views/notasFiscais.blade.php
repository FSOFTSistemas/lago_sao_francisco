@extends('adminlte::page')

@section('title', 'Notas Fiscais Eletrônicas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark font-weight-bold">Notas Fiscais Eletrônicas (NF-e)</h1>
        <div>
            <a href="{{ route('nota_fiscal.verificar_certificado') }}" class="btn btn-outline-info me-2" title="Testar Certificado Digital">
                <i class="fas fa-certificate me-1"></i> Certificado A1
            </a>
            <a href="{{ route('nota_fiscal.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nova NF-e
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Cards de Resumo Rápido -->
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Notas</span>
                    <span class="info-box-number">{{ $totalEmitidas ?? $notas->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Autorizadas</span>
                    <span class="info-box-number">{{ $totalAutorizadas ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendentes / Assinadas</span>
                    <span class="info-box-number">{{ $totalPendentes ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Rejeitadas</span>
                    <span class="info-box-number">{{ $totalRejeitadas ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros e Pesquisa -->
    <div class="card card-outline card-secondary shadow-sm mb-4">
        <div class="card-body py-2">
            <form action="{{ route('nota-fiscal.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="termo" class="form-control" placeholder="Buscar por número, chave ou cliente..." value="{{ request('termo') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Todos os status</option>
                        <option value="autorizada" {{ request('status') === 'autorizada' ? 'selected' : '' }}>Autorizadas</option>
                        <option value="assinada" {{ request('status') === 'assinada' ? 'selected' : '' }}>Assinadas</option>
                        <option value="gerada" {{ request('status') === 'gerada' ? 'selected' : '' }}>XML Gerado</option>
                        <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendentes</option>
                        <option value="rejeitada" {{ request('status') === 'rejeitada' ? 'selected' : '' }}>Rejeitadas</option>
                        <option value="denegada" {{ request('status') === 'denegada' ? 'selected' : '' }}>Uso Denegado</option>
                        <option value="cancelada" {{ request('status') === 'cancelada' ? 'selected' : '' }}>Canceladas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="data_inicio" class="form-control form-control-sm" title="Data Inicial" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="data_fim" class="form-control form-control-sm" title="Data Final" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Aplicar Filtros">
                        <i class="fas fa-filter"></i>
                    </button>
                    @if (request()->hasAny(['termo', 'status', 'data_inicio', 'data_fim']))
                        <a href="{{ route('nota-fiscal.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpar Filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-file-invoice me-1"></i> Histórico de Emissões
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 100px;">Número</th>
                            <th style="width: 80px;">Série</th>
                            <th style="width: 120px;">Data</th>
                            <th>Cliente / Destinatário</th>
                            <th style="width: 100px;">Itens</th>
                            <th style="width: 140px;">Valor Total</th>
                            <th>Status / Chave</th>
                            <th style="width: 170px;" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notas ?? [] as $nota)
                            <tr>
                                <td class="font-weight-bold text-primary">{{ $nota->numero }}</td>
                                <td>{{ $nota->serie }}</td>
                                <td>{{ \Carbon\Carbon::parse($nota->data)->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $nota->cliente->nome_razao_social ?? 'Cliente não identificado' }}</strong>
                                    @if ($nota->cliente?->cpf_cnpj)
                                        <br><small class="text-muted">{{ $nota->cliente->cpf_cnpj }}</small>
                                    @endif
                                </td>
                                <td>{{ $nota->itens->count() }} item(ns)</td>
                                <td class="font-weight-bold text-success">
                                    R$ {{ number_format($nota->total_nota ?? $nota->total_produtos, 2, ',', '.') }}
                                </td>
                                <td>
                                    @if ($nota->isAutorizada())
                                        <span class="badge badge-success mb-1"><i class="fas fa-check-double me-1"></i> Autorizada</span>
                                        @if ($nota->protocolo)
                                            <br><small class="text-success"><i class="fas fa-file-contract"></i> {{ $nota->protocolo }}</small>
                                        @endif
                                    @elseif ($nota->isRejeitada())
                                        <span class="badge badge-danger mb-1" title="{{ $nota->motivo_status }}"><i class="fas fa-times-circle me-1"></i> Rejeitada</span>
                                        @if ($nota->cstat)
                                            <br><small class="text-danger font-weight-bold">cStat {{ $nota->cstat }}</small>
                                        @endif
                                    @elseif ($nota->isDenegada())
                                        <span class="badge badge-dark mb-1" title="{{ $nota->motivo_status }}"><i class="fas fa-ban me-1"></i> Denegada</span>
                                    @elseif ($nota->isCancelada())
                                        <span class="badge badge-warning mb-1"><i class="fas fa-ban me-1"></i> Cancelada</span>
                                    @elseif ($nota->isAssinada())
                                        <span class="badge badge-primary mb-1"><i class="fas fa-file-signature me-1"></i> Assinada</span>
                                    @elseif ($nota->chave)
                                        <span class="badge badge-secondary mb-1"><i class="fas fa-file-code me-1"></i> XML Gerado</span>
                                    @else
                                        <span class="badge badge-warning mb-1"><i class="fas fa-clock me-1"></i> Pendente de XML</span>
                                    @endif
                                    @if ($nota->chave)
                                        <br><small class="text-monospace text-muted">{{ $nota->chave }}</small>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('nota_fiscal.show', $nota->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Visualizar Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if ($nota->chave && !$nota->isAssinada() && !$nota->isAutorizada())
                                        <form action="{{ route('nota_fiscal.assinar', $nota->id) }}" method="POST" class="d-inline me-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Assinar XML com Certificado A1">
                                                <i class="fas fa-file-signature"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($nota->chave && !$nota->isAutorizada())
                                        <form action="{{ route('nota_fiscal.transmitir', $nota->id) }}" method="POST" class="d-inline me-1" onsubmit="return confirm('Deseja transmitir a NF-e nº {{ $nota->numero }} para autorização na SEFAZ?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Transmitir para SEFAZ">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($nota->chave)
                                        <form action="{{ route('nota_fiscal.consultar_status', $nota->id) }}" method="POST" class="d-inline me-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Consultar Situação na SEFAZ">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($nota->chave)
                                        <a href="{{ route('nota_fiscal.xml', $nota->id) }}" class="btn btn-sm btn-outline-info me-1" title="Baixar XML {{ $nota->isAssinada() ? 'Assinado' : 'Gerado' }}">
                                            <i class="fas fa-file-code"></i> XML
                                        </a>
                                    @else
                                        <form action="{{ route('nota_fiscal.gerar_xml', $nota->id) }}" method="POST" class="d-inline me-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Gerar XML">
                                                <i class="fas fa-cogs"></i> XML
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('nota_fiscal.destroy', $nota->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta nota fiscal?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Nenhuma nota fiscal emitida até o momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if (isset($notas) && method_exists($notas, 'hasPages') && $notas->hasPages())
            <div class="card-footer clearfix">
                {{ $notas->links() }}
            </div>
        @endif
    </div>
@stop
