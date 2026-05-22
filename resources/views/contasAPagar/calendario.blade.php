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
                    <div class="mb-3">
                        <p class="mb-1"><strong>Data:</strong> <span id="detalhe-data">-</span></p>
                        <p class="mb-1"><strong>Total do dia:</strong> <span id="detalhe-total-dia">-</span></p>
                        <p class="mb-0"><strong>Quantidade de contas:</strong> <span id="detalhe-quantidade">-</span></p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Descrição</th>
                                    <th>Fornecedor</th>
                                    <th>Parcela</th>
                                    <th>Status</th>
                                    <th class="text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody id="detalhe-contas-dia">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhuma conta encontrada.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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

        .fc-event-title {
            white-space: normal;
            font-weight: 600;
        }

        .evento-total-dia {
            border: none !important;
            padding: 4px 6px !important;
            border-radius: 6px !important;
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

            function formatarMoeda(valor) {
                return Number(valor || 0).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
            }

            function formatarData(data) {
                const partes = String(data).split('-');

                if (partes.length !== 3) {
                    return data;
                }

                return `${partes[2]}/${partes[1]}/${partes[0]}`;
            }

            function extrairValorNumerico(evento) {
                const props = evento.extendedProps || {};
                const valorDireto = props.valor ?? evento.valor ?? null;

                if (valorDireto !== null && valorDireto !== undefined && valorDireto !== '') {
                    return Number(valorDireto);
                }

                const valorFormatado = props.valor_formatado || evento.valor_formatado || '';

                if (!valorFormatado) {
                    return 0;
                }

                return Number(
                    String(valorFormatado)
                        .replace('R$', '')
                        .replace(/\./g, '')
                        .replace(',', '.')
                        .trim()
                ) || 0;
            }

            function abrirModalDetalhe() {
                const modalEl = document.getElementById('modalDetalheContaCalendario');

                if (window.$ && typeof $('#modalDetalheContaCalendario').modal === 'function') {
                    $('#modalDetalheContaCalendario').modal('show');
                    return;
                }

                if (
                    window.bootstrap &&
                    window.bootstrap.Modal &&
                    typeof window.bootstrap.Modal.getOrCreateInstance === 'function'
                ) {
                    const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                    return;
                }

                if (window.bootstrap && window.bootstrap.Modal) {
                    const modal = new window.bootstrap.Modal(modalEl);
                    modal.show();
                }
            }

            function agruparEventosPorDia(eventos) {
                const grupos = {};

                eventos.forEach(function(evento) {
                    const data = evento.start;
                    const valor = extrairValorNumerico(evento);

                    if (!grupos[data]) {
                        grupos[data] = {
                            data,
                            total: 0,
                            contas: [],
                            possuiPendente: false,
                        };
                    }

                    grupos[data].total += valor;
                    grupos[data].contas.push(evento);

                    if ((evento.extendedProps?.status || evento.status) !== 'pago') {
                        grupos[data].possuiPendente = true;
                    }
                });

                return Object.values(grupos).map(function(grupo) {
                    return {
                        id: `dia_${grupo.data}`,
                        title: `Total: ${formatarMoeda(grupo.total)}`,
                        start: grupo.data,
                        allDay: true,
                        classNames: ['evento-total-dia'],
                        color: grupo.possuiPendente ? '#ffc107' : '#28a745',
                        textColor: grupo.possuiPendente ? '#212529' : '#ffffff',
                        extendedProps: {
                            data: grupo.data,
                            total: grupo.total,
                            total_formatado: formatarMoeda(grupo.total),
                            quantidade: grupo.contas.length,
                            contas: grupo.contas,
                        }
                    };
                });
            }

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
                    const url = new URL("{{ route('contasAPagar.calendario.eventos') }}");
                    url.searchParams.set('start', fetchInfo.startStr);
                    url.searchParams.set('end', fetchInfo.endStr);

                    fetch(url)
                        .then(response => response.json())
                        .then(eventos => {
                            console.log('Eventos recebidos do calendário:', eventos);
                            successCallback(agruparEventosPorDia(eventos));
                        })
                        .catch(error => {
                            console.error('Erro ao carregar calendário:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    const props = info.event.extendedProps;
                    const contas = props.contas || [];
                    const tbody = document.getElementById('detalhe-contas-dia');

                    document.getElementById('detalhe-data').innerText = formatarData(props.data);
                    document.getElementById('detalhe-total-dia').innerText = props.total_formatado || '-';
                    document.getElementById('detalhe-quantidade').innerText = props.quantidade || 0;

                    tbody.innerHTML = '';

                    if (!contas.length) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhuma conta encontrada.</td></tr>';
                    } else {
                        contas.forEach(function(conta) {
                            const dados = conta.extendedProps || conta || {};
                            const parcela = dados.parcela_id
                                ? `${dados.numero_parcela || '-'} de ${dados.total_parcelas || '-'}`
                                : '-';

                            const statusClasse = dados.status === 'pago' ? 'badge-success' : 'badge-warning';

                            tbody.insertAdjacentHTML('beforeend', `
                                <tr>
                                    <td>${dados.descricao || '-'}</td>
                                    <td>${dados.fornecedor || '-'}</td>
                                    <td>${parcela}</td>
                                    <td><span class="badge ${statusClasse}">${dados.status || '-'}</span></td>
                                    <td class="text-right">${dados.valor_formatado || '-'}</td>
                                </tr>
                            `);
                        });
                    }

                    abrirModalDetalhe();
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