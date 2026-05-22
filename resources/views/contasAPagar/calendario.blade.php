@extends('adminlte::page')

@section('title', 'Calendário de Contas a Pagar')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Calendário de Contas a Pagar</h5>
            <small class="text-muted">Visualize os vencimentos por dia no calendário.</small>
        </div>

        <a href="{{ route('contasAPagar.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar para listagem
        </a>
    </div>
    <hr>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-calendar-alt"></i> Vencimentos
            </span>

            <div class="d-flex align-items-center">
                <span class="badge badge-warning mr-2">Pendente</span>
                <span class="badge badge-success">Pago</span>
            </div>
        </div>

        <div class="card-body">
            <div id="calendario-contas-pagar"></div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalheContaCalendario" tabindex="-1" aria-labelledby="modalDetalheContaCalendarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalheContaCalendarioLabel">Detalhes da Conta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p><strong>Descrição:</strong> <span id="detalhe-descricao">-</span></p>
                    <p><strong>Fornecedor:</strong> <span id="detalhe-fornecedor">-</span></p>
                    <p><strong>Valor:</strong> <span id="detalhe-valor">-</span></p>
                    <p><strong>Status:</strong> <span id="detalhe-status">-</span></p>
                    <p id="detalhe-parcela-wrapper" class="d-none">
                        <strong>Parcela:</strong> <span id="detalhe-parcela">-</span>
                    </p>
                </div>

                <div class="modal-footer">
                    <a href="{{ route('contasAPagar.index') }}" class="btn btn-primary">
                        Abrir listagem
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        #calendario-contas-pagar {
            min-height: 650px;
        }

        .fc-event {
            cursor: pointer;
        }

        .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 600;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/pt-br.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendario-contas-pagar');

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
                events: "{{ route('contasAPagar.calendario.eventos') }}",
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    const props = info.event.extendedProps;

                    document.getElementById('detalhe-descricao').innerText = props.descricao || '-';
                    document.getElementById('detalhe-fornecedor').innerText = props.fornecedor || '-';
                    document.getElementById('detalhe-valor').innerText = props.valor_formatado || '-';
                    document.getElementById('detalhe-status').innerText = props.status || '-';

                    const parcelaWrapper = document.getElementById('detalhe-parcela-wrapper');
                    const parcelaTexto = document.getElementById('detalhe-parcela');

                    if (props.parcela_id) {
                        parcelaWrapper.classList.remove('d-none');
                        parcelaTexto.innerText = `${props.numero_parcela || '-'} de ${props.total_parcelas || '-'}`;
                    } else {
                        parcelaWrapper.classList.add('d-none');
                        parcelaTexto.innerText = '-';
                    }

                    $('#modalDetalheContaCalendario').modal('show');
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