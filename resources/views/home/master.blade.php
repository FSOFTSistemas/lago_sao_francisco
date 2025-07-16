@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Início</h1>
@stop

@section('content')
    <!-- Gráfico de Day Use -->
    <div class="card mb-4">
        <div class="card-header"><strong>Gráfico de Day Use - Mês Atual</strong></div>
        <div class="card-body">
            <div class="overflow-auto">
                <div class="chart-bar-container" style="min-width: 900px;">
                    <canvas id="graficoDayUse"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Pizza Fluxo de Caixa -->
    <div class="card">
        <div class="card-header"><strong>Fluxo de Caixa (Formas de Pagamento)</strong></div>
        <div class="card-body">
            <form id="filtrosGrafico" class="row gy-2 gx-3 align-items-center mb-4">
                <div class="col-md-4">
                    <label for="caixa_id" class="form-label">Caixa</label>
                    <select class="form-select" id="caixa_id">
                        <option value="">Todos</option>
                        <!-- Exemplo: caixas disponíveis -->
                        @foreach ($caixas ?? [] as $caixa)
                            <option value="{{ $caixa->id }}">{{ $caixa->descricao }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="modo_data" class="form-label">Modo de Data</label>
                    <select class="form-select" id="modo_data">
                        <option value="dia" selected>Dia Atual</option>
                        <option value="periodo">Período</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button id="atualizarGrafico" class="btn btn-primary w-100">Atualizar</button>
                </div>

                <div class="col-md-6 periodo-filtro d-none">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio">
                </div>

                <div class="col-md-6 periodo-filtro d-none">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim">
                </div>
            </form>
            <div class="chart-pizza-wrapper">
                <canvas id="graficoPizzaFluxo"></canvas>
                <div class="grafico-legenda mt-3" id="legendaPizzaFluxo"></div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#caixa_id').select2({
                width: '100%'
            });
            $('#modo_data').select2({
                minimumResultsForSearch: -1,
                width: '100%'
            }); // sem barra de pesquisa
        });


        Chart.register(ChartDataLabels);
        const passeioQtd = @json($passeioQtd);
        const entradaQtd = @json($entradaQtd);



        // DAY USE
        const graficoDayUse = new Chart(document.getElementById('graficoDayUse').getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{
                        label: 'Passeio (R$)',
                        data: @json($passeioValor),
                        backgroundColor: '#79A551',
                        datalabels: {
                            formatter: (value, context) => {
                                const i = context.dataIndex;
                                return `R$ ${value.toFixed(2)}\nQtd: ${passeioQtd[i]}`;
                            }
                        }

                    },
                    {
                        label: 'Entrada (R$)',
                        data: @json($entradaValor),
                        backgroundColor: '#2B82BF',
                        datalabels: {
                            formatter: (value, context) => {
                                const i = context.dataIndex;
                                return `R$ ${value.toFixed(2)}\nQtd: ${entradaQtd[i]}`;
                            }
                        }

                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Valores de Day Use por Dia',
                        padding: {
                            bottom: 50,
                        }
                    },
                    legend: {
                        position: 'bottom'
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#000',
                        font: {
                            size: 11,
                            weight: 'bold'
                        },
                        clamp: true
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Dia do Mês'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 30,
                            autoSkip: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Valor em R$'
                        }
                    }
                }
            }

        });


        // PIZZA
        let graficoPizzaFluxo = new Chart(document.getElementById('graficoPizzaFluxo').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796',
                        '#5a5c69', '#2e59d9'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        bottom: 0
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribuição por Movimento',
                        padding: {
                            bottom: 40
                        }

                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `R$ ${ctx.raw.toFixed(2)}`
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        clamp: true,
                        font: {
                            size: 12,
                            weight: 'bold'
                        },
                        formatter: (value) => `R$ ${value.toFixed(2)}`
                    }
                }
            },
            plugins: [ChartDataLabels]
        });


        // FILTROS
        $('#modo_data').on('change', function() {
            const exibir = $(this).val() === 'periodo';
            $('.periodo-filtro').toggleClass('d-none', !exibir);
        });

        document.getElementById('atualizarGrafico').addEventListener('click', function(e) {
            e.preventDefault();

            const modo = document.getElementById('modo_data').value;
            const caixa_id = document.getElementById('caixa_id').value;
            const data_inicio = document.getElementById('data_inicio').value;
            const data_fim = document.getElementById('data_fim').value;

            fetch(
                    `/grafico-fluxo-caixa?modo_data=${modo}&caixa_id=${caixa_id}&data_inicio=${data_inicio}&data_fim=${data_fim}`
                )
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(d => d.nome);
                    const valores = data.map(d => parseFloat(d.total));

                    graficoPizzaFluxo.data.labels = labels;
                    graficoPizzaFluxo.data.datasets[0].data = valores;
                    graficoPizzaFluxo.update();
                });
        });

        function carregarGraficoPizzaFluxo() {
            const modo = document.getElementById('modo_data').value;
            const caixa_id = document.getElementById('caixa_id').value;
            const data_inicio = document.getElementById('data_inicio').value;
            const data_fim = document.getElementById('data_fim').value;

            fetch(
                    `/grafico-fluxo-caixa?modo_data=${modo}&caixa_id=${caixa_id}&data_inicio=${data_inicio}&data_fim=${data_fim}`
                    )
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(d => d.nome);
                    const valores = data.map(d => parseFloat(d.total));

                    graficoPizzaFluxo.data.labels = labels;
                    graficoPizzaFluxo.data.datasets[0].data = valores;
                    graficoPizzaFluxo.update();

                    const legendaDiv = document.getElementById('legendaPizzaFluxo');
                    legendaDiv.innerHTML = ''; // limpa antes

                    labels.forEach((label, index) => {
                        const color = graficoPizzaFluxo.data.datasets[0].backgroundColor[index];
                        const valor = valores[index].toFixed(2);

                        const item = document.createElement('div');
                        item.classList.add('legenda-item');
                        item.innerHTML =
                            `<span class="legenda-cor" style="background-color: ${color};"></span> ${label} - R$ ${valor}`;
                        legendaDiv.appendChild(item);
                    });
                });
        }


        // chama no clique:
        document.getElementById('atualizarGrafico').addEventListener('click', function(e) {
            e.preventDefault();
            carregarGraficoPizzaFluxo();
        });

        // chama automaticamente ao carregar a página:
        window.addEventListener('load', carregarGraficoPizzaFluxo);
    </script>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            padding: 6px 12px;
            font-size: 1rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }

        select.form-select {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            font-size: 1rem;
        }

        .chart-bar-container {
            position: relative;
            min-width: 700px;
            height: 350px;
        }

        .overflow-auto {
            overflow-x: auto;
        }

        .chart-pizza-wrapper {
            position: relative;
            width: 100%;
            height: auto;
            min-height: 300px;
        }

        #graficoPizzaFluxo {
            width: 100% !important;
            height: auto !important;
            max-height: 350px;
        }

        .grafico-legenda {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            max-height: 150px;
            overflow-y: auto;
            padding-top: 1rem;
        }

        .legenda-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            background-color: #f8f9fa;
            padding: 6px 10px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .legenda-cor {
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 6px;
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .grafico-legenda {
                max-height: 200px;
            }
        }
    </style>



@stop
