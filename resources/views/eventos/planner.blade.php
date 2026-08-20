@extends('adminlte::page')

@section('title', 'Planner de Eventos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Planner de Eventos</h5>
            <small class="text-muted">Visualize os aluguéis de espaço e as excursões agendadas no calendário.</small>
        </div>

        <a href="{{ route('eventos.home') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
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
                <span class="badge badge-excursao">Excursão</span>
            </div>
        </div>

        <div class="card-body">
            <div id="planner-eventos"></div>
        </div>
    </div>

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
                    <p class="mb-1"><strong>Período:</strong> <span id="detalhe-periodo">-</span></p>
                    <p class="mb-1" id="detalhe-status-linha"><strong>Status:</strong> <span id="detalhe-status">-</span></p>
                    <p class="mb-0"><strong>Total:</strong> <span id="detalhe-total">-</span></p>
                </div>

                <div class="modal-footer">
                    <a href="#" id="detalhe-abrir-aluguel" class="btn btn-primary">Abrir</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
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
                events: function(fetchInfo, successCallback, failureCallback) {
                    const url = new URL("{{ route('eventos.planner.eventos') }}");
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
                    const excursao = props.categoria === 'excursao';

                    document.getElementById('detalhe-espaco').innerText = props.espaco || '-';
                    document.getElementById('detalhe-tipo').innerText = props.tipo || '-';
                    document.getElementById('detalhe-cliente').innerText = props.cliente || '-';
                    document.getElementById('detalhe-status').innerText = props.status || '-';
                    document.getElementById('detalhe-total').innerText = props.total_formatado || '-';
                    document.getElementById('detalhe-periodo').innerText = excursao
                        ? (props.data_inicio || '-')
                        : `${props.data_inicio || '-'} a ${props.data_fim || '-'}`;

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

                    const abrirAluguel = document.getElementById('detalhe-abrir-aluguel');
                    abrirAluguel.classList.toggle('d-none', excursao);
                    abrirAluguel.href = excursao ? '#' : `/aluguel/${props.aluguel_id}/edit`;

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
        });
    </script>
@stop
