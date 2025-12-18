@extends('adminlte::page')

@section('title', 'Reservas')

@section('content_header')
    <h5>Lista de Reservas</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('reserva.create') }}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Nova Reserva
        </a>
    </div>
    <form method="GET" action="{{ route('reserva.index') }}" class="mb-4" onsubmit="console.log('Form enviado')">


        <div class="d-flex flex-wrap justify-content-center gap-2">
            @php
                $situacoes = [
                    'todos' => ['label' => 'Todos', 'color' => '#679A4C'],
                    'pre-reserva' => ['label' => 'Pré-reserva', 'color' => '#FFFF00'],
                    'hospedado' => ['label' => 'Hospedado', 'color' => '#FF0000'],
                    'reserva' => ['label' => 'Reservado', 'color' => '#007BFF'],
                    'bloqueado' => ['label' => 'Data bloqueada', 'color' => '#343A40'],
                    'finalizada' => ['label' => 'Finalizado', 'color' => '#26A69A'],
                    'cancelado' => ['label' => 'Cancelado', 'color' => '#6A1B9A'],
                    'noshow' => ['label' => 'No Show', 'color' => '#F48FB1']
                ];
            @endphp

            @foreach ($situacoes as $key => $info)
                @php
                    $selecionado = ($situacao ?? 'todos') === $key;
                    $corBase = $info['color'];
                    $corTexto = $selecionado ? ($corBase === '#FFFF00' ? '#000' : '#fff') : '#000';

                    $classeBotao = $selecionado ? 'btn fw-bold' : 'btn btn-outline-dark';

                    // Hover color: fundo assume cor base, texto preto se for amarelo, branco nos demais
                    $hoverTextColor = $corBase === '#FFFF00' ? '#000' : '#fff';

                    $estiloInline = $selecionado
                        ? "background-color: $corBase; color: $corTexto;"
                        : "border-color: $corBase; color: #000;";
                @endphp

                <button type="submit" name="situacao" value="{{ $key }}" class="{{ $classeBotao }}"
                    style="{{ $estiloInline }}"
                    onmouseover="this.style.backgroundColor='{{ $corBase }}'; this.style.color='{{ $hoverTextColor }}';"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#000';">
                    {{ $info['label'] }}
                </button>
            @endforeach

        </div>
    </form>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => 3],
            ['responsivePriority' => 5, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 0,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>Reserva/Hospedagem</th>
                <th>Hóspede</th>
                <th>Quarto</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Qtd.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservas as $reserva)
                <tr>
                    <td>
                        <!-- Container Flex para alinhar o indicador, o ID e o botão de excluir -->
                        <div class="d-flex align-items-center">
                            <span class="status-indicator"
                                style="background-color: {{ getReservaStatusColor($reserva->situacao) }};"></span>
                            
                            <a id="editlink" href="{{ route('reserva.edit', $reserva->id) }}" class="mr-2">
                                00000{{ $reserva->id }}
                            </a>

                            <!-- Botão de Excluir com Supervisor (Apenas para bloqueados) -->
                            @if ($reserva->situacao === 'bloqueado')
                                <button type="button" 
                                        class="btn btn-link p-0 text-danger btn-excluir-bloqueio-super" 
                                        title="Excluir Bloqueio (Requer Senha)"
                                        data-url="{{ route('reservas.excluir.bloqueio.supervisor', $reserva->id) }}">
                                    <i class="fas fa-trash-alt" style="font-size: 0.9rem;"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                    <td>{{ $reserva->hospede->nome }}</td>
                    <td>{{ $reserva->quarto->nome }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}</td>
                    <td>
                        {{ $reserva->n_adultos }}
                        @if ($reserva->n_criancas >= 1)
                            / ({{ $reserva->n_criancas }})
                        @else
                        @endif
                    </td>

                </tr>
            @endforeach
        </tbody>
    @endcomponent
    @php
        function getReservaStatusColor($situacao)
        {
            switch ($situacao) {
                case 'bloqueado':
                    return '#343A40';
                case 'reserva':
                    return '#007BFF';
                case 'hospedado':
                    return '#FF0000';
                case 'pre-reserva':
                    return '#FFFF00';
                case 'finalizada':
                    return '#26A69A';
                case 'cancelado':
                    return '#6A1B9A';
                case 'noshow':
                    return '#F48FB1';
                default:
                    return '#808080';
            }
        }
    @endphp

@stop

@section('js')
    <!-- Carrega o SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            // Lógica para excluir bloqueio com senha de supervisor
            $(document).on('click', '.btn-excluir-bloqueio-super', async function(e) {
                e.preventDefault();
                const btn = $(this);
                const url = btn.data('url');
                const token = $('meta[name="csrf-token"]').attr('content');

                const result = await Swal.fire({
                    title: 'Autorização do Supervisor',
                    text: 'Digite a senha do supervisor para excluir este bloqueio.',
                    input: 'password',
                    inputLabel: 'Senha',
                    inputPlaceholder: 'Digite a senha...',
                    inputAttributes: {
                        autocapitalize: 'off',
                        autocomplete: 'new-password'
                    },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Autorizar Exclusão',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33', // Cor vermelha para indicar perigo/exclusão
                    showLoaderOnConfirm: true,
                    preConfirm: async (senha) => {
                        if (!senha) {
                            Swal.showValidationMessage('A senha é obrigatória');
                            return false;
                        }

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ senha_supervisor: senha })
                            });

                            if (!response.ok) {
                                const errorData = await response.json();
                                throw new Error(errorData.message || 'Erro na autorização');
                            }

                            return await response.json();
                        } catch (error) {
                            Swal.showValidationMessage(`Erro: ${error.message}`);
                        }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });

                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: result.value.message,
                        icon: 'success'
                    }).then(() => {
                        location.reload(); // Recarrega a página para atualizar a tabela
                    });
                }
            });
        });
    </script>

    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: '{{ session("success") }}',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: '{{ session("error") }}'
        });
    </script>
    @endif
@stop

@section('css')
    <style>
        #editlink {
            color: #679A4C;
            font-weight: 600;
        }

        #editlink:hover {
            color: #3e7222;
        }

        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            margin-right: 8px;
            flex-shrink: 0; 
        }

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }

        .mr-2 { margin-right: 0.5rem; }
        .p-0 { padding: 0 !important; }
        .text-danger { color: #dc3545 !important; }
        
        .me-3 {
            margin-right: 1rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .btn-outline-color {
            background-color: transparent;
            color: #000;
            border-width: 1px;
            border-style: solid;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn-outline-color:hover {
            background-color: var(--hover-color);
            color: #fff;
        }
    </style>
@stop