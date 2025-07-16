@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Início</h1>
@stop

@section('content')

    <div class="row mb-3">
        <div class="col-md-8">
            <form method="GET" action="{{ route('home.index') }}" class="d-flex align-items-end gap-2">
                <div class="me-2">
                    <label>Data Início:</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="me-2">
                    <label>Data Fim:</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
                <div class="me-2">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="{{ route('home.index') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>
    <!-- Botão para abrir/fechar os cards -->
    <div class="mb-3 d-flex justify-content-end">
        <button class="btn btn-outline-secondary" type="button" data-toggle="collapse" data-target="#collapseCardsDayUse"
            aria-expanded="false" aria-controls="collapseCardsDayUse">
            <i class="fas fa-filter"></i> Mostrar Resumo de Itens
        </button>
        <button class="btn btn-outline-secondary" type="button" data-toggle="collapse" data-target="#graficoCollapse"
            aria-expanded="false" aria-controls="graficoCollapse">
            📈 Mostrar/Ocultar Gráfico
        </button>
    </div>

    <!-- Conteúdo que aparece/oculta -->
    <div class="collapse" id="collapseCardsDayUse">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-outline-primary" onclick="filtrarCards('passeio')">
                    <i class="fas fa-umbrella-beach"></i> Mostrar Passeios
                </button>
                <button class="btn btn-outline-secondary" onclick="filtrarCards('outros')">
                    <i class="fas fa-box"></i> Mostrar Outros Itens
                </button>
                <button class="btn btn-outline-info" onclick="filtrarCards('todos')">
                    <i class="fas fa-list"></i> Mostrar Todos
                </button>
            </div>
        </div>
        <div class="row">
            @php
                $totalGeralMovimentos = $movimentos->sum('valor_total');
            @endphp


            @foreach ($movimentos as $mov)
                @php
                    $isPasseio = $mov->passeio ? 'passeio' : 'outros';
                @endphp
                <div class="col-md-3 mb-3 card-mov {{ $isPasseio }}">
                    <div class="card border-left-{{ $isPasseio == 'passeio' ? 'info' : 'success' }} shadow h-100 py-2">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6
                                    class="text-{{ $isPasseio == 'passeio' ? 'info' : 'success' }} font-weight-bold text-uppercase mb-1">
                                    {{ $mov->item_nome ?? 'Item' }}
                                </h6>
                                <span class="text-dark">Qtd: {{ $mov->total_quantidade }}</span>
                                <span class="text-dark">| Valor:
                                    R${{ number_format($mov->valor_total, 2, ',', '.') }}</span>
                            </div>
                            <div class="icon text-{{ $isPasseio == 'passeio' ? 'info' : 'success' }} ml-3">
                                <i
                                    class="fas {{ $isPasseio == 'passeio' ? 'fa-umbrella-beach' : 'fa-ticket-alt' }} fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-md-3 mb-3 card-mov total-geral">
    <div class="card border-left-dark shadow h-100 py-2">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <h6 class="text-dark font-weight-bold text-uppercase mb-1">
                    Total Geral
                </h6>
                <span class="text-dark">R$ {{ number_format($totalGeralMovimentos, 2, ',', '.') }}</span>
            </div>
            <div class="icon text-dark ml-3">
                <i class="fas fa-calculator fa-2x"></i>
            </div>
        </div>
    </div>
</div>


        </div>
    </div>

    <div class="collapse" id="graficoCollapse">
        <div class="mb-3">
            <button id="btn-passeio" class="btn btn-outline-primary filtro-btn" onclick="filtrarTipo('passeio')">
                <i class="fas fa-umbrella-beach"></i> Mostrar Passeios
            </button>
            <button id="btn-outros" class="btn btn-outline-secondary filtro-btn" onclick="filtrarTipo('outros')">
                <i class="fas fa-box"></i> Mostrar Outros Itens
            </button>
            <button id="btn-todos" class="btn btn-outline-dark filtro-btn" onclick="filtrarTipo('todos')">
                <i class="fas fa-layer-group"></i> Mostrar Todos
            </button>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                Quantidade de Itens por Dia
            </div>
            <div class="card-body p-0" style="height: 400px;">
                <div style="position: relative; height: 100%; width: 100%;">
                    <canvas id="graficoPasseiosPorDia"></canvas>
                </div>
            </div>

        </div>
    </div>

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
    <script>
        function filtrarCards(tipo) {
            if (tipo === 'todos') {
                $('.card-mov').show();
            } else {
                $('.card-mov').hide();
                $('.' + tipo).show();
            }
        }

        // Exibir todos por padrão
        $(document).ready(function() {
            filtrarCards('todos');
        });

        atualizarGrafico(tipo);
    </script>
    <script>
        const labels = @json($labelsGrafico);
        const dadosGrafico = @json($dadosGrafico);
        const tiposItens = @json($tiposItens);

        function filtrarCards(tipo) {
            if (tipo === 'todos') {
                $('.card-mov').show();
            } else {
                $('.card-mov').hide();
                $('.' + tipo).show();
            }
        }


        let lastHue = Math.floor(Math.random() * 360);

        function getRandomColor() {
            lastHue = (lastHue + Math.floor(Math.random() * 60) + 30) % 360;
            return `hsl(${lastHue}, 70%, 50%)`;
        }

        let datasetsOriginais = [];

        dadosGrafico.forEach((item) => {
            const data = item.data;
            const nome = item.nome;
            datasetsOriginais.push({
                label: nome,
                data: data,
                fill: false,
                borderColor: getRandomColor(),
                tension: 0.3,
                passeio: tiposItens[nome] ? true : false
            });
        });

        const ctx = document.getElementById('graficoPasseiosPorDia').getContext('2d');
        let chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasetsOriginais
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function atualizarGrafico(filtro) {
            let novosDatasets;
            if (filtro === 'passeio') {
                novosDatasets = datasetsOriginais.filter(d => d.passeio);
            } else if (filtro === 'outros') {
                novosDatasets = datasetsOriginais.filter(d => !d.passeio);
            } else {
                novosDatasets = datasetsOriginais;
            }

            chart.data.datasets = novosDatasets;
            chart.update();
        }

        $(document).ready(function() {
            filtrarCards('todos');
            atualizarGrafico('todos');
        });
    </script>
    <script>
        function filtrarTipo(tipo) {
            filtrarCards(tipo);
            atualizarGrafico(tipo);

            // Remove classe ativa de todos os botões
            document.querySelectorAll('.filtro-btn').forEach(btn => {
                btn.classList.remove('btn-primary', 'btn-secondary', 'btn-dark', 'active');
                btn.classList.add('btn-outline-' + btn.dataset.originalClass);
            });

            // Ativa o botão clicado
            const btnId = '#btn-' + tipo;
            const btn = document.querySelector(btnId);

            if (btn) {
                btn.classList.remove('btn-outline-' + tipoClass(tipo));
                btn.classList.add('btn-' + tipoClass(tipo), 'active');
            }
        }

        function tipoClass(tipo) {
            switch (tipo) {
                case 'passeio':
                    return 'primary';
                case 'outros':
                    return 'secondary';
                case 'todos':
                    return 'dark';
                default:
                    return 'primary';
            }
        }

        // Armazenar a classe original nos botões (apenas uma vez)
        document.querySelectorAll('.filtro-btn').forEach(btn => {
            if (!btn.dataset.originalClass) {
                const classList = btn.className.split(' ');
                const outlineClass = classList.find(c => c.startsWith('btn-outline-'));
                if (outlineClass) {
                    btn.dataset.originalClass = outlineClass.replace('btn-outline-', '');
                }
            }
        });

        // Selecionar por padrão 'outros'
        document.addEventListener('DOMContentLoaded', () => {
            filtrarTipo('todos');
        });
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

        .card-mov {
            transition: all 0.3s ease-in-out;
        }

        @media (max-width: 768px) {
            .grafico-legenda {
                max-height: 200px;
            }
        }
    </style>



@stop
