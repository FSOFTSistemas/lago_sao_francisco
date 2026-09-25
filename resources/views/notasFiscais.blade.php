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
