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

        .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 600;
        }

        .badge-excursao {
            color: #fff;
            background-color: #6f42c1;
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
                    const dataInicio = document.getElementById('bloqueio_data_inicio');
                    const dataFim = document.getElementById('bloqueio_data_fim');
                    if (dataInicio && dataFim) {
                        dataInicio.value = info.dateStr;
                        dataFim.value = info.dateStr;
                    }
                    if (window.$ && typeof $('#modalBloquearData').modal === 'function') {
                        $('#modalBloquearData').modal('show');
                    }
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

                    const props = info.event.extendedProps;
                    const isBloqueio = !!props.is_bloqueio;
                    const excursao = props.categoria === 'excursao';

                    const modalTitle = document.getElementById('modalDetalheEventoLabel');
                    if (isBloqueio) {
                        modalTitle.innerText = 'Detalhes do Bloqueio';
                    } else if (excursao) {
                        modalTitle.innerText = 'Detalhes da Excursão';
                    } else {
                        modalTitle.innerText = 'Detalhes do Evento';
                    }

                    document.getElementById('detalhe-espaco').innerText = props.espaco || '-';
                    document.getElementById('detalhe-tipo').innerHTML = isBloqueio
                        ? '<span class="badge badge-dark">Bloqueio de Data</span>'
                        : (props.tipo || '-');
                    document.getElementById('detalhe-cliente').innerText = props.cliente || '-';
                    document.getElementById('detalhe-status').innerHTML = isBloqueio
                        ? '<span class="badge badge-dark">Bloqueado</span>'
                        : (props.status || '-');
                    document.getElementById('detalhe-total').innerText = props.total_formatado || '-';
                    document.getElementById('detalhe-periodo').innerText = excursao
                        ? (props.data_inicio || '-')
                        : `${props.data_inicio || '-'} a ${props.data_fim || '-'}`;

                    // Exibir / ocultar linhas conforme o tipo
                    ['detalhe-espaco-linha', 'detalhe-cliente-linha'].forEach(id => {
                        document.getElementById(id).classList.toggle('d-none', excursao);
                    });

                    const pessoasLinha = document.getElementById('detalhe-pessoas-linha');
                    pessoasLinha.classList.toggle('d-none', !excursao);
                    document.getElementById('detalhe-pessoas').innerText = props.qtd_pessoas || '-';
                    ['detalhe-responsavel-linha', 'detalhe-telefone-linha', 'detalhe-descricao-linha'].forEach(id => {
                        document.getElementById(id).classList.toggle('d-none', !excursao);
                    });
                    document.getElementById('detalhe-responsavel').innerText = props.responsavel || '-';
                    document.getElementById('detalhe-telefone').innerText = props.telefone_responsavel || '-';
                    document.getElementById('detalhe-descricao').innerText = props.descricao || '-';

                    // Observações
                    const obsLinha = document.getElementById('detalhe-obs-linha');
                    if (isBloqueio) {
                        obsLinha.classList.remove('d-none');
                        document.getElementById('detalhe-obs').innerText = props.observacoes || 'Nenhuma observação informada.';
                    } else if (props.observacoes) {
                        obsLinha.classList.remove('d-none');
                        document.getElementById('detalhe-obs').innerText = props.observacoes;
                    } else {
                        obsLinha.classList.add('d-none');
                    }

                    // Total
                    const totalLinha = document.getElementById('detalhe-total-linha');
                    totalLinha.classList.toggle('d-none', isBloqueio);

                    // Botões de ação
                    const abrirAluguel = document.getElementById('detalhe-abrir-aluguel');
                    abrirAluguel.classList.toggle('d-none', excursao || isBloqueio);
                    abrirAluguel.href = (!excursao && !isBloqueio) ? `/aluguel/${props.aluguel_id}/edit` : '#';

                    const btnDesbloquear = document.getElementById('detalhe-btn-desbloquear');
                    btnDesbloquear.classList.toggle('d-none', !isBloqueio);
                    if (isBloqueio) {
                        btnDesbloquear.dataset.aluguelId = props.aluguel_id;
                    }

                    if (window.$ && typeof $('#modalDetalheEvento').modal === 'function') {
                        $('#modalDetalheEvento').modal('show');
                    }
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
