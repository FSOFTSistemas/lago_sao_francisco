@extends('adminlte::page')

@section('title', 'Day use')

@section('content_header')
    <h5>Vendas Day Use</h5>
    <hr>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col-md-8">
            <form method="GET" action="{{ route('dayuse.index') }}" class="d-flex align-items-end gap-2">
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
                    <a href="{{ route('dayuse.index') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('dayuse.create') }}" class="btn btn-success new">
                <i class="fas fa-plus"></i> Novo Day Use
            </a>
        </div>
    </div>



    <!-- Botão para abrir/fechar os cards -->
    <div class="mb-3 d-flex justify-content-end">
        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseCardsDayUse" aria-expanded="false" aria-controls="collapseCardsDayUse">
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
                            </div>
                            <div class="icon text-{{ $isPasseio == 'passeio' ? 'info' : 'success' }} ml-3">
                                <i
                                    class="fas {{ $isPasseio == 'passeio' ? 'fa-umbrella-beach' : 'fa-ticket-alt' }} fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
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


    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 3, 'targets' => 1],
            ['responsivePriority' => 2, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dayuses as $dayuse)
                <tr>
                    <td>{{ $dayuse->id }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($dayuse->data)->format('d/m/Y') }}</td>
                    <td>{{ $dayuse->cliente->tipo == 'PJ' ? $dayuse->cliente->apelido_nome_fantasia : $dayuse->cliente->nome_razao_social }}
                    </td>
                    <td>{{ $dayuse->vendedor->nome }}</td>
                    <td><span class="badge bg-{{ $dayuse->saldo == 0 ? 'success' : 'warning' }}">
                            {{ $dayuse->saldo == 0 ? 'Quitado' : 'Pendente' }}
                        </span></td>
                    <td>

                        <form action="{{ route('dayuse.show', $dayuse->id) }}" method="GET" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm" title="Visualizar">
                                👁️
                            </button>
                        </form>

                        @php
                            $hoje = \Illuminate\Support\Carbon::today()->toDateString();
                        @endphp

                        @if ($dayuse->data == $hoje)
                            <button type="button" class="btn btn-danger btn-sm" title="Excluir"
                                onclick="confirmarExclusaoDayUse({{ $dayuse->id }})">
                                🗑️
                            </button>
                        @endif
                    </td>
                </tr>

                <!-- Modal para senha do supervisor -->
                <div class="modal fade" id="modalSenhaSupervisor" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form id="formSenhaSupervisor">
                            @csrf
                            <input type="hidden" id="dayuse_id_modal" name="dayuse_id">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel">Confirmação de Supervisor</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Informe a senha do supervisor para confirmar a exclusão:</p>
                                    <input type="password" class="form-control" name="senha" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </tbody>
    @endcomponent
    <script>
        function confirmarExclusaoDayUse(dayuseId) {
            Swal.fire({
                title: 'Autenticação do Supervisor',
                text: 'Digite a senha do supervisor para confirmar a exclusão do Day Use.',
                input: 'password',
                inputLabel: 'Senha do Supervisor',
                inputPlaceholder: 'Digite a senha',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                preConfirm: (senha) => {
                    return fetch("{{ route('dayuse.verificaSupervisor') }}", {
                            method: "POST",
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                senha: senha,
                                dayuse_id: dayuseId
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(data => {
                                    throw new Error(data.message);
                                });
                            }
                            return response.json();
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message);
                        });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Excluído!',
                        text: result.value.message
                    }).then(() => {
                        location.reload(); // recarrega para refletir exclusão
                    });
                }
            });
        }
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


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<script>
    const labels = @json($labels);
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

    $(document).ready(function () {
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
            case 'passeio': return 'primary';
            case 'outros': return 'secondary';
            case 'todos': return 'dark';
            default: return 'primary';
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

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    
@stop

@section('css')
    <style>
        .new {
            background-color: #679A4C !important;
            border: none !important;
        }

        .new:hover {
            background-color: #3e7222 !important;
        }
    </style>
    <style>
        .card-mov {
            transition: all 0.3s ease-in-out;
        }
    </style>
    

@stop
