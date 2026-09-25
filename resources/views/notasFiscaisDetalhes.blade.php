@extends('adminlte::page')

@section('title', 'Detalhes da NF-e nº ' . ($nota->numero ?? ''))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-file-invoice me-2 text-primary"></i> Nota Fiscal Eletrônica nº {{ $nota->numero }}
            </h1>
            <small class="text-muted">Série: {{ $nota->serie }} | Emissão: {{ \Carbon\Carbon::parse($nota->data)->format('d/m/Y') }}</small>
        </div>
        <div>
            <a href="{{ route('nota_fiscal.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
            @if ($nota->chave && !$nota->isAssinada() && !$nota->isAutorizada())
                <form action="{{ route('nota_fiscal.assinar', $nota->id) }}" method="POST" class="d-inline me-1">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-signature me-1"></i> Assinar Digitalmente (A1)
                    </button>
                </form>
            @endif
            @if ($nota->chave && !$nota->isAutorizada())
                <form action="{{ route('nota_fiscal.transmitir', $nota->id) }}" method="POST" class="d-inline me-1" onsubmit="return confirm('Deseja transmitir a NF-e nº {{ $nota->numero }} para autorização na SEFAZ?');">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Transmitir para SEFAZ
                    </button>
                </form>
            @endif
            @if ($nota->chave)
                <form action="{{ route('nota_fiscal.consultar_status', $nota->id) }}" method="POST" class="d-inline me-1">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary" title="Consultar Situação da NF-e na SEFAZ">
                        <i class="fas fa-sync-alt me-1"></i> Consultar Status SEFAZ
                    </button>
                </form>
            @endif
            @if ($nota->chave)
                <a href="{{ route('nota_fiscal.xml', $nota->id) }}" class="btn btn-info">
                    <i class="fas fa-download me-1"></i> Baixar XML {{ $nota->isAssinada() ? 'Assinado' : '' }}
                </a>
            @else
                <form action="{{ route('nota_fiscal.gerar_xml', $nota->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-cogs me-1"></i> Gerar XML
                    </button>
                </form>
            @endif
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

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Card de Identificação e Chave de Acesso -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header bg-light">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-key me-1 text-primary"></i> Chave de Acesso & Identificação
            </h3>
            <div class="card-tools">
                @if ($nota->isAutorizada())
                    <span class="badge badge-success px-3 py-2 fs-6"><i class="fas fa-check-double me-1"></i> Autorizada</span>
                @elseif ($nota->isRejeitada())
                    <span class="badge badge-danger px-3 py-2 fs-6"><i class="fas fa-times-circle me-1"></i> Rejeitada</span>
                @elseif ($nota->isDenegada())
                    <span class="badge badge-dark px-3 py-2 fs-6"><i class="fas fa-ban me-1"></i> Uso Denegado</span>
                @elseif ($nota->isCancelada())
                    <span class="badge badge-warning px-3 py-2 fs-6"><i class="fas fa-ban me-1"></i> Cancelada</span>
                @elseif ($nota->isAssinada())
                    <span class="badge badge-primary px-3 py-2 fs-6"><i class="fas fa-file-signature me-1"></i> Assinada Digitalmente</span>
                @elseif ($nota->chave)
                    <span class="badge badge-secondary px-3 py-2 fs-6"><i class="fas fa-file-code me-1"></i> XML Gerado</span>
                @else
                    <span class="badge badge-warning px-3 py-2 fs-6"><i class="fas fa-clock me-1"></i> Pendente de XML</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <label class="text-muted mb-0 small">Chave de Acesso (44 dígitos):</label>
                    <div class="h5 font-monospace font-weight-bold text-dark mb-0">
                        {{ $nota->chave ?: 'Chave de acesso não gerada para esta nota fiscal.' }}
                    </div>
                </div>
                <div class="col-md-3 text-md-end mt-2 mt-md-0">
                    <label class="text-muted mb-0 small">Tipo de Operação:</label>
                    <div class="font-weight-bold">
                        {{ ($nota->tp_nota ?? 1) == 1 ? '1 - Saída' : '0 - Entrada' }}
                    </div>
                </div>
            </div>
            @if ($nota->protocolo || $nota->cstat || $nota->motivo_status || $nota->data_autorizacao)
                <hr class="my-3">
                <div class="row pt-1">
                    @if ($nota->protocolo)
                        <div class="col-md-3">
                            <small class="text-muted d-block">Protocolo de Autorização:</small>
                            <span class="font-weight-bold text-success"><i class="fas fa-file-contract me-1"></i> {{ $nota->protocolo }}</span>
                        </div>
                    @endif
                    @if ($nota->cstat)
                        <div class="col-md-2">
                            <small class="text-muted d-block">cStat SEFAZ:</small>
                            <span class="font-weight-bold">{{ $nota->cstat }}</span>
                        </div>
                    @endif
                    @if ($nota->data_autorizacao)
                        <div class="col-md-3">
                            <small class="text-muted d-block">Data de Autorização:</small>
                            <span class="font-weight-bold">{{ $nota->data_autorizacao->format('d/m/Y H:i:s') }}</span>
                        </div>
                    @endif
                    @if ($nota->motivo_status)
                        <div class="col-md-{{ $nota->protocolo ? '4' : '7' }}">
                            <small class="text-muted d-block">Mensagem da SEFAZ:</small>
                            <span class="{{ $nota->isAutorizada() ? 'text-success' : 'text-danger font-weight-bold' }}">{{ $nota->motivo_status }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Dados do Emitente e Destinatário -->
    <div class="row">
        <!-- Emitente -->
        <div class="col-md-6 mb-4">
            <div class="card card-outline card-secondary h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-building me-1 text-secondary"></i> Emitente
                    </h5>
                </div>
                <div class="card-body">
                    <h5 class="font-weight-bold text-primary">{{ $nota->empresa->razao_social ?? 'Hotel Lago Ltda' }}</h5>
                    <p class="text-muted mb-2">{{ $nota->empresa->nome_fantasia ?? '' }}</p>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted ps-0" style="width: 100px;">CNPJ:</td>
                            <td class="font-weight-bold">{{ $nota->empresa->cnpj ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Inscr. Estadual:</td>
                            <td class="font-weight-bold">{{ $nota->empresa->inscricao_estadual ?? '-' }}</td>
                        </tr>
                        @if ($nota->empresa?->endereco)
                            <tr>
                                <td class="text-muted ps-0">Endereço:</td>
                                <td>
                                    {{ $nota->empresa->endereco->logradouro }}, {{ $nota->empresa->endereco->numero }}
                                    @if ($nota->empresa->endereco->complemento)
                                        - {{ $nota->empresa->endereco->complemento }}
                                    @endif
                                    <br>
                                    {{ $nota->empresa->endereco->bairro }} - {{ $nota->empresa->endereco->cidade }}/{{ $nota->empresa->endereco->uf }}
                                    <br>CEP: {{ $nota->empresa->endereco->cep }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Destinatário -->
        <div class="col-md-6 mb-4">
            <div class="card card-outline card-secondary h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-user me-1 text-secondary"></i> Destinatário / Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <h5 class="font-weight-bold text-dark">{{ $nota->cliente->nome_razao_social ?? 'Não informado' }}</h5>
                    <p class="text-muted mb-2">{{ $nota->cliente->apelido_nome_fantasia ?? '' }}</p>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted ps-0" style="width: 100px;">CPF/CNPJ:</td>
                            <td class="font-weight-bold">{{ $nota->cliente->cpf_cnpj ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">RG/IE:</td>
                            <td class="font-weight-bold">{{ $nota->cliente->rg_ie ?? 'ISENTO' }}</td>
                        </tr>
                        @if ($nota->cliente?->endereco)
                            <tr>
                                <td class="text-muted ps-0">Endereço:</td>
                                <td>
                                    {{ $nota->cliente->endereco->logradouro }}, {{ $nota->cliente->endereco->numero }}
                                    @if ($nota->cliente->endereco->complemento)
                                        - {{ $nota->cliente->endereco->complemento }}
                                    @endif
                                    <br>
                                    {{ $nota->cliente->endereco->bairro }} - {{ $nota->cliente->endereco->cidade }}/{{ $nota->cliente->endereco->uf }}
                                    <br>CEP: {{ $nota->cliente->endereco->cep }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Itens da Nota Fiscal -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header bg-light">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-boxes me-1 text-primary"></i> Itens da Nota Fiscal ({{ $nota->itens->count() }})
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Descrição do Produto / Serviço</th>
                            <th style="width: 100px;">NCM</th>
                            <th style="width: 80px;">CFOP</th>
                            <th style="width: 80px;">CST/CSOSN</th>
                            <th style="width: 80px;" class="text-center">Qtd</th>
                            <th style="width: 120px;" class="text-end">V. Unitário</th>
                            <th style="width: 100px;" class="text-end">Desconto</th>
                            <th style="width: 120px;" class="text-end">Subtotal</th>
                            <th style="width: 120px;" class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($nota->itens as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="font-weight-bold">
                                    {{ $item->produto->descricao ?? 'Produto ID #' . $item->produto_id }}
                                </td>
                                <td>{{ $item->produto->ncm ?? '21069090' }}</td>
                                <td>{{ $item->cfop->cfop ?? '5102' }}</td>
                                <td>{{ $item->csosm ?? $item->cst ?? '102' }}</td>
                                <td class="text-center font-weight-bold">{{ $item->quantidade }}</td>
                                <td class="text-end">R$ {{ number_format($item->v_unitario, 2, ',', '.') }}</td>
                                <td class="text-end text-danger">
                                    {{ $item->desconto > 0 ? 'R$ ' . number_format($item->desconto, 2, ',', '.') : '-' }}
                                </td>
                                <td class="text-end">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                <td class="text-end font-weight-bold text-success">
                                    R$ {{ number_format($item->total, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-3 text-muted">Nenhum item cadastrado nesta nota.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Totais e Informações Adicionais -->
    <div class="row">
        <!-- Informações Complementares -->
        <div class="col-md-7 mb-4">
            <div class="card card-outline card-secondary h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-info-circle me-1 text-secondary"></i> Informações Adicionais
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Informações Complementares / Fisco:</label>
                        <p class="border rounded p-2 bg-light font-monospace small mb-0">
                            {{ $nota->info_complementares ?: 'Nenhuma informação complementar informada.' }}
                        </p>
                    </div>

                    @if ($nota->nfe_referenciavel)
                        <div class="mb-2">
                            <label class="text-muted small mb-1">Chave da NF-e Referenciada:</label>
                            <p class="font-monospace text-primary font-weight-bold mb-0">
                                {{ $nota->nfe_referenciavel }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Totais da Nota -->
        <div class="col-md-5 mb-4">
            <div class="card card-outline card-success h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title font-weight-bold mb-0 text-success">
                        <i class="fas fa-calculator me-1"></i> Totais da Nota Fiscal
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Total dos Produtos:</td>
                            <td class="text-end font-weight-bold">
                                R$ {{ number_format($nota->total_produtos, 2, ',', '.') }}
                            </td>
                        </tr>
                        @if ($nota->total_desconto > 0)
                            <tr>
                                <td class="text-muted">Desconto Total:</td>
                                <td class="text-end text-danger font-weight-bold">
                                    - R$ {{ number_format($nota->total_desconto, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                        @if ($nota->outras_despesas > 0)
                            <tr>
                                <td class="text-muted">Outras Despesas / Acréscimos:</td>
                                <td class="text-end font-weight-bold">
                                    + R$ {{ number_format($nota->outras_despesas, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Base de Cálculo ICMS:</td>
                            <td class="text-end">
                                R$ {{ number_format($nota->base_ICMS, 2, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Valor Total ICMS:</td>
                            <td class="text-end">
                                R$ {{ number_format($nota->vICMS, 2, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="border-top pt-2">
                            <td class="h5 font-weight-bold text-dark pt-3">Valor Total da Nota:</td>
                            <td class="h4 font-weight-bold text-success text-end pt-3">
                                R$ {{ number_format($nota->total_nota ?: $nota->total_produtos, 2, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
