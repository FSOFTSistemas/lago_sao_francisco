@extends('adminlte::page')

@section('title', 'Planner de Eventos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Planner de Eventos</h5>
            <small class="text-muted">Visualize os aluguéis de espaço e as excursões agendadas no calendário.</small>
        </div>

        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-dark btn-sm mr-2" data-toggle="modal" data-target="#modalBloquearData">
                <i class="fas fa-lock"></i> Bloquear Data
            </button>
            <a href="{{ route('aluguel.create') }}" class="btn btn-primary btn-sm mr-2">
                <i class="fas fa-plus"></i> Novo Aluguel
            </a>
            <a href="{{ route('eventos.home') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
    <hr>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-calendar-alt"></i> Eventos
            </span>

            <div class="d-flex align-items-center">
                <span class="badge badge-warning mr-2">Pendente</span>
                <span class="badge badge-success mr-2">Pago</span>
                <span class="badge badge-danger mr-2">Cancelado</span>
                <span class="badge badge-excursao mr-2">Excursão</span>
                <span class="badge badge-dark">Bloqueado</span>
            </div>
        </div>

        <div class="card-body">
            <div id="planner-eventos"></div>
        </div>
    </div>

    <!-- Seção de Detalhes do Dia Selecionado -->
    <div class="card shadow-sm d-none mt-3 border-top border-primary" id="painel-dia-detalhes" style="border-top-width: 3px !important;">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
            <div>
                <h5 class="mb-0 font-weight-bold text-dark">
                    <i class="fas fa-calendar-day text-primary mr-1"></i>
                    Eventos em <span id="painel-dia-data-titulo" class="text-primary">-</span>
                </h5>
                <small class="text-muted" id="painel-dia-resumo">Clique em qualquer evento para ver mais detalhes.</small>
            </div>
            <div class="d-flex align-items-center">
                <a href="#" id="painel-dia-btn-agendar-topo" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-plus mr-1"></i> Agendar Evento
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="painel-dia-btn-fechar" title="Fechar sessão">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="card-body p-3" id="painel-dia-conteudo">
            <!-- Conteúdo dinâmico via JS -->
        </div>
    </div>

    <!-- Modal Detalhe do Evento / Bloqueio -->
    <div class="modal fade" id="modalDetalheEvento" tabindex="-1" aria-labelledby="modalDetalheEventoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalheEventoLabel">Detalhes do Evento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p class="mb-1" id="detalhe-espaco-linha"><strong>Espaço:</strong> <span id="detalhe-espaco">-</span></p>
                    <p class="mb-1"><strong>Tipo:</strong> <span id="detalhe-tipo">-</span></p>
                    <p class="mb-1" id="detalhe-cliente-linha"><strong>Cliente:</strong> <span id="detalhe-cliente">-</span></p>
                    <p class="mb-1 d-none" id="detalhe-pessoas-linha"><strong>Quantidade de pessoas:</strong> <span id="detalhe-pessoas">-</span></p>
                    <p class="mb-1 d-none" id="detalhe-responsavel-linha"><strong>Responsável:</strong> <span id="detalhe-responsavel">-</span></p>
                    <p class="mb-1 d-none" id="detalhe-telefone-linha"><strong>Telefone:</strong> <span id="detalhe-telefone">-</span></p>
                    <p class="mb-1 d-none" id="detalhe-descricao-linha"><strong>Descrição:</strong> <span id="detalhe-descricao">-</span></p>
                    <p class="mb-1 d-none" id="detalhe-obs-linha"><strong>Observações:</strong> <span id="detalhe-obs">-</span></p>
                    <p class="mb-1"><strong>Período:</strong> <span id="detalhe-periodo">-</span></p>
                    <p class="mb-1" id="detalhe-status-linha"><strong>Status:</strong> <span id="detalhe-status">-</span></p>
                    <p class="mb-0" id="detalhe-total-linha"><strong>Total:</strong> <span id="detalhe-total">-</span></p>
                </div>

                <div class="modal-footer">
                    <button type="button" id="detalhe-btn-desbloquear" class="btn btn-danger d-none">
                        <i class="fas fa-unlock mr-1"></i> Desbloquear Data
                    </button>
                    <a href="#" id="detalhe-abrir-aluguel" class="btn btn-primary">Abrir</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bloquear Data -->
    <div class="modal fade" id="modalBloquearData" tabindex="-1" aria-labelledby="modalBloquearDataLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalBloquearDataLabel"><i class="fas fa-lock mr-1"></i> Bloquear Data de Evento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="formBloquearData">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="bloqueio_espaco_id"><strong>Espaço:</strong></label>
                            <select name="espaco_id" id="bloqueio_espaco_id" class="form-control" required>
                                <option value="todos">Todos os Espaços (Complexo Inteiro)</option>
                                @foreach($espacos as $espaco)
                                    <option value="{{ $espaco->id }}">{{ $espaco->nome }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Selecione um espaço específico ou bloqueie todos de uma só vez.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="bloqueio_data_inicio"><strong>Data Início:</strong></label>
                                <input type="date" name="data_inicio" id="bloqueio_data_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="bloqueio_data_fim"><strong>Data Fim:</strong></label>
                                <input type="date" name="data_fim" id="bloqueio_data_fim" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label for="bloqueio_observacoes"><strong>Motivo / Observações:</strong></label>
                            <textarea name="observacoes" id="bloqueio_observacoes" class="form-control" rows="2" placeholder="Ex: Manutenção do espaço, evento institucional interno, etc."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnSalvarBloqueio" class="btn btn-dark">
                            <i class="fas fa-lock mr-1"></i> Confirmar Bloqueio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        #planner-eventos {
            min-height: 650px;
        }

        .fc-event {
            cursor: pointer;
        }

        /* Indicador de cursor e hover para os dias do calendário */
        .fc .fc-daygrid-day {
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }

        .fc .fc-daygrid-day:hover {
            background-color: rgba(0, 123, 255, 0.08) !important;
        }

        .fc .fc-daygrid-day.dia-selecionado {
            background-color: rgba(0, 123, 255, 0.14) !important;
            box-shadow: inset 0 0 0 2px #007bff;
        }

        .fc .fc-timegrid-slot,
        .fc .fc-list-event {
            cursor: pointer;
        }

        .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 600;
        }

        .badge-excursao {
            color: #fff;
            background-color: #6f42c1;
        }

        #painel-dia-detalhes {
            transition: all 0.3s ease-in-out;
            scroll-margin-top: 20px;
        }

        #painel-dia-detalhes .table th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
            background-color: #f8f9fa;
        }

        #painel-dia-detalhes .table td {
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/pt-br.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('planner-eventos');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                buttonText: {
                    today: 'Hoje',
                    month: 'Mês',
                    week: 'Semana',
                    list: 'Lista'
                },
                dateClick: function(info) {
                    // Destaque visual da célula selecionada
                    document.querySelectorAll('.fc-daygrid-day.dia-selecionado').forEach(el => {
                        el.classList.remove('dia-selecionado');
                    });
                    if (info.dayEl) {
                        info.dayEl.classList.add('dia-selecionado');
                    }

                    const dataInicio = document.getElementById('bloqueio_data_inicio');
                    const dataFim = document.getElementById('bloqueio_data_fim');
                    if (dataInicio && dataFim) {
                        dataInicio.value = info.dateStr;
                        dataFim.value = info.dateStr;
                    }
                    renderizarPainelDia(info.dateStr);
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    const url = new URL('/eventos/planner/eventos', window.location.origin);
                    url.searchParams.set('start', fetchInfo.startStr);
                    url.searchParams.set('end', fetchInfo.endStr);

                    fetch(url)
                        .then(response => response.json())
                        .then(eventos => successCallback(eventos))
                        .catch(error => failureCallback(error));
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    abrirModalDetalhes(info.event);
                },
                loading: function(isLoading) {
                    if (isLoading) {
                        calendarEl.classList.add('opacity-50');
                    } else {
                        calendarEl.classList.remove('opacity-50');
                    }
                }
            });

            calendar.render();

            let dataAtualSelecionada = null;

            function abrirModalDetalhes(evento) {
                if (!evento) return;
                const props = evento.extendedProps || {};
                const isBloqueio = !!props.is_bloqueio;
                const excursao = props.categoria === 'excursao';

                const modalTitle = document.getElementById('modalDetalheEventoLabel');
                if (modalTitle) {
                    if (isBloqueio) {
                        modalTitle.innerText = 'Detalhes do Bloqueio';
                    } else if (excursao) {
                        modalTitle.innerText = 'Detalhes da Excursão';
                    } else {
                        modalTitle.innerText = 'Detalhes do Evento';
                    }
                }

                const espacoEl = document.getElementById('detalhe-espaco');
                if (espacoEl) espacoEl.innerText = props.espaco || '-';

                const tipoEl = document.getElementById('detalhe-tipo');
                if (tipoEl) {
                    tipoEl.innerHTML = isBloqueio
                        ? '<span class="badge badge-dark">Bloqueio de Data</span>'
                        : (props.tipo || '-');
                }

                const clienteEl = document.getElementById('detalhe-cliente');
                if (clienteEl) clienteEl.innerText = props.cliente || '-';

                const statusEl = document.getElementById('detalhe-status');
                if (statusEl) {
                    statusEl.innerHTML = isBloqueio
                        ? '<span class="badge badge-dark">Bloqueado</span>'
                        : (props.status || '-');
                }

                const totalEl = document.getElementById('detalhe-total');
                if (totalEl) totalEl.innerText = props.total_formatado || '-';

                const periodoEl = document.getElementById('detalhe-periodo');
                if (periodoEl) {
                    periodoEl.innerText = excursao
                        ? (props.data_inicio || '-')
                        : `${props.data_inicio || '-'} a ${props.data_fim || '-'}`;
                }

                // Exibir / ocultar linhas conforme o tipo
                ['detalhe-espaco-linha', 'detalhe-cliente-linha'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.toggle('d-none', excursao);
                });

                const pessoasLinha = document.getElementById('detalhe-pessoas-linha');
                if (pessoasLinha) {
                    pessoasLinha.classList.toggle('d-none', !excursao);
                    const pessoasVal = document.getElementById('detalhe-pessoas');
                    if (pessoasVal) pessoasVal.innerText = props.qtd_pessoas || '-';
                }

                ['detalhe-responsavel-linha', 'detalhe-telefone-linha', 'detalhe-descricao-linha'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.toggle('d-none', !excursao);
                });

                const respEl = document.getElementById('detalhe-responsavel');
                if (respEl) respEl.innerText = props.responsavel || '-';

                const telEl = document.getElementById('detalhe-telefone');
                if (telEl) telEl.innerText = props.telefone_responsavel || '-';

                const descEl = document.getElementById('detalhe-descricao');
                if (descEl) descEl.innerText = props.descricao || '-';

                // Observações
                const obsLinha = document.getElementById('detalhe-obs-linha');
                const obsVal = document.getElementById('detalhe-obs');
                if (obsLinha && obsVal) {
                    if (isBloqueio) {
                        obsLinha.classList.remove('d-none');
                        obsVal.innerText = props.observacoes || 'Nenhuma observação informada.';
                    } else if (props.observacoes) {
                        obsLinha.classList.remove('d-none');
                        obsVal.innerText = props.observacoes;
                    } else {
                        obsLinha.classList.add('d-none');
                    }
                }

                // Total
                const totalLinha = document.getElementById('detalhe-total-linha');
                if (totalLinha) totalLinha.classList.toggle('d-none', isBloqueio);

                // Botões de ação
                const abrirAluguel = document.getElementById('detalhe-abrir-aluguel');
                if (abrirAluguel) {
                    abrirAluguel.classList.toggle('d-none', isBloqueio);
                    if (excursao) {
                        abrirAluguel.href = `/eventos/excursoes/${props.excursao_id}/editar`;
                    } else if (!isBloqueio) {
                        abrirAluguel.href = `/aluguel/${props.aluguel_id}/edit`;
                    } else {
                        abrirAluguel.href = '#';
                    }
                }

                const btnDesbloquear = document.getElementById('detalhe-btn-desbloquear');
                if (btnDesbloquear) {
                    btnDesbloquear.classList.toggle('d-none', !isBloqueio);
                    if (isBloqueio) {
                        btnDesbloquear.dataset.aluguelId = props.aluguel_id;
                    }
                }

                if (window.$ && typeof $('#modalDetalheEvento').modal === 'function') {
                    $('#modalDetalheEvento').modal('show');
                }
            }

            function renderizarPainelDia(dateStr) {
                dataAtualSelecionada = dateStr;

                const partes = dateStr.split('-');
                const dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;

                const tituloEl = document.getElementById('painel-dia-data-titulo');
                const resumoEl = document.getElementById('painel-dia-resumo');
                const btnAgendarTopo = document.getElementById('painel-dia-btn-agendar-topo');
                const conteudoEl = document.getElementById('painel-dia-conteudo');
                const painelEl = document.getElementById('painel-dia-detalhes');

                if (tituloEl) tituloEl.innerText = dataFormatada;
                if (btnAgendarTopo) btnAgendarTopo.href = `/aluguel/create?data_inicio=${dateStr}&data_fim=${dateStr}`;

                // Filtrar eventos do calendário que cobrem esta data
                const eventos = calendar.getEvents().filter(ev => {
                    const p = ev.extendedProps || {};
                    const ini = p.raw_data_inicio || (ev.startStr ? ev.startStr.split('T')[0] : '');
                    const fim = p.raw_data_fim || (ev.endStr ? ev.endStr.split('T')[0] : ini);
                    return dateStr >= ini && dateStr <= fim;
                });

                const bloqueios = eventos.filter(ev => ev.extendedProps?.is_bloqueio);
                const agendamentos = eventos.filter(ev => !ev.extendedProps?.is_bloqueio);

                let html = '';

                // 1. Destaque de Bloqueios de Data
                if (bloqueios.length > 0) {
                    html += '<div class="mb-3">';
                    bloqueios.forEach(b => {
                        const p = b.extendedProps || {};
                        html += `
                            <div class="alert alert-dark d-flex flex-wrap justify-content-between align-items-center mb-2 shadow-sm py-2 px-3">
                                <div class="mr-2 mb-1 mb-md-0">
                                    <span class="badge badge-danger mr-2 px-2 py-1"><i class="fas fa-ban mr-1"></i> Data Bloqueada</span>
                                    <strong>Espaço:</strong> ${p.espaco || 'Todos os Espaços'}
                                    ${p.observacoes ? `<span class="text-white-50 ml-2">&bull; ${p.observacoes}</span>` : ''}
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-light btn-abrir-evento-detalhes" data-evento-id="${b.id}">
                                        <i class="fas fa-unlock mr-1"></i> Desbloquear
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                }

                // 2. Estado Vazio ou Tabela de Agendamentos
                if (eventos.length === 0) {
                    if (resumoEl) resumoEl.innerText = 'Nenhum agendamento para este dia.';
                    html += `
                        <div class="text-center py-4">
                            <div class="mb-2">
                                <i class="fas fa-calendar-check fa-3x text-muted" style="opacity: 0.4;"></i>
                            </div>
                            <h5 class="font-weight-bold text-secondary">Nenhum evento agendado para ${dataFormatada}</h5>
                            <p class="text-muted mb-3">Todos os espaços estão livres e disponíveis nesta data.</p>
                            <div>
                                <a href="/aluguel/create?data_inicio=${dateStr}&data_fim=${dateStr}" class="btn btn-primary px-3 mr-2">
                                    <i class="fas fa-plus mr-1"></i> Agendar Evento nesta data
                                </a>
                                <button type="button" class="btn btn-outline-dark px-3 btn-abrir-bloqueio-modal" data-data="${dateStr}">
                                    <i class="fas fa-lock mr-1"></i> Bloquear esta data
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    const qtdEventos = agendamentos.length;
                    const qtdBloqueios = bloqueios.length;
                    let resumoTexto = [];
                    if (qtdEventos > 0) resumoTexto.push(`${qtdEventos} agendamento(s)`);
                    if (qtdBloqueios > 0) resumoTexto.push(`${qtdBloqueios} bloqueio(s) de espaço`);
                    if (resumoEl) resumoEl.innerText = resumoTexto.join(' | ');

                    if (agendamentos.length > 0) {
                        html += `
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 120px;">Categoria</th>
                                            <th>Espaço</th>
                                            <th>Cliente / Responsável</th>
                                            <th>Tipo</th>
                                            <th style="width: 120px;">Status</th>
                                            <th class="text-right" style="width: 140px;">Valor Total</th>
                                            <th class="text-center" style="width: 110px;">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        agendamentos.forEach(ev => {
                            const p = ev.extendedProps || {};
                            const isExcursao = (p.categoria === 'excursao');

                            const catBadge = isExcursao
                                ? '<span class="badge badge-excursao px-2 py-1"><i class="fas fa-bus mr-1"></i> Excursão</span>'
                                : '<span class="badge badge-primary px-2 py-1"><i class="fas fa-glass-cheers mr-1"></i> Evento</span>';

                            const statusStr = (p.status || '').toLowerCase();
                            const statusClass = (statusStr === 'pago')
                                ? 'badge-success'
                                : (statusStr === 'cancelado' ? 'badge-danger' : 'badge-warning');

                            const statusBadge = `<span class="badge ${statusClass} px-2 py-1">${p.status || '-'}</span>`;

                            const acoes = isExcursao
                                ? `<button type="button" class="btn btn-sm btn-info btn-abrir-evento-detalhes mr-1" data-evento-id="${ev.id}" title="Ver detalhes"><i class="fas fa-eye"></i></button>
                                   <a href="/eventos/excursoes/${p.excursao_id}/editar" class="btn btn-sm btn-secondary" title="Editar"><i class="fas fa-edit"></i></a>`
                                : `<button type="button" class="btn btn-sm btn-info btn-abrir-evento-detalhes mr-1" data-evento-id="${ev.id}" title="Ver detalhes"><i class="fas fa-eye"></i></button>
                                   <a href="/aluguel/${p.aluguel_id}/edit" class="btn btn-sm btn-secondary" title="Editar"><i class="fas fa-edit"></i></a>`;

                            html += `
                                <tr>
                                    <td>${catBadge}</td>
                                    <td><strong>${isExcursao ? 'Complexo / Parque' : (p.espaco || '-')}</strong></td>
                                    <td>${isExcursao ? `${p.responsavel || '-'} <small class="text-muted">(${p.qtd_pessoas} pessoas)</small>` : (p.cliente || '-')}</td>
                                    <td>${p.tipo || '-'}</td>
                                    <td>${statusBadge}</td>
                                    <td class="text-right font-weight-bold">${p.total_formatado || '-'}</td>
                                    <td class="text-center">${acoes}</td>
                                </tr>
                            `;
                        });

                        html += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }
                }

                if (conteudoEl) conteudoEl.innerHTML = html;
                if (painelEl) {
                    painelEl.classList.remove('d-none');
                    painelEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }

            // Cliques nos botões dinâmicos da sessão do dia
            document.addEventListener('click', function(e) {
                // Abrir detalhes do evento (ou bloqueio) no modal
                const btnDetalhes = e.target.closest('.btn-abrir-evento-detalhes');
                if (btnDetalhes) {
                    const eventoId = btnDetalhes.dataset.eventoId;
                    const ev = calendar.getEventById(eventoId);
                    if (ev) {
                        abrirModalDetalhes(ev);
                    }
                }

                // Abrir modal de bloqueio pré-preenchido
                const btnBloq = e.target.closest('.btn-abrir-bloqueio-modal');
                if (btnBloq) {
                    const dataStr = btnBloq.dataset.data;
                    const dataInicio = document.getElementById('bloqueio_data_inicio');
                    const dataFim = document.getElementById('bloqueio_data_fim');
                    if (dataInicio && dataFim) {
                        dataInicio.value = dataStr;
                        dataFim.value = dataStr;
                    }
                    if (window.$ && typeof $('#modalBloquearData').modal === 'function') {
                        $('#modalBloquearData').modal('show');
                    }
                }
            });

            // Botão fechar sessão do dia
            const btnFecharPainel = document.getElementById('painel-dia-btn-fechar');
            if (btnFecharPainel) {
                btnFecharPainel.addEventListener('click', function() {
                    const painelEl = document.getElementById('painel-dia-detalhes');
                    if (painelEl) painelEl.classList.add('d-none');
                    document.querySelectorAll('.fc-daygrid-day.dia-selecionado').forEach(el => {
                        el.classList.remove('dia-selecionado');
                    });
                });
            }

            // Preencher data atual ao abrir modal de bloqueio caso esteja vazio
            if (window.$) {
                $('#modalBloquearData').on('show.bs.modal', function() {
                    const dataInicio = document.getElementById('bloqueio_data_inicio');
                    const dataFim = document.getElementById('bloqueio_data_fim');
                    if (dataInicio && !dataInicio.value) {
                        const today = new Date().toISOString().split('T')[0];
                        dataInicio.value = today;
                        if (dataFim && !dataFim.value) {
                            dataFim.value = today;
                        }
                    }
                });
            }

            // Desbloquear Data
            const btnDesbloquear = document.getElementById('detalhe-btn-desbloquear');
            if (btnDesbloquear) {
                btnDesbloquear.addEventListener('click', function() {
                    const aluguelId = this.dataset.aluguelId;
                    if (!aluguelId) return;

                    Swal.fire({
                        title: 'Desbloquear Data?',
                        text: 'Tem certeza de que deseja remover este bloqueio de data?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-trash"></i> Sim, remover bloqueio',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/aluguel/${aluguelId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(async res => {
                                const contentType = res.headers.get('content-type') || '';
                                const data = contentType.includes('application/json') ? await res.json() : null;
                                return { ok: res.ok, status: res.status, data };
                            })
                            .then(({ ok, data }) => {
                                if (ok && data && data.success) {
                                    try {
                                        if (window.$ && typeof $('#modalDetalheEvento').modal === 'function') {
                                            $('#modalDetalheEvento').modal('hide');
                                        }
                                    } catch (e) {
                                        console.warn('Modal hide error:', e);
                                    }

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Removido!',
                                        text: data.message || 'Bloqueio removido com sucesso.',
                                        timer: 1200,
                                        showConfirmButton: false
                                    });

                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    Swal.fire('Atenção', (data && data.message) ? data.message : 'Não foi possível remover o bloqueio.', 'warning');
                                }
                            })
                            .catch(err => {
                                console.error('Erro ao remover bloqueio:', err);
                                const msg = (err && err.message) ? err.message : 'Ocorreu um erro ao tentar remover o bloqueio.';
                                Swal.fire('Erro', msg, 'error');
                            });
                        }
                    });
                });
            }

            // Submeter Bloqueio de Data via AJAX
            const formBloquear = document.getElementById('formBloquearData');
            if (formBloquear) {
                formBloquear.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const btn = document.getElementById('btnSalvarBloqueio');
                    const originalBtnHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Bloqueando...';

                    const dataInicio = document.getElementById('bloqueio_data_inicio').value;
                    const dataFim = document.getElementById('bloqueio_data_fim').value;
                    const espacoId = document.getElementById('bloqueio_espaco_id').value;
                    const observacoes = document.getElementById('bloqueio_observacoes').value;

                    fetch('/eventos/bloquear-data', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            data_inicio: dataInicio,
                            data_fim: dataFim,
                            espaco_id: espacoId,
                            observacoes: observacoes
                        })
                    })
                    .then(async res => {
                        const contentType = res.headers.get('content-type') || '';
                        const data = contentType.includes('application/json') ? await res.json() : null;
                        return { ok: res.ok, status: res.status, data };
                    })
                    .then(({ ok, data }) => {
                        btn.disabled = false;
                        btn.innerHTML = originalBtnHtml;

                        if (ok && data && data.success) {
                            try {
                                if (window.$ && typeof $('#modalBloquearData').modal === 'function') {
                                    $('#modalBloquearData').modal('hide');
                                }
                            } catch (e) {
                                console.warn('Modal hide error:', e);
                            }

                            formBloquear.reset();

                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: data.message || 'Data bloqueada com sucesso!',
                                timer: 1200,
                                showConfirmButton: false
                            });

                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            const errorMsg = (data && data.message) ? data.message : 'Não foi possível realizar o bloqueio.';
                            Swal.fire('Atenção', errorMsg, 'warning');
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao processar o bloqueio:', err);
                        btn.disabled = false;
                        btn.innerHTML = originalBtnHtml;
                        const msg = (err && err.message) ? err.message : 'Ocorreu um erro ao processar o bloqueio.';
                        Swal.fire('Erro', msg, 'error');
                    });
                });
            }
        });
    </script>
@stop
