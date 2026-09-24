@extends('adminlte::page')

@section('title', 'Busca de Notas na Receita (DF-e)')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-cloud-download-alt text-primary mr-2"></i>Busca de Notas na Receita (DF-e)
            </h1>
            <p class="text-muted mb-0">
                Consulta automática de NF-e emitidas contra o CNPJ da empresa, manifestação do destinatário e download de XMLs.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('entradas.create') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-file-upload mr-1"></i> Importar XML Manual
            </a>

            <form action="{{ route('dfe.sincronizar') }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja consultar a SEFAZ agora? O processo pode levar alguns segundos.');">
                @csrf
                <button type="submit" class="btn btn-primary font-weight-bold">
                    <i class="fas fa-sync-alt mr-1"></i> Consultar SEFAZ Agora
                </button>
            </form>
        </div>
    </div>
@stop

@section('content')
    {{-- Mensagens de Feedback --}}
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
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle mr-1"></i> {{ session('info') }}
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
            <a href="{{ route('dfe.index', ['com_xml' => 'todos']) }}" class="text-decoration-none text-dark" title="Clique para ver todas as notas">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-info"><i class="fas fa-inbox"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Localizadas</span>
                        <span class="info-box-number">{{ number_format($totalGeral, 0, ',', '.') }}</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('dfe.index', ['manifestacao' => 'sem_manifestacao', 'com_xml' => 'todos']) }}" class="text-decoration-none text-dark" title="Clique para filtrar notas sem manifestação">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sem Manifestação</span>
                        <span class="info-box-number">{{ number_format($totalSemManifestacao, 0, ',', '.') }}</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('dfe.index', ['com_xml' => '1']) }}" class="text-decoration-none text-dark" title="Clique para filtrar apenas com XML completo">
                <div class="info-box shadow-sm {{ ($comXml ?? request('com_xml', '1')) === '1' ? 'border border-success' : '' }}">
                    <span class="info-box-icon bg-success"><i class="fas fa-file-code"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Com XML Pronto</span>
                        <span class="info-box-number">{{ number_format($totalComXml, 0, ',', '.') }}</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('dfe.index', ['com_xml' => '1', 'importado' => '0']) }}" class="text-decoration-none text-dark" title="Clique para filtrar prontas para entrada no estoque">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-primary"><i class="fas fa-truck-loading"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Prontas p/ Entrada</span>
                        <span class="info-box-number">{{ number_format($totalPendentesImportacao, 0, ',', '.') }}</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Barra de Status da SEFAZ --}}
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center text-sm">
                <div class="col-md-3">
                    <strong>Empresa Ativa:</strong> {{ $empresa->razao_social }} ({{ $empresa->cnpj }})
                </div>
                <div class="col-md-3">
                    <strong>Último NSU Sincronizado:</strong> <span class="badge badge-light border">{{ $preferencia?->ult_nsu ?? '0' }}</span> / Max: {{ $preferencia?->max_nsu ?? '0' }}
                </div>
                <div class="col-md-3">
                    <strong>Última Consulta SEFAZ:</strong> {{ $preferencia?->data_ultima_consulta_dfe ? $preferencia->data_ultima_consulta_dfe->format('d/m/Y H:i:s') : 'Nunca' }}
                </div>
                <div class="col-md-3 text-right">
                    <strong>Ambiente:</strong>
                    <span class="badge {{ ($preferencia?->ambiente_dfe ?? 1) == 1 ? 'badge-success' : 'badge-warning' }}">
                        {{ ($preferencia?->ambiente_dfe ?? 1) == 1 ? 'Produção' : 'Homologação' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros de Pesquisa --}}
    <div class="card card-outline card-secondary shadow-sm mb-3">
        <div class="card-header py-2">
            <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-filter mr-1"></i> Filtros de Busca</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dfe.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="text-xs">Busca Geral</label>
                        <input type="text" name="busca" class="form-control form-control-sm" placeholder="Nº da Nota, Série, Fornecedor, CNPJ ou NSU" value="{{ request('busca') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="text-xs">Manifestação</label>
                        <select name="manifestacao" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <option value="sem_manifestacao" {{ request('manifestacao') === 'sem_manifestacao' ? 'selected' : '' }}>Sem Manifestação</option>
                            <option value="ciencia" {{ request('manifestacao') === 'ciencia' ? 'selected' : '' }}>Ciência da Emissão</option>
                            <option value="confirmada" {{ request('manifestacao') === 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                            <option value="desconhecida" {{ request('manifestacao') === 'desconhecida' ? 'selected' : '' }}>Desconhecida</option>
                            <option value="nao_realizada" {{ request('manifestacao') === 'nao_realizada' ? 'selected' : '' }}>Não Realizada</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="text-xs">Status do XML</label>
                        <select name="com_xml" class="form-control form-control-sm">
                            <option value="1" {{ ($comXml ?? request('com_xml', '1')) === '1' ? 'selected' : '' }}>Apenas XML Completo</option>
                            <option value="0" {{ ($comXml ?? request('com_xml')) === '0' ? 'selected' : '' }}>Apenas Resumo</option>
                            <option value="todos" {{ in_array(($comXml ?? request('com_xml')), ['todos', 'all']) ? 'selected' : '' }}>Todas as Notas</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="text-xs">Entrada no Estoque</label>
                        <select name="importado" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <option value="0" {{ request('importado') === '0' ? 'selected' : '' }}>Não Importada</option>
                            <option value="1" {{ request('importado') === '1' ? 'selected' : '' }}>Já Importada</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="text-xs">Período de Emissão</label>
                        <div class="d-flex">
                            <input type="date" name="data_inicio" class="form-control form-control-sm mr-1" value="{{ request('data_inicio') }}">
                            <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ request('data_fim') }}">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    <a href="{{ route('dfe.index') }}" class="btn btn-outline-secondary btn-sm mr-2">Limpar</a>
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search mr-1"></i> Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Banner de Status do Filtro XML --}}
    @if (($comXml ?? request('com_xml', '1')) === '1')
        <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-2 shadow-sm">
            <span class="text-sm text-dark font-weight-500">
                <i class="fas fa-check-circle text-success mr-1"></i>
                Exibindo apenas notas com <strong>XML Completo</strong> ({{ $documentos->total() }} encontradas).
            </span>
            <a href="{{ route('dfe.index', array_merge(request()->except('page'), ['com_xml' => 'todos'])) }}" class="btn btn-outline-secondary btn-xs font-weight-bold">
                <i class="fas fa-eye mr-1"></i> Ver todas as notas (incluindo resumos)
            </a>
        </div>
    @elseif (($comXml ?? request('com_xml')) === '0')
        <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-2 shadow-sm">
            <span class="text-sm text-dark font-weight-500">
                <i class="fas fa-info-circle text-warning mr-1"></i>
                Exibindo apenas <strong>Resumos de NF-e</strong> ({{ $documentos->total() }} encontradas).
            </span>
            <a href="{{ route('dfe.index', array_merge(request()->except('page'), ['com_xml' => '1'])) }}" class="btn btn-outline-success btn-xs font-weight-bold">
                <i class="fas fa-file-code mr-1"></i> Ver notas com XML Completo
            </a>
        </div>
    @endif

    {{-- Tabela de Documentos --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0 text-sm">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 130px;">Nº Nota / Série</th>
                        <th style="width: 80px;">NSU</th>
                        <th style="width: 120px;">Emissão</th>
                        <th>Fornecedor / Emitente</th>
                        <th style="width: 130px;">Valor Total</th>
                        <th style="width: 90px;">SEFAZ</th>
                        <th style="width: 150px;">Manifestação</th>
                        <th style="width: 100px;">XML</th>
                        <th style="width: 110px;">Entrada</th>
                        <th style="width: 210px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documentos as $doc)
                        <tr>
                            <td>
                                <strong class="text-primary d-block font-weight-bold">
                                    {{ $doc->numero_nota ? 'Nº ' . $doc->numero_nota : 'S/N' }}
                                </strong>
                                <small class="text-muted">Série: {{ $doc->serie ?: '1' }}</small>
                            </td>
                            <td><span class="badge badge-light border">{{ $doc->nsu }}</span></td>
                            <td>{{ $doc->data_emissao ? $doc->data_emissao->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <strong class="d-block text-truncate" style="max-width: 320px;" title="{{ $doc->nome_emitente }}">
                                    {{ $doc->nome_emitente ?: 'Nome não informado' }}
                                </strong>
                                <small class="text-muted">CNPJ/CPF: {{ $doc->cnpj_emitente }}</small>
                            </td>
                            <td class="font-weight-bold text-dark">{{ $doc->valor_total_formatado }}</td>
                            <td>
                                @if ($doc->situacao_nfe == 1)
                                    <span class="badge badge-success">Autorizada</span>
                                @elseif ($doc->situacao_nfe == 2)
                                    <span class="badge badge-danger">Cancelada</span>
                                @elseif ($doc->situacao_nfe == 3)
                                    <span class="badge badge-secondary">Denegada</span>
                                @else
                                    <span class="badge badge-info">{{ $doc->situacao_nfe_formatada }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $doc->badge_manifestacao }}">
                                    {{ $doc->situacao_manifestacao_formatada }}
                                </span>
                            </td>
                            <td>
                                @if ($doc->temXmlCompleto())
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Completo</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-exclamation mr-1"></i> Resumo</span>
                                @endif
                            </td>
                            <td>
                                @if ($doc->importado_entrada)
                                    @if ($doc->entrada_id)
                                        <a href="{{ route('entradas.show', $doc->entrada_id) }}" class="badge badge-success text-white" title="Ver detalhes da entrada no sistema">
                                            <i class="fas fa-check-double mr-1"></i> Entrada OK
                                        </a>
                                    @else
                                        <span class="badge badge-success"><i class="fas fa-check-double mr-1"></i> Entrada OK</span>
                                    @endif
                                @else
                                    <span class="badge badge-light border">Pendente</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    {{-- Botão Dar Entrada (apenas se tiver XML completo) --}}
                                    @if ($doc->temXmlCompleto() && !$doc->importado_entrada)
                                        <a href="{{ route('entradas.conferir', $doc->id) }}" class="btn btn-success" title="Conferir e Dar Entrada no Estoque/Almoxarifado">
                                            <i class="fas fa-boxes"></i> Dar Entrada
                                        </a>
                                    @endif

                                    {{-- Botão Manifestar --}}
                                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#modalManifestar{{ $doc->id }}" title="Manifestar Destinatário">
                                        <i class="fas fa-comment-dots"></i>
                                    </button>

                                    {{-- Botão Histórico de Eventos --}}
                                    <button type="button" class="btn btn-outline-info btn-historico-eventos" data-chave="{{ $doc->chave }}" title="Ver Histórico de Eventos / Trilha de Auditoria">
                                        <i class="fas fa-history"></i>
                                    </button>

                                    {{-- Visualizar DANFE (PDF) e Download XML se disponível --}}
                                    @if ($doc->temXmlCompleto())
                                        <a href="{{ route('dfe.danfe', $doc->id) }}" target="_blank" class="btn btn-outline-danger" title="Visualizar / Imprimir DANFE (PDF)">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="{{ route('dfe.download-xml', $doc->chave) }}" class="btn btn-outline-secondary" title="Baixar XML">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @else
                                        {{-- Forçar busca de XML por chave --}}
                                        <form action="{{ route('dfe.consultar-chave', $doc->chave) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-info" title="Tentar obter XML da SEFAZ agora">
                                                <i class="fas fa-cloud-download-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                {{-- Modal de Manifestação individual --}}
                                <div class="modal fade text-left" id="modalManifestar{{ $doc->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('dfe.manifestar', $doc->chave) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title font-weight-bold">
                                                        <i class="fas fa-stamp mr-1"></i> Manifestação do Destinatário
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="text-xs text-muted mb-0">Fornecedor</label>
                                                        <div class="font-weight-bold">{{ $doc->nome_emitente }} ({{ $doc->cnpj_emitente }})</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="text-xs text-muted mb-0">Valor Total / Emissão</label>
                                                        <div>{{ $doc->valor_total_formatado }} em {{ $doc->data_emissao ? $doc->data_emissao->format('d/m/Y H:i') : '-' }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="text-xs text-muted mb-0">Chave de Acesso</label>
                                                        <div class="text-monospace small">{{ $doc->chave }}</div>
                                                    </div>
                                                    <hr>
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">Selecione o Evento de Manifestação:</label>
                                                        <div class="custom-control custom-radio mb-2">
                                                            <input type="radio" id="evt_ciencia_{{ $doc->id }}" name="evento" value="210210" class="custom-control-input" {{ $doc->situacao_manifestacao == 'sem_manifestacao' ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="evt_ciencia_{{ $doc->id }}">
                                                                <strong>Ciência da Emissão (210210)</strong>
                                                                <small class="d-block text-muted">Declara que tomou conhecimento da nota. Destrava o download do XML completo na SEFAZ.</small>
                                                            </label>
                                                        </div>
                                                        <div class="custom-control custom-radio mb-2">
                                                            <input type="radio" id="evt_confirmada_{{ $doc->id }}" name="evento" value="210200" class="custom-control-input" {{ $doc->situacao_manifestacao == 'ciencia' ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="evt_confirmada_{{ $doc->id }}">
                                                                <strong>Confirmação da Operação (210200)</strong>
                                                                <small class="d-block text-muted">Confirma que as mercadorias foram conferidas e recebidas com sucesso.</small>
                                                            </label>
                                                        </div>
                                                        <div class="custom-control custom-radio mb-2">
                                                            <input type="radio" id="evt_desconhecida_{{ $doc->id }}" name="evento" value="210220" class="custom-control-input">
                                                            <label class="custom-control-label text-warning" for="evt_desconhecida_{{ $doc->id }}">
                                                                <strong>Desconhecimento da Operação (210220)</strong>
                                                                <small class="d-block text-muted">Informa que a empresa desconhece a compra ou não solicitou a mercadoria.</small>
                                                            </label>
                                                        </div>
                                                        <div class="custom-control custom-radio mb-2">
                                                            <input type="radio" id="evt_nao_realizada_{{ $doc->id }}" name="evento" value="210240" class="custom-control-input">
                                                            <label class="custom-control-label text-danger" for="evt_nao_realizada_{{ $doc->id }}">
                                                                <strong>Operação Não Realizada (210240)</strong>
                                                                <small class="d-block text-muted">A operação foi cancelada, mercadoria devolvida ou recusada na entrega.</small>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="form-group" id="group_justificativa_{{ $doc->id }}">
                                                        <label class="text-xs">Justificativa (obrigatória para Operação Não Realizada - mín. 15 caracteres):</label>
                                                        <textarea name="justificativa" class="form-control form-control-sm" rows="2" placeholder="Informe a justificativa caso marque Operação Não Realizada"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Fechar</button>
                                                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
                                                        <i class="fas fa-paper-plane mr-1"></i> Transmitir para a SEFAZ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-2 d-block text-secondary"></i>
                                Nenhum documento fiscal localizado. Clique em <strong>"Consultar SEFAZ Agora"</strong> para sincronizar as notas emitidas contra o CNPJ da empresa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($documentos->hasPages())
            <div class="card-footer py-2 d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">
                    Exibindo {{ $documentos->firstItem() }} a {{ $documentos->lastItem() }} de {{ $documentos->total() }} documentos
                </small>
                <div class="mt-1 mt-md-0">
                    {{ $documentos->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Universal de Histórico de Eventos --}}
    <div class="modal fade" id="modalHistoricoEventos" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-history mr-1"></i> Histórico de Eventos e Auditoria SEFAZ
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="historicoLoading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-info mb-2"></i>
                        <div class="text-muted text-sm">Carregando histórico da SEFAZ...</div>
                    </div>

                    <div id="historicoConteudo" style="display: none;">
                        <div class="card card-outline card-light shadow-none border mb-3">
                            <div class="card-body p-2 text-sm bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Documento:</strong> <span id="histDocNumero">-</span> | Série <span id="histDocSerie">-</span>
                                    </div>
                                    <div class="col-md-6 text-md-right">
                                        <strong>Valor:</strong> <span id="histDocTotal" class="font-weight-bold text-dark">-</span>
                                    </div>
                                    <div class="col-12 mt-1">
                                        <strong>Fornecedor:</strong> <span id="histDocEmitente">-</span>
                                    </div>
                                    <div class="col-12 mt-1">
                                        <small class="text-muted">Chave: <span id="histDocChave" class="text-monospace"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="font-weight-bold text-sm mb-2"><i class="fas fa-stream mr-1"></i> Trilha de Eventos Registrados:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-sm mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 160px;">Data / Hora</th>
                                        <th>Evento</th>
                                        <th style="width: 160px;">Protocolo SEFAZ</th>
                                        <th style="width: 150px;">Registrado Por</th>
                                    </tr>
                                </thead>
                                <tbody id="tabelaEventosBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
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

@section('js')
<script>
    $(document).ready(function() {
        $('.btn-historico-eventos').on('click', function() {
            var chave = $(this).data('chave');
            var url = "{{ route('dfe.eventos', ':chave') }}".replace(':chave', chave);

            $('#historicoLoading').show();
            $('#historicoConteudo').hide();
            $('#tabelaEventosBody').empty();
            $('#modalHistoricoEventos').modal('show');

            $.getJSON(url, function(res) {
                $('#historicoLoading').hide();
                $('#historicoConteudo').show();

                $('#histDocNumero').text(res.numero || 'S/N');
                $('#histDocSerie').text(res.serie || '1');
                $('#histDocTotal').text(res.total || '-');
                $('#histDocEmitente').text(res.emitente || '-');
                $('#histDocChave').text(res.chave || '-');

                if (res.eventos && res.eventos.length > 0) {
                    $.each(res.eventos, function(idx, ev) {
                        var row = '<tr>' +
                            '<td>' + ev.data_evento + '</td>' +
                            '<td>' +
                                '<span class="badge ' + ev.badge_classe + ' mr-1">' + ev.nome_evento + '</span>' +
                                (ev.justificativa ? '<div class="text-xs text-muted mt-1"><em>Motivo: ' + ev.justificativa + '</em></div>' : '') +
                                (ev.detalhes && ev.detalhes.xCorrecao ? '<div class="text-xs text-info mt-1"><em>Correção: ' + ev.detalhes.xCorrecao + '</em></div>' : '') +
                            '</td>' +
                            '<td class="text-monospace small">' + ev.protocolo + '</td>' +
                            '<td><i class="fas fa-user-edit text-muted mr-1"></i>' + ev.usuario + '</td>' +
                        '</tr>';
                        $('#tabelaEventosBody').append(row);
                    });
                } else {
                    $('#tabelaEventosBody').html('<tr><td colspan="4" class="text-center py-3 text-muted"><i class="fas fa-info-circle mr-1"></i> Nenhum evento registrado no histórico para esta nota fiscal.</td></tr>');
                }
            }).fail(function() {
                $('#historicoLoading').hide();
                alert('Erro ao carregar o histórico de eventos da SEFAZ.');
            });
        });
    });
</script>
@stop
